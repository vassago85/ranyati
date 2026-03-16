<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'paul@charsley.co.za');
        $password = env('ADMIN_PASSWORD', 'changeme');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Developer',
                'password' => $password,
                'role' => User::ROLE_DEVELOPER,
            ],
        );
    }
}
