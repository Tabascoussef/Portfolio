<nav class="language-switcher" aria-label="{{ __('portfolio.language') }}">
    @foreach (\App\Enums\PortfolioLocale::cases() as $language)
        <a href="{{ $language->publicUrl($pageSlug ?? null) }}" hreflang="{{ $language->value }}" lang="{{ $language->value }}"
            aria-label="{{ $language->label() }}" @if ($language === $locale) aria-current="true" @endif>{{ strtoupper($language->value) }}</a>
    @endforeach
</nav>

