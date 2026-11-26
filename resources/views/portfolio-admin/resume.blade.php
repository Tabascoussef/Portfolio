@extends('portfolio-admin.layout')

@section('title', __('portfolio.resume'))

@section('content')
    <div class="page-top"><h1>{{ __('portfolio.resume') }}</h1></div>
    <div class="admin-form">
        <form class="panel admin-form" action="{{ route('portfolio-admin.resume.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="resume">{{ __('admin.resume_file') }}</label>
                <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx" required
                    aria-describedby="resume-help @error('resume') resume-error @enderror" @error('resume') aria-invalid="true" @enderror>
                <small id="resume-help">{{ __('admin.resume_upload_help') }}</small>
                @error('resume')<small id="resume-error" class="error">{{ $message }}</small>@enderror
            </div>
            <div><button type="submit" class="button primary">{{ __('admin.save') }}</button></div>
        </form>

        @if ($resumeUrl)
            <div class="panel actions">
                <a href="{{ $resumeUrl }}" class="button secondary" download>{{ __('admin.download') }}</a>
                <form action="{{ route('portfolio-admin.resume.destroy') }}" method="POST"
                    data-confirm="{{ __('admin.confirm_delete_resume') }}" onsubmit="return window.confirm(this.dataset.confirm)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button danger">{{ __('admin.delete') }}</button>
                </form>
            </div>
        @endif
    </div>
@endsection
