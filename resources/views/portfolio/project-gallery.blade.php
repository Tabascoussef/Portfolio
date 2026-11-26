@php($hasCarousel = count($images) > 1)

<div @class([
    'relative overflow-hidden bg-obsidian',
    'aspect-video rounded-xl border border-emerald/60' => $detailed,
    'aspect-[16/10] rounded-t-xl' => !$detailed,
    'project-gallery-fallback' => $detailed && $hasCarousel,
])
    @if ($hasCarousel) data-gallery-track @endif>
    @forelse ($images as $image)
        <div @class(['project-slide absolute inset-0', 'motion-safe:transition-opacity motion-safe:duration-700' => $hasCarousel])
            @if ($hasCarousel) data-gallery-slide @endif
            @if ($loop->first) data-active @elseif (!$detailed) inert aria-hidden="true" @endif>
            @if ($detailed)
                <a href="{{ $image }}" data-gallery-expand class="block size-full cursor-zoom-in">
            @endif
            <img src="{{ $image }}" alt="{{ $detailed ? __('portfolio.project_image', ['name' => $name, 'number' => $loop->iteration]) : '' }}"
                @class(['size-full', 'object-contain' => $detailed, 'object-cover' => !$detailed])
                @if (!$detailed || !$loop->first) loading="lazy" @endif decoding="async">
            @if ($detailed)
                </a>
            @endif
        </div>
    @empty
        <div class="absolute inset-0 bg-linear-to-br from-emerald to-forest" aria-hidden="true"><div class="grid-lines absolute inset-0"></div></div>
    @endforelse

    @if ($hasCarousel)
        <div data-gallery-controls hidden class="absolute right-2 bottom-2 z-20 flex gap-1.5">
            @if ($detailed)
                <button type="button" data-gallery-step="-1" class="gallery-button" aria-label="{{ __('portfolio.gallery_previous') }}"><span aria-hidden="true">‹</span></button>
                <button type="button" data-gallery-step="1" class="gallery-button" aria-label="{{ __('portfolio.gallery_next') }}"><span aria-hidden="true">›</span></button>
            @endif
            <button type="button" data-gallery-pause class="gallery-button group/pause motion-reduce:hidden" aria-label="{{ __('portfolio.gallery_pause') }}" aria-pressed="false">
                <span class="group-aria-pressed/pause:hidden" aria-hidden="true">Ⅱ</span>
                <span class="hidden group-aria-pressed/pause:inline" aria-hidden="true">▶</span>
            </button>
        </div>
    @endif

    @if ($detailed && $images !== [])
        <dialog data-gallery-preview aria-label="{{ __('portfolio.project.gallery') }}"
            class="fixed inset-0 m-0 h-dvh max-h-none w-full max-w-none bg-transparent p-4 text-ivory backdrop:bg-obsidian/95">
            <div class="flex h-full items-center justify-center pt-12">
                <div class="relative aspect-video w-full max-w-[calc((100dvh-5rem)*16/9)] overflow-hidden rounded-lg bg-forest">
                    <img alt="" class="absolute inset-0 size-full object-contain">
                </div>
            </div>
            <form method="dialog" class="absolute right-4 top-4">
                <button type="submit" autofocus aria-label="{{ __('portfolio.gallery_close') }}"
                    class="gallery-button size-11 border border-sage/40 text-2xl"><span aria-hidden="true">×</span></button>
            </form>
        </dialog>
    @endif
</div>
