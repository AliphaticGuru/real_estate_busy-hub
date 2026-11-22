<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ...$this->shouldSeedDemoData() ? [
                UserSeeder::class,
                DemoSeeder::class,
                CompanySeeder::class,
            ] : [],
        ]);
    }

    /**
     * Demo data is for local development and the test suite only.
     */
    private function shouldSeedDemoData(): bool
    {
        return app()->environment(['local', 'testing']);
    }
}
