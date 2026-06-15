<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        $listing->qr_code_path    = $this->generateQrImage($this->buildWhatsAppUrl($listing), $listing->id);

        $listing->save();

        return $listing;
    }

    public function generateWhatsAppQr(Listing $listing): Listing
    {
        if (empty($listing->qr_slug)) {
            $listing->qr_slug = $this->makeUniqueSlug($listing);
        }

        if (empty($listing->public_profile_url)) {
            $listing->public_profile_url = rtrim(config('tatkaldoctor.public_website_url'), '/')
                . '/d/'
                . $listing->qr_slug;
        }

        $listing->qr_generated_at = now();
        $listing->qr_code_path    = $this->generateQrImage($this->buildWhatsAppUrl($listing), $listing->id);
        $listing->save();

        return $listing;
    }

    public function buildWhatsAppUrl(Listing $listing): string
    {
        $phone = config('tatkaldoctor.whatsapp_business_phone', '919999999999');
        return 'https://wa.me/' . $phone . '?text=' . urlencode('qr:' . $listing->qr_slug);
    }

    public function getQrCodeUrl(Listing $listing): ?string
    {
        if (! $listing->qr_code_path) {
            return null;
        }
        return Storage::disk('public')->url($listing->qr_code_path);
    }

    private function generateQrImage(string $url, int $listingId): string
    {
        $filename = "qrcodes/{$listingId}.svg";

        $svg = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($url);

        Storage::disk('public')->put($filename, $svg);

        return $filename;
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
