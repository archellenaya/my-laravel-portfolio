<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'cms:admin {email?} {--generate : Generate a random password}';

    protected $description = 'Create a CMS administrator (does not modify existing users)';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Administrator email');
        $password = $this->option('generate') ? Str::password(24) : $this->secret('Password (at least 12 characters)');
        $validator = Validator::make(compact('email', 'password'), ['email' => 'required|email|unique:users,email', 'password' => 'required|string|min:12']);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

return self::FAILURE;
        }
        $user = new User(['name' => 'Portfolio Administrator', 'email' => $email, 'password' => Hash::make($password)]);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator created: '.$email);
        if ($this->option('generate')) {
            $this->line('Temporary password: '.$password);
        }

        return self::SUCCESS;
    }
}
