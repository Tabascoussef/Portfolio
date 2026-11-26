@extends('portfolio-admin.layout')

@section('title', __('admin.profile'))

@section('content')
    <div class="page-top"><h1>{{ __('admin.profile') }}</h1></div>
    <form class="panel admin-form about-photo-settings" method="POST" enctype="multipart/form-data"
        action="{{ route('portfolio-admin.profile.photo.update') }}">
        @csrf
        @method('PUT')
        <h2>{{ __('portfolio.profile_photo') }}</h2>
        <div class="about-photo-fields">
            <img class="about-photo-preview" src="{{ route('portfolio-admin.profile.photo.preview') }}" alt="{{ __('portfolio.profile_photo') }}">
            <div class="admin-form">
                <div class="field">
                    <label for="about-photo">{{ __('admin.about_photo_file') }}</label>
                    <input id="about-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                        aria-describedby="about-photo-help @error('photo') about-photo-error @enderror" @error('photo') aria-invalid="true" @enderror>
                    <small id="about-photo-help">{{ __('admin.about_photo_help') }}</small>
                    @error('photo')<small id="about-photo-error" class="error">{{ $message }}</small>@enderror
                </div>
                <div class="visibility-field">
                    <span>{{ __('admin.visibility') }}</span>
                    @include('portfolio-admin.visibility-switch', ['visibilityName' => 'visible', 'active' => (bool) old('visible', $profile->aboutPhotoVisible()), 'label' => __('admin.visibility'), 'submitOnChange' => false])
                    @error('visible')<small class="error">{{ $message }}</small>@enderror
                </div>
                <div><button class="button primary" type="submit">{{ __('admin.save') }}</button></div>
            </div>
        </div>
    </form>
    <form class="admin-form" method="POST" action="{{ route('portfolio-admin.profile.update') }}">
        @csrf
        @method('PUT')
        <section class="panel">
            <h2>{{ __('admin.shared') }}</h2>
            <div class="form-grid">
                @include('portfolio-admin.fields', ['prefix' => 'details', 'fields' => $fields['details'], 'values' => $profile->details])
            </div>
        </section>
        @include('portfolio-admin.translations', ['translatedFields' => $fields['translations'], 'translations' => $profile->translations])
        <div><button class="button primary" type="submit">{{ __('admin.save') }}</button></div>
    </form>
@endsection

