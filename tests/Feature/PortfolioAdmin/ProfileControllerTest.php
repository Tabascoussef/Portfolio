<?php

use App\Models\PortfolioProfile;
use App\Models\User;
use Database\Seeders\PortfolioSeeder;

test('publishes shared settings and the selected translation', function () {
    $this->seed(PortfolioSeeder::class);
    $admin = User::factory()->create(['is_admin' => true]);
    $profile = PortfolioProfile::findOrFail(PortfolioProfile::SINGLETON_ID);
    $resumePath = 'portfolio/resumes/current.pdf';
    $profile->update(['details' => array_replace($profile->details, ['resume_path' => $resumePath])]);
    $details = array_replace($profile->details, ['email' => 'contact@example.test']);
    $translations = $profile->translations;
    $translations['ru']['location'] = 'Андижан';

    $this->actingAs($admin)->get(route('portfolio-admin.profile.edit'))->assertOk()->assertDontSee('name="details[resume]"', false);
    $this->put(route('portfolio-admin.profile.update', ['lang' => 'ru']), compact('details', 'translations'))
        ->assertRedirect(route('portfolio-admin.profile.edit'))
        ->assertSessionHas('status', 'Изменения сохранены.');

    expect($profile->fresh()->details['email'])->toBe('contact@example.test');
    expect($profile->fresh()->resumePath())->toBe($resumePath);
    $this->get('/ru')->assertOk()->assertSeeText('Андижан')->assertSee('mailto:contact@example.test');
    $this->get('/en')->assertOk()->assertSeeText('Based in Uzbekistan.');
});
