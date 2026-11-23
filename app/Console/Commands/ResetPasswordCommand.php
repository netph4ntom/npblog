<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'npblog:reset-password
                            {email? : Alamat email administrator}
                            {--password= : Kata sandi baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset kata sandi akun administrator NPblog';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Email administrator:');

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("User dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Kata sandi baru (min 8 karakter):');

        $validator = Validator::make([
            'password' => $password,
        ], [
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user->update([
            'password' => Hash::make((string) $password),
        ]);

        $this->info("Kata sandi untuk {$email} berhasil diperbarui.");

        return self::SUCCESS;
    }
}
