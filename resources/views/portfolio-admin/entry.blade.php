@php
    $scope = ['section' => $section->value];
    $heading = __($entry->exists ? 'admin.edit' : 'admin.add');
    $action = $entry->exists
        ? route('portfolio-admin.entries.update', $scope + ['entry' => $entry])
        : route('portfolio-admin.entries.store', $scope);
    $isProject = $section === \App\Enums\PortfolioSection::Projects;
    $gallery = $isProject ? $entry->galleryItems() : [];
    $entryFields = ['position' => 'position'];
    if ($isProject) {
        $entryFields['slug'] = 'slug';
    }
@endphp

@extends('portfolio-admin.layout')

@section('title', $heading)

@section('content')
    <div class="page-top">
        <h1>{{ __('admin.sections.'.$section->value) }} · {{ $heading }}</h1>
        <a class="button secondary" href="{{ route('portfolio-admin.entries.index', $scope) }}">{{ __('admin.cancel') }}</a>
    </div>
    @if ($gallery !== [])
        <section class="panel saved-gallery" aria-labelledby="saved-gallery-heading">
            <h2 id="saved-gallery-heading">{{ __('admin.uploaded_images') }}</h2>
            <ul class="uploaded-images-list">
                @foreach ($gallery as $image)
                    <li class="uploaded-image">
                        <img src="{{ $image['url'] }}" alt="" loading="lazy">
                        <form method="POST" action="{{ route('portfolio-admin.projects.images.destroy', ['entry' => $entry]) }}"
                            data-confirm="{{ __('admin.confirm_delete_image') }}" onsubmit="return window.confirm(this.dataset.confirm)">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="image" value="{{ $image['reference'] }}">
                            <button class="button danger" type="submit" aria-label="{{ __('admin.remove_image_number', ['number' => $loop->iteration]) }}">{{ __('admin.delete') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            @error('image')<p class="error" role="alert">{{ $message }}</p>@enderror
        </section>
    @endif
    <form class="admin-form" method="POST" action="{{ $action }}" @if ($isProject) enctype="multipart/form-data" @endif>
        @csrf
        @if ($entry->exists) @method('PUT') @endif
        <section class="panel">
            <h2>{{ __('admin.shared') }}</h2>
            <div class="form-grid">
                <div class="visibility-field wide">
                    <span>{{ __('admin.visibility') }}</span>
                    @include('portfolio-admin.visibility-switch', ['active' => (bool) old('is_active', $entry->is_active), 'label' => __('admin.visibility'), 'submitOnChange' => false])
                    @error('is_active')<small class="error">{{ $message }}</small>@enderror
                </div>
                @include('portfolio-admin.fields', ['prefix' => null, 'fields' => $entryFields, 'values' => ['position' => $entry->position, 'slug' => $entry->slug]])
                @include('portfolio-admin.fields', ['prefix' => 'details', 'fields' => \Illuminate\Support\Arr::except($section->sharedFields(), ['images']), 'values' => $entry->details])
                @if ($isProject)
                    <div class="field wide">
                        <label for="image-uploads">{{ __('admin.field.image_uploads') }}</label>
                        <input id="image-uploads" type="file" name="image_uploads[]" multiple accept="image/jpeg,image/png,image/webp,image/gif"
                            aria-describedby="image-uploads-help @if ($errors->has('image_uploads') || $errors->has('image_uploads.*')) image-uploads-error @endif"
                            @if ($errors->has('image_uploads') || $errors->has('image_uploads.*')) aria-invalid="true" @endif>
                        <small id="image-uploads-help">{{ __('admin.image_upload_help') }}</small>
                        @if ($error = $errors->first('image_uploads') ?: $errors->first('image_uploads.*'))
                            <small id="image-uploads-error" class="error">{{ $error }}</small>
                        @endif
                    </div>
                @endif
            </div>
        </section>
        @include('portfolio-admin.translations', ['translatedFields' => $section->translatedFields(), 'translations' => $entry->translations])
        <div><button class="button primary" type="submit">{{ __('admin.save') }}</button></div>
    </form>
@endsection

