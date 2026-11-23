<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'npblog:create-admin
                            {--name= : Nama administrator}
                            {--email= : Alamat email administrator}
                            {--password= : Kata sandi administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat akun administrator baru untuk NPblog';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama administrator:', 'Admin');
        $email = $this->option('email') ?: $this->ask('Email administrator:');
        $password = $this->option('password') ?: $this->secret('Kata sandi administrator (min 8 karakter):');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.unique' => 'Email :input sudah digunakan oleh akun lain.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => (string) $name,
            'email' => (string) $email,
            'password' => Hash::make((string) $password),
            'email_verified_at' => now(),
        ]);

        $this->info("Admin berhasil dibuat: {$email}");

        return self::SUCCESS;
    }
}
