@php
    $project = $entry->localized();
    $images = $entry->galleryImages();
    $pageTitle = $project['name'].' — '.($site['name'] ?? 'Portfolio');
    $pageDescription = $project['about'] ?? $project['solution'] ?? null;
    $pageSlug = $entry->slug;
    $metadata = array_filter([$project['category'] ?? null, $project['year'] ?? null]);
@endphp

@extends('portfolio.layout')

@section('content')
    <main class="portfolio-container pb-24 pt-28">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ $locale->publicUrl() }}#work" class="text-sm text-muted hover:text-sage">{{ __('portfolio.back_to_work') }}</a>
            <div class="flex items-center gap-2 sm:gap-4">
                @include('portfolio.languages')
                @include('portfolio.theme-toggle')
            </div>
        </div>
        <h1 class="display h-lg mb-4 mt-8 font-bold">{{ $project['name'] }}</h1>
        @if ($metadata !== [])
            <p class="font-mono text-sm text-muted">{{ implode(' · ', $metadata) }}</p>
        @endif
        @if (!empty($project['about']))
            <p class="mt-6 max-w-2xl text-[1.0625rem] leading-relaxed whitespace-pre-line text-muted">{{ $project['about'] }}</p>
        @endif
        @if ($images !== [])
            <div class="mt-8 max-w-4xl">
                @include('portfolio.project-gallery', ['images' => $images, 'name' => $project['name'], 'detailed' => true])
            </div>
        @endif

        <div class="mt-12">
            @foreach (['stack', 'problem', 'solution', 'role', 'architecture', 'challenges', 'outcomes', 'links'] as $block)
                @continue($block === 'links' ? empty($project['github']) && empty($project['live']) : empty($project[$block]))
                <section class="grid gap-4 border-t border-emerald/40 py-8 md:grid-cols-12">
                    <h2 class="display text-[1.375rem] font-semibold md:col-span-3">{{ __('portfolio.project.'.$block) }}</h2>
                    <div class="text-muted md:col-span-8">
                        @if ($block === 'links')
                            <div class="flex gap-6">
                                @if (!empty($project['github']))
                                    <a class="hover:text-ivory" href="{{ $project['github'] }}">GitHub</a>
                                @endif
                                @if (!empty($project['live']))
                                    <a class="hover:text-ivory" href="{{ $project['live'] }}">{{ __('portfolio.live') }}</a>
                                @endif
                            </div>
                        @elseif ($block === 'stack')
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($project['stack'] as $technology)
                                    <li class="rounded-full bg-emerald px-4 py-2 text-sm text-ivory">{{ $technology }}</li>
                                @endforeach
                            </ul>
                        @elseif (is_array($project[$block]))
                            <ul class="list-disc space-y-2 pl-5">
                                @foreach ($project[$block] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{ $project[$block] }}
                        @endif
                    </div>
                </section>
            @endforeach
        </div>

        @if ($entry->isNot($next))
            <a href="{{ $locale->publicUrl($next->slug) }}" class="display mt-12 block text-[1.75rem] font-semibold hover:text-sage md:text-[2.75rem]">{{ __('portfolio.next', ['name' => $next->localized()['name']]) }}</a>
        @endif
    </main>
@endsection

