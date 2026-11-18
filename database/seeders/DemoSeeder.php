<?php

namespace Database\Seeders;

use App\Enums\CompanyStatus;
use App\Enums\ListingStatus;
use App\Enums\ListingVerificationStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Create a demo company with listings in mixed verification states.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'acme-realty'],
            [
                'name' => 'Acme Realty',
                'slug' => 'acme-realty',
                'description' => 'Verified real estate and construction partner for Abuja.',
                'email' => 'hello@acme-realty.test',
                'phone' => '+2348000000001',
                'whatsapp_number' => '+2348000000001',
                'address' => 'Abuja',
                'city' => 'Abuja',
                'state' => 'Federal Capital Territory',
                'status' => CompanyStatus::Active->value,
            ],
        );

        $companyOwner = User::where('email', 'company@busyhub.test')->firstOrCreate(
            ['email' => 'company@busyhub.test'],
            [
                'name' => 'Demo Company',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $companyOwner->company_id = $company->id;
        $companyOwner->save();

        $verifier = User::where('email', 'verifier@busyhub.test')->firstOrCreate(
            ['email' => 'verifier@busyhub.test'],
            [
                'name' => 'Demo Verifier',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        Listing::create([
            'company_id' => $company->id,
            'created_by' => $companyOwner->id,
            'title' => 'Grand Villa in Gwarinpa',
            'slug' => 'acme-grand-villa',
            'description' => 'Four bedroom grand villa with fenced land in Gwarinpa.',
            'property_type' => 'villa',
            'price' => 850_000_000,
            'city' => 'Abuja',
            'district' => 'Gwarinpa',
            'address' => 'Plot 12, Gwarinpa',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'size_sqm' => 450,
            'status' => ListingStatus::Published->value,
            'verification_status' => ListingVerificationStatus::Verified->value,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);

        Listing::create([
            'company_id' => $company->id,
            'created_by' => $companyOwner->id,
            'title' => 'Office Suite for Rent',
            'slug' => 'acme-office-suite',
            'description' => 'Two bedroom office suite in Wuse II.',
            'property_type' => 'office',
            'price' => 120_000_000,
            'city' => 'Abuja',
            'district' => 'Wuse II',
            'address' => 'Wuse II',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'size_sqm' => 90,
            'status' => ListingStatus::Draft->value,
            'verification_status' => ListingVerificationStatus::Pending->value,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        Listing::create([
            'company_id' => $company->id,
            'created_by' => $companyOwner->id,
            'title' => 'Townhouse in Kubwa',
            'slug' => 'acme-townhouse',
            'description' => 'Three bedroom townhouse awaiting final verification.',
            'property_type' => 'townhouse',
            'price' => 650_000_000,
            'city' => 'Abuja',
            'district' => 'Kubwa',
            'address' => 'Kubwa',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'size_sqm' => 180,
            'status' => ListingStatus::Published->value,
            'verification_status' => ListingVerificationStatus::InReview->value,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }
}
