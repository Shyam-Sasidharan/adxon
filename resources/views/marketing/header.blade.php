<a class="skip-link" href="#main">Skip to content</a>
<header class="marketing-header">
    <a href="{{ route('home') }}#top" aria-label="Adxon home"><img class="brand-logo" src="{{ asset('assets/brand/adxon-full-dark.png') }}" alt="Adxon"></a>
    <button class="icon-button mobile-nav" aria-label="Open navigation" aria-controls="site-nav" aria-expanded="false" data-nav-toggle><i data-lucide="menu"></i></button>
    <nav id="site-nav" aria-label="Main navigation">
        @foreach (['top' => 'Home', 'about' => 'About', 'services' => 'Services', 'solutions' => 'Solutions', 'work' => 'Case Studies', 'pricing' => 'Pricing', 'blog' => 'Resources', 'contact' => 'Contact'] as $id => $label)
            <a href="{{ route('home') }}#{{ $id }}">{{ $label }}</a>
        @endforeach
    </nav>
    <a class="button primary header-cta" href="{{ route('home') }}#contact">Let's talk <i data-lucide="arrow-up-right"></i></a>
</header>
