<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Force the testing database before the application boots.
     *
     * Defaults to an in-memory SQLite database. When a PostgreSQL connection is
     * configured through the environment (GitHub Actions service container, local
     * dev Postgres) the configured database is used instead.
     */
    protected function setUp(): void
    {
        $this->useInMemoryDatabase();

        parent::setUp();
    }

    private function useInMemoryDatabase(): void
    {
        if (env('DB_CONNECTION') === 'pgsql') {
            return;
        }

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
    }
}
