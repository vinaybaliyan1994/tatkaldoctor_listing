<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use App\Models\MasterLocation;
use App\Models\MasterService;
use App\Services\ListingAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class ImportedDoctorController extends Controller
{
    private const REQUIRED_COLUMNS = [
        'doctor_name',
        'speciality',
        'clinic_address',
    ];

    private const CSV_COLUMNS = [
        'doctor_name',
        'speciality',
        'clinic_name',
        'clinic_address',
        'clinic_mobile',
        'city',
        'location',
        'google_business_url',
        'status',
    ];

    public function __construct(private readonly ListingAuditService $audit) {}

    public function index(Request $request): View
    {
        $query = Listing::with(['city', 'location'])
            ->imported()
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('hospital_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('personal_contact_no', 'like', "%{$search}%")
                    ->orWhere('meta_data->speciality', 'like', "%{$search}%")
                    ->orWhere('meta_data->city_name', 'like', "%{$search}%")
                    ->orWhere('meta_data->location_name', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }

        $doctors = $query->paginate(20)->withQueryString();

        return view('imported-doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('imported-doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $row = $this->validatedDoctorRow($request->validate($this->manualRules()));

        if ($duplicate = $this->duplicateFor($row)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Duplicate imported doctor found: listing #' . $duplicate->id);
        }

        $listing = $this->createImportedListing($row);

        return redirect()->route('imported-doctors.show', $listing)
            ->with('success', 'Imported doctor added successfully.');
    }

    public function show(Listing $importedDoctor): View
    {
        abort_unless($importedDoctor->isImported(), 404);

        return view('imported-doctors.show', ['doctor' => $importedDoctor->load(['city', 'location'])]);
    }

    public function edit(Listing $importedDoctor): View
    {
        abort_unless($importedDoctor->isImported(), 404);

        return view('imported-doctors.edit', ['doctor' => $importedDoctor]);
    }

    public function update(Request $request, Listing $importedDoctor): RedirectResponse
    {
        abort_unless($importedDoctor->isImported(), 404);

        $row = $this->validatedDoctorRow($request->validate($this->manualRules()));

        $duplicate = $this->duplicateFor($row, $importedDoctor->id);
        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Duplicate imported doctor found: listing #' . $duplicate->id);
        }

        $old = $importedDoctor->only(['name', 'hospital_name', 'address', 'personal_contact_no', 'status']);
        $this->fillImportedListing($importedDoctor, $row);
        $importedDoctor->save();

        $this->audit->log($importedDoctor->id, 'imported_doctor_updated', $old, [
            'name' => $importedDoctor->name,
            'status' => $importedDoctor->status,
        ]);

        return redirect()->route('imported-doctors.show', $importedDoctor)
            ->with('success', 'Imported doctor updated successfully.');
    }

    public function destroy(Listing $importedDoctor): RedirectResponse
    {
        abort_unless($importedDoctor->isImported(), 404);

        $importedDoctor->delete();

        return redirect()->route('imported-doctors.index')
            ->with('success', 'Imported doctor deleted successfully.');
    }

    public function upload(): View
    {
        return view('imported-doctors.upload');
    }

    public function preview(Request $request): RedirectResponse|View
    {
        $validated = $request->validate([
            'import_file' => ['required', 'file', 'max:5120', 'mimes:csv,txt,xlsx'],
        ]);

        $rows = $this->parseImportFile($validated['import_file']->getRealPath(), $validated['import_file']->getClientOriginalExtension());

        $preview = [];
        $seen = [];
        foreach ($rows as $index => $rawRow) {
            $row = $this->validatedDoctorRow($rawRow);
            $errors = $this->rowErrors($row);
            $key = $this->rowDuplicateKey($row);
            $duplicate = $key && isset($seen[$key]);
            if ($key) {
                $seen[$key] = true;
            }

            $existing = $this->duplicateFor($row);
            $preview[] = [
                'row_number' => $index + 2,
                'row' => $row,
                'errors' => $errors,
                'duplicate_in_file' => $duplicate,
                'duplicate_listing_id' => $existing?->id,
                'will_import' => empty($errors) && ! $duplicate && ! $existing,
            ];
        }

        $summary = [
            'total_rows' => count($preview),
            'importable' => collect($preview)->where('will_import', true)->count(),
            'duplicates' => collect($preview)->filter(fn ($item) => $item['duplicate_in_file'] || $item['duplicate_listing_id'])->count(),
            'errors' => collect($preview)->filter(fn ($item) => ! empty($item['errors']))->count(),
        ];

        session(['imported_doctors.preview' => $preview]);

        return view('imported-doctors.preview', compact('preview', 'summary'));
    }

    public function import(Request $request): RedirectResponse
    {
        $preview = session('imported_doctors.preview', []);
        if (empty($preview)) {
            return redirect()->route('imported-doctors.upload')
                ->with('error', 'Upload a file and review the preview before importing.');
        }

        $summary = [
            'total_rows' => count($preview),
            'imported' => 0,
            'skipped' => 0,
            'duplicates' => 0,
            'errors' => 0,
        ];

        DB::transaction(function () use ($preview, &$summary): void {
            foreach ($preview as $item) {
                if (! empty($item['errors'])) {
                    $summary['errors']++;
                    $summary['skipped']++;
                    continue;
                }

                if ($item['duplicate_in_file'] || $item['duplicate_listing_id'] || $this->duplicateFor($item['row'])) {
                    $summary['duplicates']++;
                    $summary['skipped']++;
                    continue;
                }

                $this->createImportedListing($item['row']);
                $summary['imported']++;
            }
        });

        session()->forget('imported_doctors.preview');

        return redirect()->route('imported-doctors.index')
            ->with('success', 'Import complete.')
            ->with('import_summary', $summary);
    }

    private function manualRules(): array
    {
        return [
            'doctor_name' => ['required', 'string', 'max:191'],
            'speciality' => ['required', 'string', 'max:191'],
            'clinic_name' => ['nullable', 'string', 'max:191'],
            'clinic_address' => ['required', 'string', 'max:500'],
            'clinic_mobile' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:191'],
            'location' => ['nullable', 'string', 'max:191'],
            'google_business_url' => ['nullable', 'url', 'max:2000'],
            'status' => ['nullable', 'in:active,inactive,1,0,on'],
        ];
    }

    private function validatedDoctorRow(array $row): array
    {
        $normalized = [];
        foreach (self::CSV_COLUMNS as $column) {
            $normalized[$column] = trim((string) ($row[$column] ?? ''));
        }

        $normalized['status'] = strtolower($normalized['status'] ?: 'active');

        return $normalized;
    }

    private function rowErrors(array $row): array
    {
        $errors = [];
        foreach (self::REQUIRED_COLUMNS as $column) {
            if ($row[$column] === '') {
                $errors[] = $column . ' is required';
            }
        }

        if ($row['google_business_url'] !== '' && ! filter_var($row['google_business_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'google_business_url must be a valid URL';
        }

        return $errors;
    }

    private function createImportedListing(array $row): Listing
    {
        $listing = new Listing();
        $this->fillImportedListing($listing, $row);
        $listing->save();

        $listing->qr_slug = $this->makeImportedSlug($listing);
        $listing->public_profile_url = rtrim(config('tatkaldoctor.public_website_url'), '/') . '/doctor/' . $listing->qr_slug;
        $listing->saveQuietly();

        $this->audit->log($listing->id, 'imported_doctor_created', null, [
            'name' => $listing->name,
            'source' => $listing->source,
        ]);

        return $listing;
    }

    private function fillImportedListing(Listing $listing, array $row): void
    {
        $city = $this->findCity($row['city']);
        $location = $city ? $this->findLocation($city, $row['location']) : null;
        $service = $this->findService($row['speciality']);

        $listing->fill([
            'country_code' => $city?->country_code ?? $this->defaultCountryCode(),
            'master_city_id' => $city?->id,
            'master_location_id' => $location?->id,
            'name' => $row['doctor_name'],
            'hospital_name' => $row['clinic_name'] ?: null,
            'address' => $row['clinic_address'],
            'personal_contact_no' => $row['clinic_mobile'] ?: null,
            'appointment_no' => null,
            'services' => $service ? [$service->id] : null,
            'qualifications' => null,
            'meta_data' => [
                'speciality' => $row['speciality'],
                'city_name' => $row['city'] ?: null,
                'location_name' => $row['location'] ?: null,
                'import_source' => 'google_business_import',
            ],
            'status' => in_array($row['status'], ['active', '1', 'on', 'true', 'yes'], true),
            'source' => 'google_business_import',
            'is_imported' => true,
            'is_verified_by_tatkaldoctor' => false,
            'verification_status' => 'imported',
            'verified_at' => null,
            'verified_by' => null,
            'rejection_reason' => null,
            'external_source' => 'google_business',
            'external_url' => $row['google_business_url'] ?: null,
            'qr_code_path' => null,
            'qr_generated_at' => null,
        ]);
    }

    private function duplicateFor(array $row, ?int $ignoreId = null): ?Listing
    {
        $name = Str::lower($row['doctor_name']);
        $mobile = preg_replace('/\D+/', '', $row['clinic_mobile']);
        $address = Str::lower($row['clinic_address']);

        return Listing::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->whereRaw('LOWER(name) = ?', [$name])
            ->get()
            ->first(function (Listing $listing) use ($mobile, $address): bool {
                $listingMobile = preg_replace('/\D+/', '', (string) $listing->personal_contact_no);
                $listingAddress = Str::lower((string) $listing->address);

                return ($mobile !== '' && $listingMobile === $mobile)
                    || ($address !== '' && $listingAddress === $address);
            });
    }

    private function rowDuplicateKey(array $row): ?string
    {
        $name = Str::lower($row['doctor_name']);
        $mobile = preg_replace('/\D+/', '', $row['clinic_mobile']);
        $address = Str::lower($row['clinic_address']);

        if ($name === '') {
            return null;
        }

        return $name . '|' . ($mobile ?: $address);
    }

    private function parseImportFile(string $path, string $extension): array
    {
        return strtolower($extension) === 'xlsx'
            ? $this->parseXlsx($path)
            : $this->parseCsv($path);
    }

    private function parseCsv(string $path): array
    {
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

        $headers = [];
        $rows = [];
        foreach ($file as $row) {
            if (! is_array($row) || $row === [null]) {
                continue;
            }

            if ($headers === []) {
                $headers = array_map(fn ($value) => Str::snake(trim((string) $value)), $row);
                continue;
            }

            $rows[] = array_combine($headers, array_pad($row, count($headers), '')) ?: [];
        }

        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            abort(422, 'XLSX import requires the PHP zip extension.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            abort(422, 'Unable to open XLSX file.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si ?? [] as $item) {
                $sharedStrings[] = (string) ($item->t ?? '');
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXml) {
            abort(422, 'XLSX sheet1.xml was not found.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $grid = [];
        foreach ($sheet->sheetData->row ?? [] as $rowNode) {
            $row = [];
            foreach ($rowNode->c as $cell) {
                $ref = (string) $cell['r'];
                $columnIndex = $this->columnIndex($ref);
                $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
                $row[$columnIndex] = $value;
            }
            ksort($row);
            $grid[] = $row;
        }

        $headers = array_map(fn ($value) => Str::snake(trim((string) $value)), array_values($grid[0] ?? []));
        $rows = [];
        foreach (array_slice($grid, 1) as $row) {
            $values = array_values($row);
            $rows[] = array_combine($headers, array_pad($values, count($headers), '')) ?: [];
        }

        return $rows;
    }

    private function columnIndex(string $cellRef): int
    {
        preg_match('/^[A-Z]+/', $cellRef, $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function findCity(string $city): ?MasterCity
    {
        if ($city === '') {
            return null;
        }

        return MasterCity::whereRaw('LOWER(name) = ?', [Str::lower($city)])->first();
    }

    private function findLocation(MasterCity $city, string $location): ?MasterLocation
    {
        if ($location === '') {
            return null;
        }

        return MasterLocation::where('master_city_id', $city->id)
            ->whereRaw('LOWER(location) = ?', [Str::lower($location)])
            ->first();
    }

    private function findService(string $speciality): ?MasterService
    {
        return MasterService::whereRaw('LOWER(service) = ?', [Str::lower($speciality)])->first();
    }

    private function defaultCountryCode(): string
    {
        return MasterCountry::where('code', 'IND')->exists()
            ? 'IND'
            : (string) MasterCountry::query()->value('code');
    }

    private function makeImportedSlug(Listing $listing): string
    {
        $city = $listing->city?->name ?? ($listing->meta_data['city_name'] ?? null);
        $base = Str::slug(trim($listing->name . ' ' . $city)) . '-' . $listing->id;

        return $base ?: 'imported-doctor-' . $listing->id;
    }
}
