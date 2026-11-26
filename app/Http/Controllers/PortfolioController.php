<?php

namespace App\Http\Controllers;

use App\Enums\PortfolioLocale;
use App\Enums\PortfolioSection;
use App\Models\PortfolioEntry;
use App\Models\PortfolioProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortfolioController extends Controller
{
    public function home(): View
    {
        return view('portfolio.home', [
            'locale' => PortfolioLocale::from(app()->getLocale()),
            'site' => $this->profile(),
            'entries' => PortfolioEntry::query()->active()->ordered()->get()->groupBy(fn (PortfolioEntry $entry) => $entry->section->value),
        ]);
    }

    public function show(string $slug): View
    {
        $projects = PortfolioEntry::query()->active()->where('section', PortfolioSection::Projects)->ordered()->get();
        $index = $projects->search(fn (PortfolioEntry $entry) => $entry->slug === $slug);

        abort_if($index === false, 404);

        return view('portfolio.project', [
            'locale' => PortfolioLocale::from(app()->getLocale()),
            'site' => $this->profile(),
            'entry' => $projects[$index],
            'next' => $projects[($index + 1) % $projects->count()],
        ]);
    }

    public function sitemap(): Response
    {
        $slugs = PortfolioEntry::query()->active()->where('section', PortfolioSection::Projects)->ordered()->pluck('slug');
        $urls = collect(PortfolioLocale::cases())->flatMap(
            fn (PortfolioLocale $locale) => $slugs->map(fn (string $slug) => $locale->publicUrl($slug))->prepend($locale->publicUrl()),
        );

        return response()->view('portfolio.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml']);
    }

    public function downloadResume(): StreamedResponse
    {
        $path = PortfolioProfile::find(PortfolioProfile::SINGLETON_ID)?->resumePath();
        $disk = Storage::disk('local');

        abort_unless($path !== null && $disk->exists($path), 404);

        return $disk->download($path, 'resume.'.pathinfo($path, PATHINFO_EXTENSION), [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function robots(): Response
    {
        $sitemap = route('sitemap');

        return response("User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: {$sitemap}\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /** @return array<string, bool|string|null> */
    private function profile(): array
    {
        $profile = PortfolioProfile::findOrNew(PortfolioProfile::SINGLETON_ID);

        return array_replace($profile->localized(), [
            'resume' => $profile->resumeUrl(),
            'about_photo' => $profile->aboutPhotoVisible() ? route('portfolio.photo.show') : null,
        ]);
    }
}
