<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\SolutionSyncService;
use Illuminate\Console\Command;

class SyncToSolution extends Command
{
    protected $signature = 'listing:sync-to-solution {listing_uuid?}';

    protected $description = 'Push doctor listing profile data to Solution cache. Pass listing_uuid to sync one; omit to sync all approved active listings.';

    public function handle(SolutionSyncService $syncService): int
    {
        $listingUuid = $this->argument('listing_uuid');

        $query = Listing::with(['country', 'city', 'location'])
            ->where('status', true)
            ->where('is_imported', false)
            ->where('verification_status', 'approved');

        if ($listingUuid) {
            $query->where('uuid', $listingUuid);
        }

        $listings = $query->orderBy('id')->get();

        if ($listings->isEmpty()) {
            $this->warn($listingUuid
                ? "No approved active listing found for uuid: {$listingUuid}"
                : 'No approved active listings found.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed  = 0;

        foreach ($listings as $listing) {
            $this->line("Syncing {$listing->uuid} ({$listing->name})...");
            try {
                $syncService->syncListing($listing, 'listing.manual_sync');
                $success++;
                $this->info('  OK');
            } catch (\Throwable $e) {
                $failed++;
                $this->error('  Failed: ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$success}; Failed: {$failed}; Total: " . $listings->count());

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
