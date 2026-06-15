<?php

namespace Tests\Feature;

use App\Http\Middleware\HmacAuthentication;
use App\Models\Listing;
use App\Models\MasterCountry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingIntakeDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_profile_submission_updates_existing_registration_listing(): void
    {
        $this->withoutMiddleware(HmacAuthentication::class);
        MasterCountry::create(['code' => 'IND', 'name' => 'India']);

        $this->postJson('http://localhost/api/v1/listings', [
            'name'            => 'Nishant Tomar',
            'phone'           => '+91 98765 43210',
            'country_code'    => 'IND',
            'registration_no' => 'DMC-123',
        ])->assertCreated();

        $this->postJson('http://localhost/api/v1/listings/intake', [
            'doctor_name'         => 'Dr. Nishant Tomar',
            'email'               => 'nishant@example.com',
            'mobile'              => '9876543210',
            'clinic_name'         => 'Tomar Clinic',
            'address'             => 'Sector 10',
            'country_code'        => 'IND',
            'registration_number' => 'DMC-123',
            'bio'                 => 'General physician',
            'experience_years'    => 0,
        ])->assertOk();

        $this->assertSame(1, Listing::count());

        $listing = Listing::firstOrFail();
        $this->assertSame('Dr. Nishant Tomar', $listing->name);
        $this->assertSame('nishant@example.com', $listing->email);
        $this->assertSame('Tomar Clinic', $listing->hospital_name);
        $this->assertSame('DMC-123', $listing->meta_data['registration_no']);
        $this->assertSame(0, $listing->meta_data['experience_years']);
        $this->assertFalse($listing->status);
        $this->assertSame('pending', $listing->verification_status);
    }

    public function test_repeated_final_profile_submission_remains_idempotent(): void
    {
        $this->withoutMiddleware(HmacAuthentication::class);
        MasterCountry::create(['code' => 'IND', 'name' => 'India']);

        $payload = [
            'doctor_name'         => 'Nishant Tomar',
            'email'               => 'nishant@example.com',
            'mobile'              => '9876543210',
            'clinic_name'         => 'Tomar Clinic',
            'country_code'        => 'IND',
            'registration_number' => 'DMC-123',
        ];

        $this->postJson('http://localhost/api/v1/listings/intake', $payload)->assertCreated();
        $this->postJson('http://localhost/api/v1/listings/intake', $payload)->assertOk();

        $this->assertSame(1, Listing::count());
    }
}
