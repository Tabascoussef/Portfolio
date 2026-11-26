<?php

use App\Enums\PortfolioSection;
use App\Models\PortfolioEntry;
use Database\Seeders\PortfolioSeeder;

test('renders the original portfolio in the requested language', function (string $url, string $language, string $heading, string $workUrl) {
    $this->seed(PortfolioSeeder::class);

    $this->get($url)->assertOk()
        ->assertSee('lang="'.$language.'"', false)
        ->assertSeeText($heading)
        ->assertSee($workUrl);
})->with([
    'default Uzbek' => ['/', 'uz', 'KELAJAKNI', '/work/grantform'],
    'English' => ['/en', 'en', 'BUILDING', '/en/work/grantform'],
    'Russian' => ['/ru', 'ru', 'СТРОЮ', '/ru/work/grantform'],
]);

test('keeps the project slug when changing languages', function () {
    $this->seed(PortfolioSeeder::class);

    $this->get('/ru/work/grantform')->assertOk()
        ->assertSeeText('Приложение для обработки заявок')
        ->assertSee('/en/work/grantform')
        ->assertSee('/ru/work/gentree');
});

test('returns a not found page for an unknown project', function () {
    $this->get('/en/work/unknown-project')->assertNotFound();
});

test('skips hidden projects in detail pages and the sitemap', function () {
    foreach (['first-project' => true, 'hidden-project' => false, 'next-project' => true] as $slug => $active) {
        PortfolioEntry::create([
            'section' => PortfolioSection::Projects,
            'slug' => $slug,
            'is_active' => $active,
            'translations' => ['en' => ['name' => $slug]],
        ]);
    }

    $this->get('/en/work/hidden-project')->assertNotFound();
    $this->get('/en/work/first-project')->assertOk()->assertSee('/en/work/next-project')->assertDontSee('hidden-project');
    $this->get('/sitemap.xml')->assertOk()->assertSee('/en/work/next-project')->assertDontSee('hidden-project');
});
