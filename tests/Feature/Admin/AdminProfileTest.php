<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Dapit Asli',
        'email' => 'dapit@npblog.id',
        'password' => Hash::make('secret-lama'),
    ]);
});

test('admin profile page can be accessed via /admin/profile or /profile', function () {
    $this->actingAs($this->admin)->get('/profile')
        ->assertOk()
        ->assertSee('Dapit Asli')
        ->assertSee('dapit@npblog.id');

    $this->actingAs($this->admin)->get('/admin/profile')
        ->assertOk()
        ->assertSee('Dapit Asli')
        ->assertSee('dapit@npblog.id');
});

test('admin profile name and email can be updated with unique validation', function () {
    $other = User::factory()->create(['email' => 'terpakai@npblog.id']);

    // Attempt to use already used email
    $responseFail = $this->actingAs($this->admin)->patch('/profile', [
        'name' => 'Nama Baru',
        'email' => 'terpakai@npblog.id',
    ]);
    $responseFail->assertSessionHasErrors(['email']);

    // Update with unique email
    $responseOk = $this->actingAs($this->admin)->patch('/profile', [
        'name' => 'Nama Baru Dapit',
        'email' => 'baru@npblog.id',
    ]);
    $responseOk->assertSessionHasNoErrors()->assertRedirect('/profile');

    $this->admin->refresh();
    expect($this->admin->name)->toBe('Nama Baru Dapit')
        ->and($this->admin->email)->toBe('baru@npblog.id');
});

test('admin password change requires correct current password', function () {
    // Wrong current password
    $responseWrong = $this->actingAs($this->admin)->from('/profile')->put('/password', [
        'current_password' => 'salah-password',
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ]);
    $responseWrong->assertSessionHasErrorsIn('updatePassword', 'current_password');

    // Correct current password
    $responseCorrect = $this->actingAs($this->admin)->from('/profile')->put('/password', [
        'current_password' => 'secret-lama',
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ]);
    $responseCorrect->assertSessionHasNoErrors();

    $this->admin->refresh();
    expect(Hash::check('password-baru-123', $this->admin->password))->toBeTrue();
});

test('admin user account cannot be deleted via profile route', function () {
    $this->actingAs($this->admin)->delete('/profile', [
        'password' => 'secret-lama',
    ])->assertMethodNotAllowed();

    expect($this->admin->fresh())->not->toBeNull();
});
