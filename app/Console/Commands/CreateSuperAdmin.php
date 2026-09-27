<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-super-admin {name? : Full name} {email? : Email address} {password? : Password (min 8 characters)}')]
#[Description('Create a super_admin user. Prompts for any missing arguments.')]
class CreateSuperAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->ask('Ім’я');
        $email = $this->argument('email') ?? $this->ask('Email');
        $password = $this->argument('password') ?? $this->secret('Пароль (мінімум 8 символів)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::defaults()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::SuperAdmin,
            'email_verified_at' => now(),
        ]);

        $this->info("Супер адміна створено: {$user->email} (id {$user->id}).");

        return self::SUCCESS;
    }
}
