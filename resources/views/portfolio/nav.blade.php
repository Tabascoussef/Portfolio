<header class="fixed inset-x-0 top-0 z-50 border-b border-emerald/40 bg-obsidian/80 backdrop-blur-sm">
    <div class="portfolio-container flex items-center justify-between gap-3 py-4">
        <a href="#home" class="display text-xl font-bold" aria-label="{{ __('portfolio.nav.home') }}">{{ $site['short'] ?? 'YUSUF.' }}</a>
        <nav aria-label="{{ __('portfolio.navigation') }}" class="portfolio-nav hidden gap-8 lg:flex">
            @foreach (__('portfolio.nav') as $id => $label)
                <a href="#{{ $id }}" class="text-sm text-muted transition hover:text-ivory">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-2 sm:gap-4">
            {{-- @if ($site['status'] ?? null)
                <span class="hidden items-center gap-2 font-mono text-xs text-muted lg:flex"><span class="h-1.5 w-1.5 shrink-0 rounded-full bg-sage"></span>{{ $site['status'] }}</span>
            @endif --}}
            @include('portfolio.languages')
            @include('portfolio.theme-toggle')
            <details class="relative lg:hidden">
                <summary class="cursor-pointer list-none rounded-full border border-emerald px-3 py-2.5 text-sm sm:px-4">{{ __('portfolio.menu') }}</summary>
                <div class="absolute right-0 mt-3 flex w-56 flex-col rounded-xl border border-emerald bg-forest p-2">
                    @foreach (__('portfolio.nav') as $id => $label)
                        <a href="#{{ $id }}" class="rounded-lg px-4 py-3.5 hover:bg-emerald/50">{{ $label }}</a>
                    @endforeach
                </div>
            </details>
        </div>
    </div>
</header>

