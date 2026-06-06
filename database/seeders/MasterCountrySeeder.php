<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['code' => 'IND', 'name' => 'India'],
        ];

        foreach ($countries as $country) {
            \App\Models\MasterCountry::updateOrCreate(
                ['code' => $country['code']],
                ['name' => $country['name']]
            );
        }
    }
}
