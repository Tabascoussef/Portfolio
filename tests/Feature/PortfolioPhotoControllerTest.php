<?php

use App\Models\PortfolioProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('shows the bundled photo on the localized portfolio homepage', function () {
    $imageUrl = route('portfolio.photo.show');

    $this->get('/en')->assertOk()->assertSee('src="'.$imageUrl.'"', false);
    $image = $this->get($imageUrl)->assertOk()->assertHeader('Content-Type', 'image/webp');

    expect($image->headers->hasCacheControlDirective('no-store'))->toBeTrue();
});

test('hides the photo and retains the administrator preview until it is enabled again', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $settings = route('portfolio-admin.profile.edit');
    $save = route('portfolio-admin.profile.photo.update');
    $preview = route('portfolio-admin.profile.photo.preview');
    $publicImage = route('portfolio.photo.show');

    $this->actingAs($admin)->get($settings)->assertOk()->assertSee('src="'.$preview.'"', false);
    $this->post($save, ['_method' => 'PUT', 'visible' => '0'])->assertRedirect($settings);

    $this->get('/en')->assertOk()->assertDontSee('src="'.$publicImage.'"', false);
    $this->get($publicImage)->assertNotFound();
    $this->get($preview)->assertOk()->assertHeader('Content-Type', 'image/webp');

    $this->post($save, ['_method' => 'PUT', 'visible' => '1'])->assertRedirect($settings);
    $this->get($publicImage)->assertOk();
});

test('replaces the current portrait while preserving the other site settings', function () {
    $disk = Storage::fake('local');
    $previous = 'portfolio/profile-photos/previous.jpg';
    $disk->put($previous, 'old photo');
    $profile = PortfolioProfile::create(['id' => PortfolioProfile::SINGLETON_ID, 'details' => [
        'about_photo_path' => $previous,
        'about_photo_visible' => false,
        'email' => 'contact@example.test',
    ]]);
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post(route('portfolio-admin.profile.photo.update'), [
        '_method' => 'PUT',
        'photo' => UploadedFile::fake()->image('replacement.png', 480, 640),
        'visible' => '1',
    ])->assertRedirect(route('portfolio-admin.profile.edit'));

    $updated = $profile->fresh();
    expect($updated->aboutPhotoVisible())->toBeTrue();
    expect($updated->details['email'])->toBe('contact@example.test');
    $disk->assertExists($updated->aboutPhotoPath());
    $disk->assertMissing($previous);
    $this->get(route('portfolio.photo.show'))->assertOk()->assertHeader('Content-Type', 'image/png');
});

test('rejects non-image uploads without changing the current photo visibility', function () {
    $disk = Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->put(route('portfolio-admin.profile.photo.update'), [
        'photo' => UploadedFile::fake()->createWithContent('not-a-photo.png', 'plain text'),
        'visible' => '0',
    ])->assertSessionHasErrors('photo');

    expect($disk->allFiles())->toBeEmpty();
    $this->get(route('portfolio.photo.show'))->assertOk()->assertHeader('Content-Type', 'image/webp');
});

test('restricts the photo preview and modification to portfolio administrators', function () {
    $preview = route('portfolio-admin.profile.photo.preview');
    $save = route('portfolio-admin.profile.photo.update');

    $this->get($preview)->assertRedirect(route('login'));
    $this->put($save, ['visible' => '0'])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['is_admin' => false]));
    $this->get($preview)->assertForbidden();
    $this->put($save, ['visible' => '0'])->assertForbidden();
});
