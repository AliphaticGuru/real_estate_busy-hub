<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Force the testing database before the application boots.
     *
     * The Docker dev environment injects DB_* variables via docker-compose,
     * which take precedence over phpunit.xml, so tests would otherwise run
     * against the development PostgreSQL database.
     */
    protected function setUp(): void
    {
        $this->useInMemoryDatabase();

        parent::setUp();
    }

    private function useInMemoryDatabase(): void
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
    }
}
