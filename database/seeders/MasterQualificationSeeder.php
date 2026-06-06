<?php

namespace Database\Seeders;

use App\Models\MasterQualification;
use Illuminate\Database\Seeder;

class MasterQualificationSeeder extends Seeder
{
    public function run(): void
    {
        $qualifications = ['MBBS', 'MD', 'MS', 'BDS', 'MDS', 'BAMS', 'BHMS', 'DNB', 'DM', 'MCh'];

        foreach ($qualifications as $qualification) {
            MasterQualification::firstOrCreate(
                ['qualification' => $qualification],
                ['status' => true]
            );
        }
    }
}
