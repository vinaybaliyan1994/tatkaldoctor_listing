<?php

namespace App\Observers;

use App\Models\Listing;
use App\Services\SolutionSyncService;

class ListingObserver
{
    private const SYNC_FIELDS = [
        'name',
        'hospital_name',
        'email',
        'personal_contact_no',
        'appointment_no',
        'country_code',
        'master_city_id',
        'master_location_id',
        'address',
        'services',
        'qualifications',
        'profile_photo_path',
        'qr_slug',
        'public_profile_url',
        'qr_code_path',
        'qr_generated_at',
        'verification_status',
        'status',
        'description',
        'latitude',
        'longitude',
    ];

    public function created(Listing $listing): void
    {
        if ($listing->isImported()) {
            return;
        }

        app(SolutionSyncService::class)->syncListing($listing, 'listing.created');
    }

    public function updated(Listing $listing): void
    {
        if ($listing->isImported()) {
            return;
        }

        $changed = array_keys($listing->getChanges());
        $syncFields = array_values(array_intersect($changed, self::SYNC_FIELDS));

        if ($syncFields === []) {
            return;
        }

        app(SolutionSyncService::class)->syncListing(
            $listing,
            $this->eventName($syncFields)
        );
    }

    private function eventName(array $syncFields): string
    {
        if (in_array('verification_status', $syncFields, true)) {
            return 'listing.verification_changed';
        }

        if (in_array('status', $syncFields, true)) {
            return 'listing.status_changed';
        }

        if (in_array('profile_photo_path', $syncFields, true)) {
            return 'listing.profile_photo_updated';
        }

        return 'listing.profile_updated';
    }
}
