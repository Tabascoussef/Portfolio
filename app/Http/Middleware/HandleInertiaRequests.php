<?php

namespace App\Http\Middleware;

use App\Enums\PortfolioLocale;
use App\Enums\PortfolioSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'portfolioAdmin' => fn () => $this->portfolioAdmin($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /** @return array<string, mixed>|null */
    private function portfolioAdmin(Request $request): ?array
    {
        if (Gate::denies('manage-portfolio')) {
            return null;
        }

        $locale = $request->session()->get('portfolio_locale', PortfolioLocale::Uzbek->value);

        return [
            'locale' => $locale,
            'title' => __('admin.title', locale: $locale),
            'profile' => __('admin.profile', locale: $locale),
            'resume' => __('portfolio.resume', locale: $locale),
            'list' => __('admin.list', locale: $locale),
            'add' => __('admin.add', locale: $locale),
            'edit' => __('admin.edit', locale: $locale),
            'sections' => array_map(fn (PortfolioSection $section) => [
                'id' => $section->value,
                'title' => __('admin.sections.'.$section->value, locale: $locale),
            ], PortfolioSection::cases()),
        ];
    }
}
