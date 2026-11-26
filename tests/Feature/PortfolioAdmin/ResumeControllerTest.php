<?php

use App\Models\PortfolioProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('manages a single downloadable resume file', function () {
    $disk = Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);
    $editUrl = route('portfolio-admin.resume.edit');
    $downloadUrl = route('portfolio.resume.download');
    $original = "%PDF-1.4\nOriginal resume\n%%EOF";
    $replacement = "%PDF-1.4\nUpdated resume\n%%EOF";

    $this->get($downloadUrl)->assertNotFound();
    $this->actingAs($admin)->get($editUrl)->assertOk()->assertSee('name="resume" type="file"', false);

    $this->post($editUrl, ['_method' => 'PUT', 'resume' => UploadedFile::fake()->createWithContent('original.pdf', $original)])
        ->assertRedirect($editUrl);

    $profile = PortfolioProfile::findOrFail(PortfolioProfile::SINGLETON_ID);
    $originalPath = $profile->resumePath();
    $disk->assertExists($originalPath);
    $this->get('/')->assertOk()
        ->assertSeeInOrder(['id="home"', 'href="'.$downloadUrl.'" download', 'Rezyumeni yuklab olish', 'id="about"'], false);
    $this->get('/en')->assertOk()->assertSeeText('Download resume');
    $this->get('/ru')->assertOk()->assertSeeText('Скачать резюме');
    $this->get($downloadUrl)->assertDownload('resume.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->post($editUrl, ['_method' => 'PUT', 'resume' => UploadedFile::fake()->createWithContent('updated.pdf', $replacement)])
        ->assertRedirect($editUrl);

    $updatedPath = $profile->refresh()->resumePath();
    $disk->assertMissing($originalPath);
    $disk->assertExists($updatedPath);
    expect($this->get($downloadUrl)->assertDownload('resume.pdf')->streamedContent())->toBe($replacement);

    $this->delete($editUrl)->assertRedirect($editUrl);

    $disk->assertMissing($updatedPath);
    $this->get($downloadUrl)->assertNotFound();
    $this->get('/')->assertOk()->assertDontSee($downloadUrl)->assertDontSeeText('Rezyumeni yuklab olish');
});

test('rejects an unsupported resume upload', function () {
    $disk = Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);
    $editUrl = route('portfolio-admin.resume.edit');

    $this->actingAs($admin)->from($editUrl)->post($editUrl, [
        '_method' => 'PUT',
        'resume' => UploadedFile::fake()->createWithContent('resume.html', '<p>Resume</p>'),
    ])->assertRedirect($editUrl)->assertSessionHasErrors('resume');

    expect($disk->allFiles())->toBeEmpty();
    $this->get(route('portfolio.resume.download'))->assertNotFound();
});

test('forbids members from changing the resume', function () {
    $member = User::factory()->create();
    $editUrl = route('portfolio-admin.resume.edit');
    $this->actingAs($member);

    $this->get($editUrl)->assertForbidden();
    $this->put($editUrl)->assertForbidden();
    $this->delete($editUrl)->assertForbidden();
});
