<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
});

test('admin categories index lists categories with article counts', function () {
    $category = Category::factory()->create(['name' => 'Infrastruktur', 'slug' => 'infrastruktur']);
    Article::factory()->count(3)->create([
        'user_id' => $this->admin->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/categories');

    $response->assertOk()
        ->assertSee('Infrastruktur')
        ->assertSee('3');
});

test('admin can store new category with generated slug', function () {
    $response = $this->actingAs($this->admin)->post('/admin/categories', [
        'name' => 'Kecerdasan Buatan',
        'description' => 'Topik seputar machine learning dan AI engineering.',
    ]);

    $response->assertRedirect('/admin/categories');

    $category = Category::where('name', 'Kecerdasan Buatan')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('kecerdasan-buatan')
        ->and($category->description)->toBe('Topik seputar machine learning dan AI engineering.');
});

test('admin can update category name and description', function () {
    $category = Category::factory()->create([
        'name' => 'Nama Lama',
        'slug' => 'nama-lama',
        'description' => 'Deskripsi lama.',
    ]);

    $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
        'name' => 'Nama Baru',
        'description' => 'Deskripsi baru yang diperbarui.',
    ]);

    $response->assertRedirect('/admin/categories');

    $category->refresh();
    expect($category->name)->toBe('Nama Baru')
        ->and($category->description)->toBe('Deskripsi baru yang diperbarui.');
});

test('admin cannot delete category in use by articles', function () {
    $category = Category::factory()->create(['name' => 'Kategori Terpakai']);
    Article::factory()->count(2)->create([
        'user_id' => $this->admin->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

    $response->assertRedirect()
        ->assertSessionHas('error');

    expect(Category::find($category->id))->not->toBeNull();
});

test('admin can delete category with zero articles', function () {
    $category = Category::factory()->create(['name' => 'Kategori Kosong']);

    $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

    $response->assertRedirect('/admin/categories')
        ->assertSessionHas('success');

    expect(Category::find($category->id))->toBeNull();
});
