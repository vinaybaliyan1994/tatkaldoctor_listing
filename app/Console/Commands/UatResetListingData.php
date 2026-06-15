<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UatResetListingData extends Command
{
    protected $signature = 'uat:reset-listing-data {--force : Confirm destructive UAT listing reset}';

    protected $description = 'Reset UAT doctor listing data while preserving master/config/admin data';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->warn('This command deletes UAT listing data. Re-run with --force to continue.');
            $this->line('Protected data is preserved: admin users, master data, plans, API clients, settings.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('listings')) {
            $this->error('Table not found: listings');

            return self::FAILURE;
        }

        $summary = [
            'listings' => 0,
            'solution_registration_listings' => 0,
            'test_listings' => 0,
            'documents' => 0,
            'audit_logs' => 0,
            'storage_files' => 0,
        ];

        $storagePaths = [];

        DB::transaction(function () use (&$summary, &$storagePaths): void {
            $listingIds = $this->targetListingIds();

            $summary['listings'] = count($listingIds);

            if (empty($listingIds)) {
                return;
            }

            if (Schema::hasColumn('listings', 'source')) {
                $summary['solution_registration_listings'] = DB::table('listings')
                    ->whereIn('id', $listingIds)
                    ->where('source', 'solution_registration')
                    ->count();
            }

            $summary['test_listings'] = max(0, $summary['listings'] - $summary['solution_registration_listings']);

            if (Schema::hasTable('doctor_documents')) {
                $documents = DB::table('doctor_documents')
                    ->whereIn('listing_id', $listingIds)
                    ->get(['file_path']);

                foreach ($documents as $document) {
                    $this->addStoragePath($storagePaths, $document->file_path ?? null);
                }

                $summary['documents'] = DB::table('doctor_documents')
                    ->whereIn('listing_id', $listingIds)
                    ->delete();
            }

            if (Schema::hasTable('listing_audit_logs')) {
                $summary['audit_logs'] = DB::table('listing_audit_logs')
                    ->whereIn('listing_id', $listingIds)
                    ->delete();
            }

            $fileColumns = array_filter([
                Schema::hasColumn('listings', 'qr_code_path') ? 'qr_code_path' : null,
                Schema::hasColumn('listings', 'profile_photo_path') ? 'profile_photo_path' : null,
            ]);

            if (! empty($fileColumns)) {
                DB::table('listings')
                    ->whereIn('id', $listingIds)
                    ->select($fileColumns)
                    ->orderBy('id')
                    ->chunk(100, function ($listings) use (&$storagePaths, $fileColumns): void {
                        foreach ($listings as $listing) {
                            foreach ($fileColumns as $column) {
                                $this->addStoragePath($storagePaths, $listing->{$column} ?? null);
                            }
                        }
                    });
            }

            DB::table('listings')->whereIn('id', $listingIds)->delete();
        });

        $summary['storage_files'] = $this->deleteStorageFiles($storagePaths);

        $this->info('UAT listing reset complete.');
        $this->table(
            ['Item', 'Deleted'],
            [
                ['Listings', $summary['listings']],
                ['Solution registration listings', $summary['solution_registration_listings']],
                ['Clearly test/manual listings', $summary['test_listings']],
                ['Doctor documents', $summary['documents']],
                ['Listing audit logs', $summary['audit_logs']],
                ['Linked storage files', $summary['storage_files']],
            ]
        );
        $this->line('Skipped protected data: countries, cities, locations, services, qualifications, plans, API clients, admin users, settings.');

        return self::SUCCESS;
    }

    private function targetListingIds(): array
    {
        return DB::table('listings')
            ->where(function ($query): void {
                if (Schema::hasColumn('listings', 'source')) {
                    $query->where('source', 'solution_registration');
                }

                foreach (['name', 'hospital_name', 'email', 'appointment_no', 'personal_contact_no'] as $column) {
                    if (! Schema::hasColumn('listings', $column)) {
                        continue;
                    }

                    $query
                        ->orWhere($column, 'like', '%test%')
                        ->orWhere($column, 'like', '%demo%')
                        ->orWhere($column, 'like', '%dummy%');
                }

                if (Schema::hasColumn('listings', 'email')) {
                    $query->orWhere('email', 'like', '%@example.%');
                }
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function addStoragePath(array &$paths, ?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '') {
            return;
        }

        $paths[] = preg_replace('#^(storage/|public/)#', '', $path);
    }

    private function deleteStorageFiles(array $paths): int
    {
        $deleted = 0;

        foreach (array_values(array_unique(array_filter($paths))) as $path) {
            if (Storage::disk('public')->exists($path) && Storage::disk('public')->delete($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
