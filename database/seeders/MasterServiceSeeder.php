<?php

namespace Database\Seeders;

use App\Models\MasterService;
use Illuminate\Database\Seeder;

class MasterServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            'General Physician',
            'Cardiologist',
            'Dermatologist',
            'Orthopedic',
            'Pediatrician',
            'Gynecologist',
            'Neurologist',
            'Psychiatrist',
            'Ophthalmologist',
            'ENT Specialist',
            'Dentist',
            'Urologist',
        ];

        foreach ($services as $service) {
            MasterService::firstOrCreate(
                ['service' => $service, 'parent_id' => 0],
                ['status' => true]
            );
        }
    }
}
