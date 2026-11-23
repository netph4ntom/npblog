<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login page contains noindex robots meta tag', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('name="robots" content="noindex', false);
});

test('login failure returns generic error message for non-existent and wrong password credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@npblog.local',
        'password' => Hash::make('password123'),
    ]);

    // Test 1: Non-existent email
    $res1 = $this->post('/login', [
        'email' => 'unknown@npblog.local',
        'password' => 'any-password',
    ]);
    $res1->assertSessionHasErrors('email');
    $error1 = session('errors')->first('email');

    // Test 2: Existing email but wrong password
    $res2 = $this->post('/login', [
        'email' => 'admin@npblog.local',
        'password' => 'wrong-password',
    ]);
    $res2->assertSessionHasErrors('email');
    $error2 = session('errors')->first('email');

    // Both error messages must be strictly identical so attacker cannot enumerate emails
    expect($error1)->toBe($error2);
});

test('login requests are rate limited after 5 failed attempts', function () {
    $email = 'bruteforce@npblog.local';

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-pass',
        ]);
    }

    // 6th attempt must be blocked by rate limiter
    $response = $this->post('/login', [
        'email' => $email,
        'password' => 'wrong-pass',
    ]);

    $response->assertSessionHasErrors('email');
    $errorMessage = session('errors')->first('email');

    // Message comes from auth.throttle
    expect($errorMessage)->toMatch('/(terlalu banyak|too many|coba lagi dalam|seconds)/i');
});

test('guest accessing /admin is redirected to /login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

test('authenticated user can access /admin and page contains noindex tag', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk()
        ->assertSee('name="robots" content="noindex', false);
});

test('php artisan npblog:create-admin creates a new administrator user', function () {
    $this->artisan('npblog:create-admin', [
        '--name' => 'Dapit',
        '--email' => 'dapit@npblog.local',
        '--password' => 'secure-password-123',
    ])->assertSuccessful()
        ->expectsOutputToContain('Admin berhasil dibuat: dapit@npblog.local');

    $user = User::where('email', 'dapit@npblog.local')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Dapit')
        ->and(Hash::check('secure-password-123', $user->password))->toBeTrue();
});

test('php artisan npblog:create-admin rejects duplicate email', function () {
    User::factory()->create(['email' => 'existing@npblog.local']);

    $this->artisan('npblog:create-admin', [
        '--name' => 'Admin Dua',
        '--email' => 'existing@npblog.local',
        '--password' => 'secure-password-123',
    ])->assertFailed()
        ->expectsOutputToContain('sudah digunakan');
});

test('php artisan npblog:reset-password updates password for existing user', function () {
    $user = User::factory()->create([
        'email' => 'admin@npblog.local',
        'password' => Hash::make('old-password'),
    ]);

    $this->artisan('npblog:reset-password', [
        'email' => 'admin@npblog.local',
        '--password' => 'new-secret-password-99',
    ])->assertSuccessful()
        ->expectsOutputToContain('berhasil diperbarui');

    $user->refresh();
    expect(Hash::check('new-secret-password-99', $user->password))->toBeTrue();
});

test('php artisan npblog:reset-password fails when user is not found', function () {
    $this->artisan('npblog:reset-password', [
        'email' => 'unknown@npblog.local',
        '--password' => 'new-password-123',
    ])->assertFailed()
        ->expectsOutputToContain('tidak ditemukan');
});
