<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreatePortfolioAdmin extends Command
{
    protected $signature = 'portfolio:admin {email? : Admin email address}';

    protected $description = 'Create a portfolio administrator';

    public function handle(): int
    {
        $email = Str::lower(trim((string) ($this->argument('email') ?? $this->ask('Email'))));
        $name = trim((string) $this->ask('Name'));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Repeat password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', 'min:12', Password::defaults()],
            ],
        );

        if ($validator->fails() || strlen($password) > 72) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            if (strlen($password) > 72) {
                $this->error('The password must be at most 72 bytes.');
            }

            return self::FAILURE;
        }

        $user = new User($validator->safe()->only(['name', 'email', 'password']));
        $user->is_admin = true;
        $user->email_verified_at = now();
        $user->save();

        $this->info('Portfolio administrator created. Sign in at /login.');

        return self::SUCCESS;
    }
}
