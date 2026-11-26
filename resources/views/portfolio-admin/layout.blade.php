<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ __('admin.title') }}</title>
    @vite('resources/css/portfolio-admin.css')
</head>
<body>
    <header class="admin-header">
        <div class="admin-header-inner">
            <a class="admin-brand" href="{{ route('dashboard') }}"><span aria-hidden="true">←</span> {{ __('admin.dashboard') }}</a>
            <div class="header-actions">
                <nav class="languages" aria-label="{{ __('admin.language') }}">
                    @foreach (\App\Enums\PortfolioLocale::cases() as $language)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $language->value]) }}" lang="{{ $language->value }}"
                            @if (app()->getLocale() === $language->value) aria-current="true" @endif>{{ $language->label() }}</a>
                    @endforeach
                </nav>
                <a href="{{ \App\Enums\PortfolioLocale::from(app()->getLocale())->publicUrl() }}">{{ __('admin.site') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button header-button" type="submit">{{ __('admin.logout') }}</button>
                </form>
            </div>
        </div>
    </header>
    <main class="admin-body">
        @if (session('status'))
            <p class="status" role="status">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>

