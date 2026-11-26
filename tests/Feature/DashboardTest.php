<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('legacy portfolio entry points redirect to the shared login and dashboard', function () {
    $this->get('/admin/login')->assertRedirect(route('login'));
    $this->get('/admin')->assertRedirect(route('dashboard'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('portfolioAdmin', null));
});

test('administrators see the portfolio management sections on the dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('portfolioAdmin.profile', 'Sayt sozlamalari')
            ->where('portfolioAdmin.resume', 'Rezyume')
            ->where('portfolioAdmin.sections', [
                ['id' => 'projects', 'title' => 'Loyihalar'],
                ['id' => 'skills', 'title' => 'Ko‘nikmalar guruhlari'],
                ['id' => 'journey', 'title' => 'Faoliyat'],
                ['id' => 'principles', 'title' => 'Tamoyillar'],
            ]));
});
