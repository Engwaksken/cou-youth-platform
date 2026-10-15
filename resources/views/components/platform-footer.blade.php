@props(['brand' => []])

<footer class="platform-footer">
    <div class="platform-footer-grid">
        <div class="platform-footer-column">
            <a class="platform-footer-brand" href="{{ route('home') }}">
                @if(!empty($brand['logo_url']))
                    <img src="{{ $brand['logo_url'] }}" alt="" width="48" height="48" loading="lazy">
                @endif
                <strong>{{ $brand['name'] ?? $brand['short_name'] ?? 'Church of Uganda Youth Platform' }}</strong>
            </a>
            <p>{{ $brand['tagline'] ?? 'Connecting Young People. Growing Disciples. Transforming Nations.' }}</p>
        </div>
        <nav class="platform-footer-column" aria-label="Footer explore links">
            <h2>Explore</h2>
            <ul>
                <li><a href="{{ route('public.about') }}">About Us</a></li>
                <li><a href="{{ route('public.news') }}">News &amp; Resources</a></li>
                <li><a href="{{ route('public.events') }}">Events</a></li>
                <li><a href="{{ route('public.churches') }}">Church Locator</a></li>
            </ul>
        </nav>
        <nav class="platform-footer-column" aria-label="Footer community links">
            <h2>Grow &amp; Connect</h2>
            <ul>
                <li><a href="{{ route('public.courses') }}">Courses</a></li>
                <li><a href="{{ route('public.life-groups') }}">Life Groups</a></li>
                <li><a href="{{ route('public.prayer') }}">Prayer &amp; Pastoral Support</a></li>
                <li><a href="{{ route('public.donate') }}">Support Youth Ministry</a></li>
            </ul>
        </nav>
        <nav class="platform-footer-column" aria-label="Footer account and support links">
            <h2>Account &amp; Support</h2>
            <ul>
                @auth
                    <li><a href="{{ route('youth.dashboard') }}">Youth Portal</a></li>
                    <li><a href="{{ route('youth.profile') }}">My Profile</a></li>
                @else
                    <li><a href="{{ route('login') }}">Youth Login</a></li>
                    <li><a href="{{ route('register') }}">Create an Account</a></li>
                @endauth
                <li><a href="{{ route('password.request') }}">Reset Password</a></li>
                @if(!empty($brand['support_email']))
                    <li><a href="mailto:{{ $brand['support_email'] }}">{{ $brand['support_email'] }}</a></li>
                @endif
            </ul>
        </nav>
    </div>
    <div class="platform-footer-bottom">
        <div class="platform-footer-bottom-inner">
            <p>&copy; {{ date('Y') }} Church of Uganda. All rights reserved.</p>
            <p>{{ $brand['short_name'] ?? 'COU Youth Platform' }} &middot; Connect. Grow. Serve.</p>
        </div>
    </div>
</footer>
