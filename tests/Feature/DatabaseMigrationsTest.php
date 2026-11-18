<?php

namespace Tests\Feature;

use Tests\TestCase;

class DatabaseMigrationsTest extends TestCase
{
    public function test_migrations_run_and_roll_back_cleanly(): void
    {
        $this->artisan('migrate:fresh')->assertSuccessful();

        // Roll back the eight migrations introduced with the Phase 1 data layer.
        $this->artisan('migrate:rollback', ['--step' => 8])->assertSuccessful();

        $this->artisan('migrate')->assertSuccessful();
    }
}
