<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PreLaunchCheckCommand extends Command
{
    /**
     * The signature of the console command.
     */
    protected $signature = 'prelaunch:check';

    /**
     * The console command description.
     */
    protected $description = 'Run pre-launch configuration checks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->laravel->environment('production')) {
            return self::SUCCESS;
        }

        $failures = $this->failures();

        if ($failures === []) {
            $this->info('Pre-launch checks passed.');

            return self::SUCCESS;
        }

        foreach ($failures as $failure) {
            $this->error($failure);
        }

        $this->error('Pre-launch checks failed. Fix the issues above before launch.');

        return self::FAILURE;
    }

    /**
     * The configuration issues that block a production launch.
     *
     * @return array<int, string>
     */
    private function failures(): array
    {
        $failures = [];

        if ((bool) config('app.debug')) {
            $failures[] = 'APP_DEBUG must be false in production.';
        }

        if (str_contains((string) config('app.url'), 'localhost') || str_starts_with((string) config('app.url'), 'http://')) {
            $failures[] = 'APP_URL must be a public https URL.';
        }

        if (in_array((string) config('mail.default'), ['log', 'array'], true)) {
            $failures[] = 'MAIL_MAILER must not be "log" or "array" in production (use smtp or resend).';
        }

        if (blank(config('app.key'))) {
            $failures[] = 'APP_KEY must be set in production.';
        }

        $trustedProxies = config('app.trusted_proxies');

        if ($trustedProxies === '*' || blank($trustedProxies)) {
            $failures[] = 'TRUSTED_PROXIES must be a comma-separated list of proxy IPs/CIDRs, not "*" or empty.';
        }

        if (! (bool) config('session.secure')) {
            $failures[] = 'SESSION_SECURE_COOKIE must be true in production.';
        }

        if (
            in_array((string) config('session.driver'), ['redis'], true)
            || (string) config('cache.default') === 'redis'
        ) {
            if (blank(config('cache.stores.default.host'))) {
                $failures[] = 'Redis requires REDIS_HOST to be configured.';
            }
        }

        if ((string) config('app.name') === 'Laravel') {
            $failures[] = 'APP_NAME must not be the default "Laravel".';
        }

        return $failures;
    }
}
