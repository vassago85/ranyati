<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class SetAdminPassword extends Command
{
    protected $signature = 'admin:password
                            {email : Email address of the admin}
                            {--password= : New password (omit to be prompted securely)}
                            {--name= : Name to set when creating a new admin}
                            {--role=admin : Role to assign: admin, owner or developer}';

    protected $description = 'Create an admin or reset an existing admin\'s password';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $role = $this->option('role');
        $password = $this->option('password') ?: $this->secret('New password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'role' => $role],
            [
                'email' => 'required|email',
                'password' => 'required|string|min:8',
                'role' => 'required|in:'.implode(',', array_keys(User::ROLES)),
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $this->option('name') ?: ($existing?->name ?: 'Administrator'),
                'password' => $password,
                'role' => $existing?->role ?: $role,
            ],
        );

        $this->newLine();
        if ($existing) {
            $this->info("Password reset for {$user->email} (role: {$user->role}).");
        } else {
            $this->info("Created admin {$user->email} (role: {$user->role}).");
        }

        return self::SUCCESS;
    }
}
