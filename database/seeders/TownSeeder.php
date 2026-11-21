<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\ListingVerificationStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class TownSeeder extends Seeder
{
    public function run(): void
    {
        $towns = [
            ['name' => 'Gwarinpa', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Wuse', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Wuse II', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Maitama', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Asokoro', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Central Business District', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Kubwa', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Peripheral'],
            ['name' => 'Jabi', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Peripheral'],
            ['name' => 'Nyanya', 'state' => 'Nasarawa', 'region' => 'Abuja Satellite'],
            ['name' => 'Kuje', 'state' => 'Nasarawa', 'region' => 'Abuja Satellite'],
            ['name' => 'Garki', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Central'],
            ['name' => 'Utako', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Central'],
            ['name' => 'Gwarimpa', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Guzape', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Metropolitan'],
            ['name' => 'Bwari', 'state' => 'Federal Capital Territory', 'region' => 'Abuja Rural'],
            ['name' => 'Karu', 'state' => 'Nassarawa', 'region' => 'Abuja Satellite'],
            ['name' => 'Masaka', 'state' => 'Nassarawa', 'region' => 'Abuja Satellite'],
            ['name' => 'Goronyo', 'state' => 'Nassarawa', 'region' => 'Abuja Satellite'],
        ];

        $company = Company::first();
        if (! $company) {
            $company = Company::create([
                'name' => 'Abuja Real Estate Hub',
                'slug' => 'abuja-real-estate-hub',
                'description' => 'Real estate company based in Abuja.',
                'email' => 'info@abuja-re.com',
                'phone' => '+2348000000002',
                'whatsapp_number' => '+2348000000002',
                'address' => 'Abuja',
                'city' => 'Abuja',
                'state' => 'Federal Capital Territory',
                'status' => 'active',
            ]);
        }

        $user = User::where('email', 'company@busyhub.test')->first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Demo Company',
                'email' => 'company@busyhub.test',
            ]);
        }

        foreach ($towns as $town) {
            Listing::firstOrCreate(
                ['city' => $town['name']],
                [
                    'company_id' => $company->id,
                    'created_by' => $user->id,
                    'title' => ucfirst($town['name']).' Area Development',
                    'slug' => strtolower(str_replace(' ', '-', $town['name'])).'-area-development',
                    'city' => $town['name'],
                    'district' => $town['name'],
                    'address' => $town['name'].' District, '.$town['state'],
                    'description' => 'Residential and commercial properties in '.$town['name'].', '.$town['region'].', Abuja, Nigeria',
                    'property_type' => 'land',
                    'price' => rand(50000000, 200000000),
                    'bedrooms' => null,
                    'bathrooms' => null,
                    'size_sqm' => null,
                    'status' => ListingStatus::Draft->value,
                    'verification_status' => ListingVerificationStatus::Pending->value,
                ]
            );
        }
    }
}
