@extends('portfolio.layout')

@section('content')
    @include('portfolio.nav')
    <main>
        <section id="home" class="relative flex min-h-[100svh] flex-col justify-end overflow-hidden pb-16 pt-32">
            @if ($site['about_photo'] ?? null)
                <img src="{{ $site['about_photo'] }}" alt="{{ __('portfolio.profile_photo') }}" decoding="async"
                    class="hero-photo pointer-events-none absolute object-cover">
            @endif
            <canvas data-grid-field aria-hidden="true" class="absolute inset-0 h-full w-full"></canvas>
            <div class="grid-lines pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="portfolio-container relative">
                <h1 class="display h-xl font-bold">
                    <span class="block overflow-hidden pb-[0.08em]"><span class="block" data-hero-line>{{ $site['hero_first'] ?? '' }}</span></span>
                    <span class="block overflow-hidden pb-[0.08em]"><span class="block" data-hero-line style="--delay: 0.22s">{{ $site['hero_second'] ?? '' }}</span></span>
                </h1>
                <div class="mt-12 grid gap-8 md:grid-cols-12">
                    <p class="max-w-md text-[1.0625rem] text-muted md:col-span-5 md:col-start-8">{{ $site['hero_intro'] ?? '' }}</p>
                    <p class="font-mono text-sm text-muted md:col-span-3 md:col-start-8">{{ __('portfolio.based_in', ['location' => $site['location'] ?? '']) }}</p>
                    <div class="flex flex-wrap gap-4 md:col-span-5 md:col-start-8">
                        <a href="#work" class="rounded-full bg-sage px-6 py-3.5 font-medium text-obsidian transition hover:bg-ivory">{{ __('portfolio.explore') }}</a>
                        @if ($site['resume'] ?? null)
                            <a href="{{ $site['resume'] }}" download class="rounded-full border border-sage/50 px-6 py-3.5 font-medium text-sage transition hover:border-sage hover:bg-forest">{{ __('portfolio.download_resume') }}</a>
                        @endif
                        <a href="https://web.telegram.org/k/#@azimov_Yusuf" class="rounded-full border border-emerald px-6 py-3.5 transition hover:border-sage">{{ __('portfolio.connect') }}</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="py-28 md:py-40">
            <div class="portfolio-container grid gap-12 md:grid-cols-12">
                <h2 class="display h-lg font-bold md:col-span-6">{{ $site['about_title'] ?? '' }}</h2>
                <div data-reveal class="space-y-6 text-[1.0625rem] text-muted md:col-span-5 md:col-start-8">
                    <p>{{ $site['about_first'] ?? '' }}</p>
                    <p>{{ $site['about_second'] ?? '' }}</p>
                </div>
            </div>
        </section>

        <section id="work" class="border-t border-emerald/40 py-28 md:py-40">
            <div class="portfolio-container">
                <h2 class="display h-lg mb-16 font-bold">{{ __('portfolio.selected_work') }}</h2>
                <ul class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                    @foreach ($entries->get('projects', []) as $entry)
                        <li class="min-w-0">
                            @include('portfolio.project-card', ['project' => $entry->localized(), 'images' => $entry->galleryImages(), 'url' => $locale->publicUrl($entry->slug)])
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section id="expertise" class="border-t border-emerald/40 py-28 md:py-40">
            <div class="portfolio-container">
                <h2 class="display h-lg mb-6 font-bold">{{ __('portfolio.nav.expertise') }}</h2>
                <p class="mb-16 max-w-xl text-muted">{{ __('portfolio.expertise_intro') }}</p>
                <div class="grid gap-12 md:grid-cols-2">
                    @foreach ($entries->get('skills', []) as $entry)
                        @php($skill = $entry->localized())
                        <div id="skill-{{ $entry->getKey() }}" class="scroll-mt-24 border-t border-emerald/40 pt-6">
                            <h3 class="display mb-5 text-[1.375rem] font-semibold">{{ $skill['name'] }}</h3>
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($skill['established'] ?? [] as $item)
                                    <li class="rounded-full bg-emerald px-4 py-2 text-sm">{{ $item }}</li>
                                @endforeach
                                @foreach ($skill['learning'] ?? [] as $item)
                                    <li class="rounded-full border border-dashed border-sage px-4 py-2 text-sm text-sage">{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="border-t border-emerald/40 bg-forest py-28 md:py-40">
            <div data-reveal class="portfolio-container">
                <p class="font-mono text-sm text-muted">{{ $site['award_event'] ?? '' }}</p>
                <p class="display h-lg mt-6 font-bold">{{ $site['award_title'] ?? '' }}</p>
                <p class="mt-6 max-w-lg text-[1.0625rem] text-muted">{{ $site['award_text'] ?? '' }}</p>
            </div>
        </section>

        <section id="journey" class="border-t border-emerald/40 py-28 md:py-40">
            <div class="portfolio-container">
                <h2 class="display h-lg mb-16 font-bold">{{ __('portfolio.nav.journey') }}</h2>
                <ol class="border-l border-emerald">
                    @foreach ($entries->get('journey', []) as $entry)
                        @php($milestone = $entry->localized())
                        <li class="relative pb-12 pl-8 last:pb-0">
                            <span class="absolute -left-[5px] top-2 h-2.5 w-2.5 rounded-full bg-sage"></span>
                            <span class="font-mono text-sm text-muted">{{ $milestone['when'] ?? '' }}</span>
                            <h3 class="display text-[1.375rem] font-semibold md:text-[1.75rem]">{{ $milestone['name'] }}</h3>
                            <p class="mt-2 max-w-lg text-muted">{{ $milestone['text'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="border-t border-emerald/40 py-28 md:py-40">
            <div class="portfolio-container">
                <h2 class="sr-only">{{ __('portfolio.philosophy') }}</h2>
                @foreach ($entries->get('principles', []) as $entry)
                    <div data-reveal><p class="display h-lg py-3 font-bold text-ivory/90">{{ $entry->localized()['name'] }}</p></div>
                @endforeach
            </div>
        </section>

        <section id="contact" class="border-t border-emerald/40 py-28 md:py-40">
            <div class="portfolio-container">
                <h2 class="display h-xl mb-14 font-bold">{{ $site['contact_first'] ?? '' }}<br>{{ $site['contact_second'] ?? '' }}</h2>
                @if ($site['email'] ?? null)
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="mailto:{{ $site['email'] }}" class="rounded-full bg-sage px-7 py-4 font-medium text-obsidian transition hover:bg-ivory">{{ $site['email'] }}</a>
                        <button type="button" hidden data-copy-email="{{ $site['email'] }}" data-copied="{{ __('portfolio.copied') }}" data-failed="{{ __('portfolio.copy_failed') }}"
                            class="rounded-full border border-emerald px-6 py-4 transition hover:border-sage" aria-live="polite">{{ __('portfolio.copy_email') }}</button>
                    </div>
                @elseif ($site['github'] ?? null)
                    <a href="https://web.telegram.org/k/#@azimov_Yusuf" target="_blank" class="inline-block rounded-full bg-sage px-7 py-4 font-medium text-obsidian">{{ __('portfolio.github_hello') }}</a>
                @endif
                <div class="mt-10 flex flex-wrap gap-6 text-muted">
                    @if ($site['github'] ?? null)
                        <a class="underline-offset-4 hover:text-ivory hover:underline" href="https://github.com/Tabascoussef">GitHub</a>
                    @endif
                    @if ($site['linkedin'] ?? null)
                        <a class="underline-offset-4 hover:text-ivory hover:underline" href="{{ $site['linkedin'] }}">LinkedIn</a>
                    @endif
                    @if ($site['resume'] ?? null)
                        <a class="underline-offset-4 hover:text-ivory hover:underline" href="{{ $site['resume'] }}" download>{{ __('portfolio.download_resume') }}</a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-emerald/40 py-8">
        <div class="portfolio-container flex flex-wrap items-center justify-between gap-4">
            <span class="display text-xl font-bold">{{ $site['short'] ?? 'YUSUF.' }}</span>
            <span class="font-mono text-sm text-muted" data-local-time="{{ __('portfolio.local_time', ['time' => '__time__', 'location' => $site['location'] ?? '']) }}"
                data-zone="{{ $site['time_zone'] ?? 'Asia/Tashkent' }}">&nbsp;</span>
            <span class="text-sm text-muted">© {{ now($site['time_zone'] ?? 'Asia/Tashkent')->year }} {{ $site['name'] ?? '' }}</span>
            <a href="#home" class="text-sm hover:text-sage">{{ __('portfolio.back_to_top') }}</a>
        </div>
    </footer>
@endsection

