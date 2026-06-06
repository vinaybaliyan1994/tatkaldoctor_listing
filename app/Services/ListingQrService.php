<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Str;

class ListingQrService
{
    public function generate(Listing $listing): Listing
    {
        if (empty($listing->qr_slug)) {
            $listing->qr_slug = $this->makeUniqueSlug($listing);
        }

        $listing->public_profile_url = rtrim(config('tatkaldoctor.public_website_url'), '/')
            . '/d/'
            . $listing->qr_slug;

        $listing->qr_generated_at = now();

        // TODO: QR image generation
        // Install package: composer require simplesoftwareio/simple-qrcode
        // Then generate image:
        //   $filename = "qrcodes/{$listing->id}.png";
        //   Storage::disk('public')->put($filename, QrCode::format('png')->size(300)->generate($listing->public_profile_url));
        //   $listing->qr_code_path = $filename;

        $listing->save();

        return $listing;
    }

    private function makeUniqueSlug(Listing $listing): string
    {
        $base = Str::slug($listing->name) . '-' . substr($listing->uuid, 0, 8);
        $slug = $base;
        $i    = 1;

        while (
            Listing::where('qr_slug', $slug)
                ->where('id', '!=', $listing->id)
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
