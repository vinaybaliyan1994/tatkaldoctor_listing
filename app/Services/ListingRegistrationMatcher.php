<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListingRegistrationMatcher
{
    public function findExisting(array $attributes): ?Listing
    {
        $uuid = $attributes['uuid'] ?? $attributes['listing_uuid'] ?? null;
        if ($uuid) {
            $listing = Listing::where('uuid', $uuid)
                ->where('source', 'solution_registration')
                ->whereIn('verification_status', ['pending', 'rejected'])
                ->first();

            if ($listing) {
                return $listing;
            }
        }

        $name = $this->normalizeName($attributes['name'] ?? $attributes['doctor_name'] ?? null);
        $email = $this->normalizeEmail($attributes['email'] ?? null);
        $phone = $this->normalizePhone($attributes['phone'] ?? $attributes['mobile'] ?? null);
        $registrationNo = $this->normalizeRegistration($attributes['registration_no'] ?? $attributes['registration_number'] ?? null);

        if (! $email && ! $phone && ! $registrationNo) {
            return null;
        }

        $candidates = Listing::query()
            ->where('source', 'solution_registration')
            ->whereIn('verification_status', ['pending', 'rejected'])
            ->where(function (Builder $query) use ($email, $phone, $registrationNo, $name) {
                if ($email) {
                    $query->orWhereRaw('LOWER(email) = ?', [$email]);
                }

                if ($phone) {
                    $query->orWhere('personal_contact_no', 'like', '%' . $phone . '%');
                }

                if ($registrationNo) {
                    $query->orWhere('meta_data->registration_no', $registrationNo);
                }

                if ($name) {
                    $query->orWhereRaw('LOWER(name) = ?', [$name]);
                }
            })
            ->latest('id')
            ->limit(25)
            ->get();

        foreach ($candidates as $candidate) {
            if ($this->isMatch($candidate, $name, $email, $phone, $registrationNo)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isMatch(Listing $listing, ?string $name, ?string $email, ?string $phone, ?string $registrationNo): bool
    {
        $listingName = $this->normalizeName($listing->name);
        $listingEmail = $this->normalizeEmail($listing->email);
        $listingPhone = $this->normalizePhone($listing->personal_contact_no);
        $listingRegistrationNo = $this->normalizeRegistration($listing->meta_data['registration_no'] ?? null);

        if ($registrationNo && $listingRegistrationNo && $registrationNo === $listingRegistrationNo) {
            return true;
        }

        if ($email && $listingEmail && $email === $listingEmail) {
            return true;
        }

        return $phone && $listingPhone && $phone === $listingPhone && $name && $listingName === $name;
    }

    private function normalizeName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $name = Str::of($name)->lower()->replaceMatches('/\bdr\.?\s+/i', '')->squish()->toString();

        return $name !== '' ? $name : null;
    }

    private function normalizeEmail(?string $email): ?string
    {
        if (! $email) {
            return null;
        }

        $email = Str::lower(trim($email));

        return $email !== '' ? $email : null;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, -10);
        }

        return $digits !== '' ? $digits : null;
    }

    private function normalizeRegistration(?string $registrationNo): ?string
    {
        if (! $registrationNo) {
            return null;
        }

        $registrationNo = Str::of($registrationNo)->lower()->replaceMatches('/\s+/', '')->toString();

        return $registrationNo !== '' ? $registrationNo : null;
    }
}
