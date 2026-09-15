<footer class="marketing-footer">
    <div class="shell footer-grid">
        <div>
            <img class="brand-logo" src="{{ asset('assets/brand/adxon-full-light.png') }}" alt="Adxon">
            <p>{{ $settings['tagline'] }}</p>
            <div class="social-links">
                @foreach (['instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'facebook' => 'Facebook'] as $key => $label)
                    @if ($url = \App\Support\AdxonContent::imageUrl($settings[$key.'_url'] ?? null))<a href="{{ $url }}" aria-label="{{ $label }}" rel="noopener noreferrer" target="_blank"><i data-lucide="{{ $key }}"></i></a>@endif
                @endforeach
            </div>
        </div>
        <nav aria-label="Company"><strong>Company</strong><a href="{{ route('home') }}#about">About Adxon</a><a href="{{ route('home') }}#work">Our work</a><a href="{{ route('home') }}#blog">Resources</a><a href="{{ route('home') }}#pricing">Packages</a></nav>
        <nav aria-label="Expertise"><strong>Expertise</strong>@foreach ($content['services'] as $service)<a href="{{ route('home') }}#services">{{ $service['title'] }}</a>@endforeach</nav>
        <nav aria-label="Contact"><strong>Say hello</strong><a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a><a href="tel:{{ preg_replace('/[^+0-9]/', '', $settings['phone']) }}">{{ $settings['phone'] }}</a><span>{{ $settings['location'] }}</span><a href="{{ route('admin.dashboard') }}">Workspace <i data-lucide="arrow-up-right"></i></a></nav>
    </div>
    <div class="shell footer-bottom"><span>&copy; {{ date('Y') }} {{ $settings['brand'] }}. All rights reserved.</span><div>
        @foreach (['privacy_url' => 'Privacy Policy', 'terms_url' => 'Terms & Conditions'] as $key => $label)
            @if ($url = \App\Support\AdxonContent::imageUrl($settings[$key] ?? null))<a href="{{ $url }}">{{ $label }}</a>@endif
        @endforeach
    </div><span>Creative minds. Measurable growth.</span></div>
</footer>
