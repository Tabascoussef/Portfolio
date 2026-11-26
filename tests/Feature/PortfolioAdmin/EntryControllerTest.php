<?php

use App\Enums\PortfolioSection;
use App\Models\PortfolioEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('redirects guests to the shared sign-in', function () {
    $this->get('/admin/projects')->assertRedirect(route('login'));
});

test('returns an administrator to a portfolio management page as a full browser visit after signing in', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $url = route('portfolio-admin.entries.index', ['section' => PortfolioSection::Skills, 'lang' => 'en']);

    $this->get($url)->assertRedirect(route('login'));
    $login = $this->get(route('login'))->inertiaPage();
    $inertia = ['X-Inertia' => 'true', 'X-Inertia-Version' => $login['version']];
    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'], $inertia)
        ->assertRedirect($url);

    $this->get($url, $inertia)->assertConflict()->assertHeader('X-Inertia-Location', $url);
    $this->get($url)->assertOk()->assertSee('href="'.route('dashboard').'"', false)->assertSeeText('Skill groups');
});

test('forbids a signed-in user without the administrator role from saving entries', function () {
    $user = User::factory()->create();
    $dashboard = $this->actingAs($user)->get(route('dashboard'))->inertiaPage();

    $this->get('/admin/projects', ['X-Inertia' => 'true', 'X-Inertia-Version' => $dashboard['version']])->assertForbidden();
    $this->post('/admin/projects')->assertForbidden();

    $this->assertDatabaseCount('portfolio_entries', 0);
});

test('publishes an administrator’s project edits in all three languages', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $payload = [
        'slug' => 'sample-project',
        'position' => 10,
        'translations' => [
            'uz' => ['name' => 'Namuna', 'about' => 'Loyihaning qisqa tavsifi', 'architecture' => "Birinchi\n\n Ikkinchi"],
            'en' => ['name' => 'Sample', 'about' => 'Short project description'],
            'ru' => ['name' => 'Пример'],
        ],
        'details' => ['stack' => "Laravel\nSQLite"],
    ];
    $this->post('/admin/projects', $payload)->assertRedirect('/admin/projects');
    $entry = PortfolioEntry::where('slug', 'sample-project')->firstOrFail();
    expect($entry->translations['uz']['architecture'])->toBe(['Birinchi', 'Ikkinchi']);
    $this->get('/admin/projects')->assertOk()->assertSee('/work/sample-project');
    $this->get('/admin/projects/'.$entry->id.'/edit')->assertOk()->assertSee('Loyihaning qisqa tavsifi');

    $payload['translations']['ru']['name'] = 'Новый пример';
    $payload['translations']['en']['about'] = 'Updated project description';
    $this->put('/admin/projects/'.$entry->id, $payload)->assertRedirect('/admin/projects');

    $this->get('/')->assertOk()->assertSee('data-project-card', false)->assertSeeText('Loyihaning qisqa tavsifi')->assertSeeText('SQLite');
    $this->get('/work/sample-project')->assertOk()->assertSeeText('Namuna')->assertSeeText('Loyihaning qisqa tavsifi')->assertSeeText('SQLite');
    $this->get('/en')->assertOk()->assertSeeText('Updated project description');
    $this->get('/en/work/sample-project')->assertOk()->assertSeeText('Sample')->assertSeeText('Updated project description');
    $this->get('/ru/work/sample-project')->assertOk()->assertSeeText('Новый пример');

    $this->delete('/admin/projects/'.$entry->id)->assertRedirect('/admin/projects');
    $this->assertModelMissing($entry);
    $this->get('/work/sample-project')->assertNotFound();
});

test('publishes and removes skill groups managed by an administrator', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $this->get('/admin/skills/create')->assertOk()
        ->assertSee('name="translations[uz][established]"', false)
        ->assertSee('name="translations[uz][learning]"', false);

    $payload = [
        'position' => 10,
        'translations' => [
            'uz' => ['name' => 'Dasturlash', 'established' => "PHP\nLaravel", 'learning' => 'Rust'],
            'en' => ['name' => 'Programming'],
            'ru' => ['name' => 'Программирование'],
        ],
    ];

    $this->post('/admin/skills', $payload)->assertRedirect('/admin/skills');

    $skill = PortfolioEntry::where('section', PortfolioSection::Skills)->sole();
    $anchor = 'skill-'.$skill->getKey();

    $this->get('/admin/skills')->assertOk()->assertSeeText('Dasturlash')->assertSee('#'.$anchor);
    $this->get('/admin/skills/'.$skill->getKey().'/edit')->assertOk()->assertSeeText('Rust');
    $this->get('/')->assertOk()->assertSee('id="'.$anchor.'"', false)->assertSeeText('Laravel')->assertSeeText('Rust');

    $payload['translations']['uz']['learning'] = 'Elixir';
    $this->put('/admin/skills/'.$skill->getKey(), $payload)->assertRedirect('/admin/skills');

    $this->get('/')->assertOk()->assertSeeText('Elixir')->assertDontSeeText('Rust');
    $this->get('/en')->assertOk()->assertSeeText('Programming');
    $this->get('/ru')->assertOk()->assertSeeText('Программирование');

    $this->delete('/admin/skills/'.$skill->getKey())->assertRedirect('/admin/skills');

    $this->assertModelMissing($skill);
    $this->get('/')->assertOk()->assertDontSee($anchor)->assertDontSeeText('Elixir');
});

test('administrators control public visibility in every entry section', function (PortfolioSection $section) {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $index = '/admin/'.$section->value;
    $name = 'Sinov yozuvi';
    $payload = [
        'position' => 10,
        'is_active' => '0',
        'translations' => ['uz' => ['name' => $name], 'en' => ['name' => 'Test entry'], 'ru' => ['name' => 'Тестовая запись']],
    ];
    if ($section === PortfolioSection::Projects) {
        $payload['slug'] = 'test-entry';
    }

    $this->get($index.'/create')->assertOk()->assertSee('role="switch"', false);
    $this->post($index, $payload)->assertRedirect($index);

    $entry = PortfolioEntry::where('section', $section)->sole();
    $editUrl = $index.'/'.$entry->getKey();
    $visibilityUrl = $editUrl.'/visibility';
    expect($entry->is_active)->toBeFalse();
    $this->get($index)->assertOk()->assertSeeText($name)->assertSee($visibilityUrl);
    $this->get('/')->assertOk()->assertDontSeeText($name);

    $this->patch($visibilityUrl, ['is_active' => '1'])->assertRedirect($index);

    expect($entry->refresh()->is_active)->toBeTrue();
    $this->get('/')->assertOk()->assertSeeText($name);
    $this->get($editUrl.'/edit')->assertOk()->assertSee('role="switch"', false);

    $this->put($editUrl, $payload)->assertRedirect($index);

    expect($entry->refresh()->is_active)->toBeFalse();
    $this->get('/')->assertOk()->assertDontSeeText($name);
})->with(PortfolioSection::cases());

test('requires the three project titles and rejects unsafe gallery URLs', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post('/admin/projects?lang=en', [
        'slug' => 'sample-project',
        'position' => 10,
        'translations' => ['uz' => ['name' => 'Namuna']],
        'details' => ['images' => 'ftp://example.test/image.png'],
    ])->assertSessionHasErrors(['translations.en.name', 'translations.ru.name', 'details.images.0']);

    $this->assertDatabaseCount('portfolio_entries', 0);
});

test('publishes project links and galleries and removes uploaded images on edit and delete', function () {
    $disk = Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    $cover = 'https://images.example.test/cover.png';
    $payload = [
        'slug' => 'gallery-project',
        'position' => 10,
        'translations' => ['uz' => ['name' => 'Galereya'], 'en' => ['name' => 'Gallery'], 'ru' => ['name' => 'Галерея']],
        'details' => [
            'github' => 'https://git.example.test/gallery',
            'live' => 'gallery.example.test',
            'images' => $cover,
        ],
    ];

    $this->actingAs($admin)->get('/admin/projects/create')->assertOk()
        ->assertSee('type="file" name="image_uploads[]" multiple', false)
        ->assertDontSee('name="details[images]"', false);

    $this->post('/admin/projects', $payload + [
        'image_uploads' => [
            UploadedFile::fake()->createWithContent('first.gif', $gif),
            UploadedFile::fake()->createWithContent('second.gif', $gif),
        ],
    ])->assertRedirect('/admin/projects');

    $entry = PortfolioEntry::where('slug', 'gallery-project')->firstOrFail();
    [$first, $second] = $entry->details['image_paths'];
    $disk->assertExists([$first, $second]);
    $editUrl = '/admin/projects/'.$entry->id.'/edit';
    $imageUrl = '/admin/projects/'.$entry->id.'/images';
    $this->get($editUrl)->assertOk()
        ->assertSee($imageUrl)
        ->assertSee('name="image" value="'.$cover.'"', false)
        ->assertDontSee('name="details[images]"', false);
    $this->get('/en')->assertOk()
        ->assertSee('data-gallery-track', false)
        ->assertSeeInOrder(['/storage/'.$first, '/storage/'.$second, $cover])
        ->assertSee('href="https://git.example.test/gallery"', false)
        ->assertSee('href="https://gallery.example.test"', false);
    $this->get('/en/work/gallery-project')->assertOk()->assertSee('data-gallery-track', false);

    $this->delete($imageUrl, ['image' => $first])->assertRedirect($editUrl);

    $disk->assertMissing($first);
    $disk->assertExists($second);
    $this->get('/en')->assertOk()->assertSee($cover)->assertSee('data-gallery-track', false);

    $this->delete($imageUrl, ['image' => $cover])->assertRedirect($editUrl);
    $this->get('/en')->assertOk()
        ->assertSee('/storage/'.$second)->assertDontSee($cover)->assertDontSee('data-gallery-track', false)
        ->assertSee('href="https://git.example.test/gallery"', false);

    $this->delete('/admin/projects/'.$entry->id)->assertRedirect('/admin/projects');

    $disk->assertMissing($second);
    $this->assertModelMissing($entry);
});

test('rejects a non-image upload without saving the project', function () {
    $disk = Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post('/admin/projects', [
        'slug' => 'invalid-image',
        'position' => 10,
        'translations' => ['uz' => ['name' => 'Loyiha'], 'en' => ['name' => 'Project'], 'ru' => ['name' => 'Проект']],
        'image_uploads' => [UploadedFile::fake()->createWithContent('fake.png', 'not an image')],
    ])->assertSessionHasErrors('image_uploads.0');

    expect($disk->allFiles())->toBeEmpty();
    $this->assertDatabaseCount('portfolio_entries', 0);
});

test('rejects a record belonging to a different portfolio section', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $entry = PortfolioEntry::create([
        'section' => 'principles',
        'translations' => ['uz' => ['name' => 'Maqsad']],
    ]);

    $this->actingAs($admin)->delete('/admin/skills/'.$entry->id)->assertNotFound();
    $this->patch('/admin/skills/'.$entry->id.'/visibility', ['is_active' => '0'])->assertNotFound();
    $this->delete('/admin/projects/'.$entry->id.'/images', ['image' => 'portfolio/projects/image.png'])->assertNotFound();

    $this->assertModelExists($entry);
});
