<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'             => 'Free',
                'slug'             => 'free',
                'description'      => 'Basic listing plan for individual doctors. No cost, limited features.',
                'price'            => 0.00,
                'duration_days'    => 365,
                'max_staff'        => 1,
                'max_locations'    => 1,
                'max_appointments' => null,
                'features'         => ['1 doctor profile', '1 location', 'Basic listing visibility', 'No appointment booking'],
                'status'           => true,
            ],
            [
                'name'             => 'Starter',
                'slug'             => 'starter',
                'description'      => 'For small clinics. Includes appointment management and multi-location support.',
                'price'            => 499.00,
                'duration_days'    => 30,
                'max_staff'        => 3,
                'max_locations'    => 2,
                'max_appointments' => 100,
                'features'         => ['Up to 3 staff profiles', '2 locations', 'Appointment booking (100/month)', 'WhatsApp notifications', 'Priority listing'],
                'status'           => true,
            ],
            [
                'name'             => 'Professional',
                'slug'             => 'professional',
                'description'      => 'For growing clinics and hospitals. Full features with higher limits.',
                'price'            => 1499.00,
                'duration_days'    => 30,
                'max_staff'        => 10,
                'max_locations'    => 5,
                'max_appointments' => 500,
                'features'         => ['Up to 10 staff profiles', '5 locations', 'Appointment booking (500/month)', 'WhatsApp & AI booking', 'Priority listing', 'Analytics dashboard', 'API access'],
                'status'           => true,
            ],
            [
                'name'             => 'Enterprise',
                'slug'             => 'enterprise',
                'description'      => 'Unlimited plan for hospital chains and large healthcare organisations.',
                'price'            => 4999.00,
                'duration_days'    => 30,
                'max_staff'        => null,
                'max_locations'    => null,
                'max_appointments' => null,
                'features'         => ['Unlimited staff profiles', 'Unlimited locations', 'Unlimited appointments', 'WhatsApp & AI booking', 'Dedicated support', 'Custom integrations', 'Full API access', 'SLA guarantee'],
                'status'           => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
