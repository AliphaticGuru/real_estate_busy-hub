<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Seed 100 random companies.
     */
    public function run(): void
    {
        Company::factory(100)->create();
    }
}
