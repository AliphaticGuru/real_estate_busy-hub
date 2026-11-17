<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed a demo account for every role.
     *
     * All seeded accounts use the password "password".
     */
    public function run(): void
    {
        $this->createUser('Super Admin', 'admin@busyhub.test', RoleName::SuperAdmin);
        $this->createUser('Demo Company', 'company@busyhub.test', RoleName::Company);
        $this->createUser('Demo Professional', 'professional@busyhub.test', RoleName::Professional);
        $this->createUser('Demo Verifier', 'verifier@busyhub.test', RoleName::Verifier);
        $this->createUser('Demo Customer', 'customer@busyhub.test', RoleName::Customer);
    }

    private function createUser(string $name, string $email, RoleName $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole($role);

        return $user;
    }
}
