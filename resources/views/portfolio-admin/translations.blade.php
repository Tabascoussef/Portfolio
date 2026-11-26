<nav class="actions lang-jump" aria-label="{{ __('admin.language') }}">
    @foreach (\App\Enums\PortfolioLocale::cases() as $language)
        <a href="#translations-{{ $language->value }}">{{ $language->label() }}</a>
    @endforeach
</nav>
@foreach (\App\Enums\PortfolioLocale::cases() as $language)
    <section class="panel" id="translations-{{ $language->value }}">
        <h2 lang="{{ $language->value }}">{{ $language->label() }}</h2>
        <div class="form-grid">
            @include('portfolio-admin.fields', [
                'prefix' => 'translations.'.$language->value,
                'fields' => $translatedFields,
                'values' => $translations[$language->value] ?? [],
            ])
        </div>
    </section>
@endforeach

