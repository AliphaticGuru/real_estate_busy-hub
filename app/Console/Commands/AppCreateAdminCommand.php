<?php

namespace App\Console\Commands;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppCreateAdminCommand extends Command
{
    /**
     * The signature of the console command.
     */
    protected $signature = 'app:create-admin {--force : Create the admin without interactive prompts}';

    /**
     * The console command description.
     */
    protected $description = 'Create a super admin user for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->input->isInteractive()) {
            $this->error('This command must be run interactively or with --force.');

            return self::FAILURE;
        }

        $name = $this->option('force')
            ? 'Platform Admin'
            : $this->prompt('Administrator name');

        $email = $this->option('force')
            ? (string) config('app.admin_email', 'admin@busyhub.test')
            : $this->prompt('Administrator email');

        $password = $this->option('force')
            ? Hash::make(Str::random(12))
            : ($this->confirm('Generate a strong password?') ? Hash::make(Str::random(12)) : Hash::make($this->secret('Administrator password')));

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(RoleName::SuperAdmin);

        $this->info('Super admin created successfully.');

        return self::SUCCESS;
    }
}
