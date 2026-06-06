<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name',          'value' => 'TatkalDoctor',                         'type' => 'string',  'group' => 'general',       'is_public' => true],
            ['key' => 'site_tagline',        'value' => 'Find Doctors Near You',                'type' => 'string',  'group' => 'general',       'is_public' => true],
            ['key' => 'timezone',            'value' => 'Asia/Kolkata',                         'type' => 'string',  'group' => 'general',       'is_public' => false],
            ['key' => 'maintenance_mode',    'value' => '0',                                    'type' => 'boolean', 'group' => 'general',       'is_public' => false],

            // Contact
            ['key' => 'support_email',       'value' => 'support@tatkaldoctor.com',             'type' => 'string',  'group' => 'contact',       'is_public' => true],
            ['key' => 'whatsapp_no',         'value' => '',                                     'type' => 'string',  'group' => 'contact',       'is_public' => true],

            // Appearance
            ['key' => 'logo',                'value' => '',                                     'type' => 'string',  'group' => 'appearance',    'is_public' => true],
            ['key' => 'primary_color',       'value' => '#2563eb',                              'type' => 'string',  'group' => 'appearance',    'is_public' => true],

            // API
            ['key' => 'api_rate_limit',      'value' => '100',                                  'type' => 'integer', 'group' => 'api',           'is_public' => false],
            ['key' => 'hmac_tolerance_sec',  'value' => '300',                                  'type' => 'integer', 'group' => 'api',           'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
