<?php

namespace App\Console\Commands;

use App\Models\DoctorDocument;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DebugPendingDocuments extends Command
{
    protected $signature = 'documents:debug-pending';

    protected $description = 'Show pending doctor document diagnostics for admin review troubleshooting.';

    public function handle(): int
    {
        $total = DoctorDocument::count();
        $pending = DoctorDocument::where('status', 'pending')->count();
        $orphaned = DoctorDocument::query()
            ->leftJoin('listings', 'doctor_documents.listing_id', '=', 'listings.id')
            ->whereNull('listings.id')
            ->count();

        $this->info('Doctor documents diagnostics');
        $this->line('Total doctor_documents: ' . $total);
        $this->line('Pending count: ' . $pending);
        $this->line('Orphaned count: ' . $orphaned);
        $this->newLine();

        $documents = DoctorDocument::with('listing')
            ->latest()
            ->limit(10)
            ->get();

        if ($documents->isEmpty()) {
            $this->warn('No doctor documents found.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'listing_id', 'listing uuid/name', 'document_type', 'status', 'file_path', 'file exists'],
            $documents->map(function (DoctorDocument $document): array {
                $listing = $document->listing;

                return [
                    $document->id,
                    $document->listing_id,
                    $listing ? "{$listing->uuid} / {$listing->name}" : 'missing listing',
                    $document->document_type,
                    $document->status,
                    $document->file_path,
                    Storage::disk('public')->exists($document->file_path) ? 'yes' : 'no',
                ];
            })->all()
        );

        $this->newLine();
        $this->info('Latest listings with document counts');

        $listings = Listing::query()
            ->withCount([
                'documents',
                'documents as pending_documents_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->latest('id')
            ->limit(10)
            ->get(['id', 'uuid', 'name']);

        $this->table(
            ['id', 'uuid', 'name', 'documents_count', 'pending_documents_count'],
            $listings->map(fn (Listing $listing): array => [
                $listing->id,
                $listing->uuid,
                $listing->name,
                $listing->documents_count,
                $listing->pending_documents_count,
            ])->all()
        );

        $this->newLine();
        $this->info('Recent document API attempts');

        $apiLogs = DB::table('api_logs')
            ->where('endpoint', 'like', '%/documents%')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'endpoint', 'method', 'response_status', 'success', 'error_message', 'created_at']);

        if ($apiLogs->isEmpty()) {
            $this->line('No recent document API attempts found in api_logs.');
        } else {
            $this->table(
                ['id', 'endpoint', 'method', 'response_status', 'success', 'error_message', 'created_at'],
                $apiLogs->map(fn ($log): array => [
                    $log->id,
                    $log->endpoint,
                    $log->method,
                    $log->response_status,
                    $log->success ? 'yes' : 'no',
                    $log->error_message,
                    $log->created_at,
                ])->all()
            );
        }

        return self::SUCCESS;
    }
}
