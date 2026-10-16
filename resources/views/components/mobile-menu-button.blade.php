@props(['title', 'logo' => null, 'controls'])

<button class="mobile-menu-btn branded-menu-toggle" id="mobileNavigationToggle" type="button"
    data-mobile-nav-toggle aria-label="Open navigation for {{ $title }}"
    aria-controls="{{ $controls }}" aria-expanded="false" data-menu-title="{{ $title }}">
    <span class="brand-logo-shell" aria-hidden="true">
        @if($logo)
            <img class="brand-logo" src="{{ $logo }}" alt="">
        @else
            <i class="fas fa-church"></i>
        @endif
    </span>
    <span class="menu-brand-title" title="{{ $title }}">{{ $title }}</span>
    <i class="fas fa-bars menu-toggle-icon" aria-hidden="true"></i>
</button>
