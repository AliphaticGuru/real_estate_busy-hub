<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationsTest extends TestCase
{
    public function test_migrations_run_and_roll_back_cleanly(): void
    {
        $this->artisan('migrate:fresh')->assertSuccessful();

        $this->artisan('migrate:rollback')->assertSuccessful();

        $this->artisan('migrate')->assertSuccessful();
    }

    public function test_notify_inquiries_column_rolls_back_and_forwards(): void
    {
        $this->artisan('migrate:fresh')->assertSuccessful();

        $this->assertTrue(Schema::hasColumn('users', 'notify_inquiries'));

        $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

        $this->assertFalse(Schema::hasColumn('users', 'notify_inquiries'));

        $this->artisan('migrate')->assertSuccessful();

        $this->assertTrue(Schema::hasColumn('users', 'notify_inquiries'));
    }
}
