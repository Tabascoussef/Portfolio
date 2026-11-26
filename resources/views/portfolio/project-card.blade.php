<article data-project-card class="group relative flex h-full flex-col rounded-xl border border-emerald/80 bg-forest transition-colors hover:border-sage/60 focus-within:border-sage/60">
    @include('portfolio.project-gallery', ['images' => $images, 'name' => $project['name'], 'detailed' => false])

    <div class="flex flex-1 flex-col gap-3 p-4">
        <h3 class="text-base/5 font-semibold wrap-break-word">
            <a href="{{ $url }}" class="after:absolute after:inset-0 after:z-10 after:rounded-xl group-hover:text-sage focus-visible:text-sage">{{ $project['name'] }}</a>
        </h3>
        @if ($about = $project['about'] ?? $project['solution'] ?? $project['category'] ?? null)
            <p class="line-clamp-3 text-sm/5 wrap-break-word text-muted">{{ $about }}</p>
        @endif

        @if (!empty($project['stack']))
            <ul class="mt-auto flex flex-wrap gap-1.5 pt-1">
                @foreach ($project['stack'] as $technology)
                    <li class="max-w-full rounded-md bg-emerald px-2 py-1 font-mono text-xs/4 wrap-break-word text-ivory">{{ $technology }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    @if (!empty($project['github']) || !empty($project['live']))
        <div class="flex flex-wrap gap-x-4 gap-y-2 border-t border-emerald/70 px-4 py-3 text-xs text-sage">
            @if (!empty($project['github']))
                <a class="relative z-20 underline-offset-4 hover:underline" href="{{ $project['github'] }}">GitHub</a>
            @endif
            @if (!empty($project['live']))
                <a class="relative z-20 underline-offset-4 hover:underline" href="{{ $project['live'] }}">{{ __('portfolio.live') }}</a>
            @endif
        </div>
    @endif
</article>
