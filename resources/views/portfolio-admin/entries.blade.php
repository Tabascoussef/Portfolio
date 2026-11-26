@extends('portfolio-admin.layout')

@php
    $locale = \App\Enums\PortfolioLocale::from(app()->getLocale());
@endphp

@section('title', __('admin.sections.'.$section->value))

@section('content')
    <div class="page-top">
        <h1>{{ __('admin.sections.'.$section->value) }}</h1>
        <a href="{{ route('portfolio-admin.entries.create', ['section' => $section->value]) }}" class="button primary">{{ __('admin.add') }}</a>
    </div>
    @if ($entries->isEmpty())
        <p class="panel muted">{{ __('admin.empty') }}</p>
    @else
        <div class="panel entry-list">
            <table>
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.order') }}</th>
                        <th scope="col">{{ __('admin.item') }}</th>
                        <th scope="col">{{ __('admin.visibility') }}</th>
                        <th scope="col">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        @php
                            $parameters = ['section' => $section->value, 'entry' => $entry];
                            $name = $entry->localized()['name'];
                        @endphp
                        <tr>
                            <td>{{ $entry->position }}</td>
                            <td>{{ $name }}</td>
                            <td>
                                <form class="visibility-form" method="POST" action="{{ route('portfolio-admin.entries.visibility.update', $parameters) }}">
                                    @csrf
                                    @method('PATCH')
                                    @if ($entries->currentPage() > 1)
                                        <input type="hidden" name="page" value="{{ $entries->currentPage() }}">
                                    @endif
                                    @include('portfolio-admin.visibility-switch', ['active' => $entry->is_active, 'label' => __('admin.visibility_for', ['name' => $name]), 'submitOnChange' => true])
                                    <noscript><button class="button secondary" type="submit">{{ __('admin.save') }}</button></noscript>
                                </form>
                            </td>
                            <td>
                                <div class="actions">
                                    @if ($entry->is_active && $section === \App\Enums\PortfolioSection::Projects)
                                        <a class="button secondary" href="{{ $locale->publicUrl($entry->slug) }}">{{ __('admin.view') }}</a>
                                    @elseif ($entry->is_active && $section === \App\Enums\PortfolioSection::Skills)
                                        <a class="button secondary" href="{{ $locale->publicUrl() }}#skill-{{ $entry->getKey() }}">{{ __('admin.view') }}</a>
                                    @endif
                                    <a class="button secondary" href="{{ route('portfolio-admin.entries.edit', $parameters) }}">{{ __('admin.edit') }}</a>
                                    <form method="POST" action="{{ route('portfolio-admin.entries.destroy', $parameters) }}"
                                        data-confirm="{{ __('admin.confirm_delete') }}" onsubmit="return window.confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger" type="submit">{{ __('admin.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($entries->hasPages())
        <nav class="pagination" aria-label="{{ __('admin.sections.'.$section->value) }}">
            @if ($entries->previousPageUrl())<a class="button secondary" href="{{ $entries->previousPageUrl() }}">{{ __('admin.previous') }}</a>@endif
            @if ($entries->nextPageUrl())<a class="button secondary" href="{{ $entries->nextPageUrl() }}">{{ __('admin.next') }}</a>@endif
        </nav>
    @endif
@endsection

