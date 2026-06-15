<?php

namespace App\Console\Commands;

use App\Models\DoctorDocument;
use App\Models\Listing;
use Illuminate\Console\Command;

class AttachOrphanDocument extends Command
{
    protected $signature = 'documents:attach-orphan
        {document_id : The orphan doctor_documents id}
        {listing_id : The listing id to attach the document to}
        {--force : Apply the change}';

    protected $description = 'Attach an orphan doctor document to an existing listing after manual verification.';

    public function handle(): int
    {
        $document = DoctorDocument::find($this->argument('document_id'));
        $listing = Listing::find($this->argument('listing_id'));

        if (! $document) {
            $this->error('Document not found.');

            return self::FAILURE;
        }

        if (! $listing) {
            $this->error('Listing not found.');

            return self::FAILURE;
        }

        $currentListing = Listing::find($document->listing_id);
        if ($currentListing) {
            $this->error("Document {$document->id} is not orphaned. It is already attached to listing {$currentListing->id}.");

            return self::FAILURE;
        }

        $this->table(
            ['document_id', 'current_listing_id', 'new_listing_id', 'new_listing_name', 'document_type', 'file_path'],
            [[
                $document->id,
                $document->listing_id,
                $listing->id,
                $listing->name,
                $document->document_type,
                $document->file_path,
            ]]
        );

        if (! $this->option('force')) {
            $this->warn('Dry run only. Re-run with --force to attach this document.');

            return self::SUCCESS;
        }

        $document->listing_id = $listing->id;
        $document->save();

        $this->info("Document {$document->id} attached to listing {$listing->id}.");

        return self::SUCCESS;
    }
}
