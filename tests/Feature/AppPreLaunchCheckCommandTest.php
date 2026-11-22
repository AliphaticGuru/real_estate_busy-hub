<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppPreLaunchCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_passes_in_production_with_safe_configuration(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();

        $this->artisan('prelaunch:check')->assertExitCode(0);
    }

    public function test_command_passes_outside_production(): void
    {
        $this->artisan('prelaunch:check')->assertExitCode(0);
    }

    public function test_command_fails_in_production_when_debug_is_enabled(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();
        config(['app.debug' => true]);

        $this->artisan('prelaunch:check')->assertExitCode(1);
    }

    public function test_command_fails_in_production_when_app_url_is_not_https(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();
        config(['app.url' => 'http://localhost']);

        $this->artisan('prelaunch:check')->assertExitCode(1);
    }

    public function test_command_fails_in_production_when_mail_mailer_is_not_smtp(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();
        config(['mail.default' => 'log']);

        $this->artisan('prelaunch:check')->assertExitCode(1);
    }

    public function test_command_fails_in_production_when_session_cookie_is_not_secure(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();
        config(['session.secure' => false]);

        $this->artisan('prelaunch:check')->assertExitCode(1);
    }

    public function test_command_fails_in_production_when_redis_is_used_without_host(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();
        config(['session.driver' => 'redis', 'cache.stores.default.host' => null]);

        $this->artisan('prelaunch:check')->assertExitCode(1);
    }

    public function test_command_fails_in_production_when_app_name_is_the_default(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        $this->safeProductionConfig();
        config(['app.name' => 'Laravel']);

        $this->artisan('prelaunch:check')->assertExitCode(1);
    }

    /**
     * The configuration values an approved production launch must have.
     */
    private function safeProductionConfig(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://busy-hub.example.com',
            'app.name' => 'Busy Hub',
            'mail.default' => 'smtp',
            'session.secure' => true,
            'session.driver' => 'database',
            'cache.default' => 'database',
        ]);
    }
}
