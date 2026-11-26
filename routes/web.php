<?php

use App\Enums\PortfolioLocale;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortfolioAdmin\EntryController;
use App\Http\Controllers\PortfolioAdmin\ProfileController;
use App\Http\Controllers\PortfolioAdmin\ResumeController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PortfolioPhotoController;
use App\Http\Middleware\FullPageNavigation;
use App\Http\Middleware\SetPortfolioLocale;
use Illuminate\Support\Facades\Route;

foreach (PortfolioLocale::cases() as $locale) {
    $prefix = $locale === PortfolioLocale::Uzbek ? '' : $locale->value;

    Route::prefix($prefix)
        ->name($prefix === '' ? '' : $prefix.'.')
        ->middleware(SetPortfolioLocale::class.':'.$locale->value)
        ->group(function (): void {
            Route::get('/', [PortfolioController::class, 'home'])->name('home');
            Route::get('/work/{slug}', [PortfolioController::class, 'show'])->name('work.show');
        });
}

Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PortfolioController::class, 'robots'])->name('robots');
Route::get('/resume', [PortfolioController::class, 'downloadResume'])->name('portfolio.resume.download');
Route::get('/about-photo', [PortfolioPhotoController::class, 'show'])->name('portfolio.photo.show');

Route::prefix('admin')->name('portfolio-admin.')->middleware(SetPortfolioLocale::class)->group(function (): void {
    Route::get('/login', fn () => to_route('login'))->name('login');
    Route::get('/', fn () => to_route('dashboard'))->name('index');

    Route::middleware(['auth', 'can:manage-portfolio', FullPageNavigation::class])->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/photo', [PortfolioPhotoController::class, 'preview'])->name('profile.photo.preview');
        Route::put('/profile/photo', [PortfolioPhotoController::class, 'update'])->name('profile.photo.update');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.legacy-update');
        Route::get('/resume', [ResumeController::class, 'edit'])->name('resume.edit');
        Route::put('/resume', [ResumeController::class, 'update'])->name('resume.update');
        Route::delete('/resume', [ResumeController::class, 'destroy'])->name('resume.destroy');
        Route::delete('/projects/{entry}/images', [EntryController::class, 'destroyImage'])->name('projects.images.destroy');

        Route::prefix('{section}')->name('entries.')->group(function (): void {
            Route::get('/', [EntryController::class, 'index'])->name('index');
            Route::get('/create', [EntryController::class, 'create'])->name('create');
            Route::post('/', [EntryController::class, 'store'])->name('store');
            Route::get('/{entry}/edit', [EntryController::class, 'edit'])->name('edit');
            Route::put('/{entry}', [EntryController::class, 'update'])->name('update');
            Route::patch('/{entry}/visibility', [EntryController::class, 'updateVisibility'])->name('visibility.update');
            Route::delete('/{entry}', [EntryController::class, 'destroy'])->name('destroy');
        });
    });
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
