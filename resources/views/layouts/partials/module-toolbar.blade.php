@include('layouts.partials.module-toolbar-styles')

@php
    $moduleToolbarUser = auth()->user();
    $moduleToolbarName = (string) ($moduleToolbarUser?->name ?? 'User');
    $moduleToolbarEmail = (string) ($moduleToolbarUser?->email ?? '-');
    $moduleToolbarInitial = strtoupper(substr($moduleToolbarName, 0, 1));

    $moduleToolbarEnglishButtonId = (string) ($englishButtonId ?? 'moduleLanguageEnglish');
    $moduleToolbarIndonesianButtonId = (string) ($indonesianButtonId ?? 'moduleLanguageIndonesian');
    $moduleToolbarAriaLabel = (string) ($ariaLabel ?? 'Language Toggle');
    $moduleToolbarExtraToggleClass = trim((string) ($languageToggleClass ?? ''));
    $moduleToolbarToggleClasses = 'module-language-toggle';

    if ($moduleToolbarExtraToggleClass !== '') {
        $moduleToolbarToggleClasses .= ' ' . $moduleToolbarExtraToggleClass;
    }
@endphp

<div class="module-toolbar">
    <div class="module-toolbar-user">
        <span class="module-toolbar-avatar">{{ $moduleToolbarInitial !== '' ? $moduleToolbarInitial : 'U' }}</span>
        <div class="module-toolbar-user-meta">
            <div class="module-toolbar-user-name">{{ $moduleToolbarName }}</div>
            <div class="module-toolbar-user-email">{{ $moduleToolbarEmail }}</div>
        </div>
    </div>
    <div class="module-toolbar-controls">
        <div class="btn-group btn-group-xs {{ $moduleToolbarToggleClasses }}" role="group" aria-label="{{ $moduleToolbarAriaLabel }}">
            <button type="button" class="btn btn-default" id="{{ $moduleToolbarEnglishButtonId }}" data-lang="en">EN</button>
            <button type="button" class="btn btn-default" id="{{ $moduleToolbarIndonesianButtonId }}" data-lang="id">ID</button>
        </div>
    </div>
</div>
