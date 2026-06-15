<?php

namespace Tests\Feature;

use App\Http\Middleware\HmacAuthentication;
use App\Models\DoctorDocument;
use App\Models\Listing;
use App\Models\MasterCountry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DoctorDocumentAdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_documents_on_listing_and_approve_or_reject_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        MasterCountry::create(['code' => 'IND', 'name' => 'India']);

        $listing = Listing::create([
            'name' => 'Nishant Tomar',
            'country_code' => 'IND',
            'status' => false,
            'verification_status' => 'pending',
        ]);

        $approvalDocument = DoctorDocument::create([
            'listing_id' => $listing->id,
            'document_type' => 'medical_registration',
            'file_path' => 'doctor-documents/test/registration.png',
            'original_name' => 'registration.png',
            'mime_type' => 'image/png',
            'file_size' => 2048,
            'status' => 'pending',
        ]);

        $rejectionDocument = DoctorDocument::create([
            'listing_id' => $listing->id,
            'document_type' => 'degree_certificate',
            'file_path' => 'doctor-documents/test/degree.pdf',
            'original_name' => 'degree.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4096,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get("http://localhost/listings/{$listing->id}#documents")
            ->assertOk()
            ->assertSee('Documents')
            ->assertSee('Medical Registration')
            ->assertSee('registration.png')
            ->assertSee('Approve')
            ->assertSee('Reject');

        $this->actingAs($admin)
            ->patch("http://localhost/documents/{$approvalDocument->id}/status", [
                'status' => 'approved',
                'redirect_to' => 'listing_documents',
            ])
            ->assertRedirect("http://localhost/listings/{$listing->id}#documents");

        $this->assertDatabaseHas('doctor_documents', [
            'id' => $approvalDocument->id,
            'status' => 'approved',
            'verified_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch("http://localhost/documents/{$rejectionDocument->id}/status", [
                'status' => 'rejected',
                'remarks' => 'Blurry document',
                'redirect_to' => 'listing_documents',
            ])
            ->assertRedirect("http://localhost/listings/{$listing->id}#documents");

        $this->assertDatabaseHas('doctor_documents', [
            'id' => $rejectionDocument->id,
            'status' => 'rejected',
            'remarks' => 'Blurry document',
            'verified_by' => $admin->id,
        ]);
    }

    public function test_intake_profile_submit_stores_attached_documents(): void
    {
        $this->withoutMiddleware(HmacAuthentication::class);
        Storage::fake('public');
        MasterCountry::create(['code' => 'IND', 'name' => 'India']);

        $response = $this->post('http://localhost/api/v1/listings/intake', [
            'doctor_name' => 'Nishant Tomar',
            'email' => 'nishugoldiprashant7@gmai.com',
            'mobile' => '9634894416',
            'clinic_name' => 'Tomar Clinic',
            'country_code' => 'IND',
            'documents' => [
                [
                    'document_type' => 'medical_registration',
                    'file' => UploadedFile::fake()->image('registration.png'),
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.documents_received', 1);

        $listing = Listing::firstOrFail();
        $document = DoctorDocument::firstOrFail();

        $this->assertSame($listing->id, $document->listing_id);
        $this->assertSame('medical_registration', $document->document_type);
        $this->assertSame('pending', $document->status);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_duplicate_listing_admin_pages_show_documents_from_matching_listing(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        MasterCountry::create(['code' => 'IND', 'name' => 'India']);

        $documentOwner = Listing::create([
            'name' => 'Nishant Tomar',
            'email' => 'nishugoldiprashant7@gmai.com',
            'personal_contact_no' => '9634894416',
            'hospital_name' => 'Tomar Clinic',
            'country_code' => 'IND',
            'status' => true,
            'verification_status' => 'approved',
        ]);

        $duplicate = Listing::create([
            'name' => 'Nishant Tomar',
            'email' => 'nishugoldiprashant7@gmai.com',
            'personal_contact_no' => '9634894416',
            'hospital_name' => 'Tomar Clinic',
            'country_code' => 'IND',
            'status' => true,
            'verification_status' => 'approved',
        ]);

        DoctorDocument::create([
            'listing_id' => $documentOwner->id,
            'document_type' => 'degree_certificate',
            'file_path' => 'doctor-documents/test/degree.pdf',
            'original_name' => 'degree.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4096,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get("http://localhost/listings/{$duplicate->id}#documents")
            ->assertOk()
            ->assertSee('degree.pdf')
            ->assertSee('Documents are attached to duplicate listing');

        $this->actingAs($admin)
            ->get("http://localhost/listings/{$duplicate->id}/edit#documents")
            ->assertOk()
            ->assertSee('degree.pdf')
            ->assertSee('Approve')
            ->assertSee('Reject');

        $this->actingAs($admin)
            ->get("http://localhost/listings/{$duplicate->id}/documents")
            ->assertOk()
            ->assertSee('degree.pdf')
            ->assertSee('Documents are attached to duplicate listing');
    }
}
