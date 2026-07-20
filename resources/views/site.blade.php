@php
    $settings = $content['settings'];
    $seo = $content['seo'];
    $hero = $content['hero'];
    $serviceHighlights = array_slice($content['services'], 0, 4);
    $packageGroups = $content['package_groups'] ?? ['content' => $content['packages']];
    $packageTabs = [
        'content' => 'Content Production',
        'social' => 'Social Media Management',
        'ads' => 'Paid Ads Management',
        'combo' => 'Popular Combinations',
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
</head>
<body class="site-body">
    <header class="site-header" data-nav>
        <a class="brand" href="#top" aria-label="Adxon home">
            <span class="brand-mark">A</span>
            <span><strong>{{ $settings['brand'] }}</strong><small>Digital Agency</small></span>
        </a>
        <button class="menu-button" type="button" data-menu aria-label="Toggle navigation">
            <span></span><span></span>
        </button>
        <nav class="nav-links" data-links>
            <a href="#top">Home</a>
            <a href="#about">About Us</a>
            <a href="#services">Services</a>
            <a href="#pricing">Packages</a>
            <a href="#work">Portfolio</a>
            <a href="#blog">Blog</a>
            <a href="#contact">Contact Us</a>
        </nav>
        <a class="quote-button" href="{{ route('admin.dashboard') }}">CMS</a>
    </header>

    <main id="top">
        <section class="hero">
            <div class="hero-inner section-shell">
                <div class="hero-copy reveal">
                    <p class="eyebrow">Content that connects. <span>Strategy that grows.</span></p>
                    <h1>We Create. We Manage. We Grow <em>Your Brand.</em></h1>
                    <p class="hero-subline">{{ $hero['subline'] }}</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#pricing">Explore Packages <span>-></span></a>
                        <a class="button button-ghost" href="#work">Our Work <span class="play-dot"></span></a>
                    </div>
                    <div class="hero-features">
                        <span><i>HQ</i>High Quality Content</span>
                        <span><i>RD</i>Result Driven Strategy</span>
                        <span><i>GF</i>Growth Focused Solutions</span>
                    </div>
                </div>

                <div class="camera-stage reveal" aria-hidden="true">
                    <div class="camera-glow"></div>
                    <div class="camera-rig">
                        <div class="camera-body">
                            <span class="camera-top"></span>
                            <span class="camera-screen"></span>
                            <span class="camera-lens"></span>
                            <span class="camera-handle"></span>
                        </div>
                        <div class="camera-stick"></div>
                    </div>
                </div>

                <div class="hero-proof reveal">
                    @foreach (array_slice($content['stats'], 0, 3) as $stat)
                        <div>
                            <strong><span data-counter="{{ $stat['value'] }}">0</span>{{ $stat['suffix'] }}</strong>
                            <small>{{ $stat['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="about section-shell reveal" id="about">
            <div class="media-collage">
                <div class="photo-card photo-main"><span class="play-card">Play</span></div>
                <div class="photo-card photo-room"></div>
                <div class="photo-card photo-edit"></div>
            </div>
            <div class="about-copy">
                <p class="eyebrow">Who We Are</p>
                <h2>We're a Creative & Digital Marketing Agency</h2>
                <p>At Adxon, we blend creativity with strategy to deliver content that connects and campaigns that convert. From production to social media management and paid ads, we do it all.</p>
                <div class="about-points">
                    <span>Creative Production</span>
                    <span>Social Media Experts</span>
                    <span>Performance Driven</span>
                    <span>Transparent Reporting</span>
                </div>
                <a class="button button-primary" href="#contact">More About Us <span>-></span></a>
            </div>
        </section>

        <section class="services section-shell" id="services">
            <div class="section-heading reveal">
                <div>
                    <p class="eyebrow">Our Services</p>
                    <h2>End-to-End Solutions for Your Brand Growth</h2>
                </div>
                <a class="button button-light" href="#contact">View All Services <span>-></span></a>
            </div>
            <div class="service-grid">
                @foreach (array_slice($content['services'], 0, 3) as $service)
                    <article class="service-card reveal">
                        <span class="service-icon">{{ $service['icon'] }}</span>
                        <div>
                            <h3>{{ $service['title'] }}</h3>
                            <p>{{ $service['summary'] }}</p>
                            <a href="#contact">Explore <span>-></span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="packages" id="pricing">
            <div class="section-shell package-shell">
                <div class="package-main">
                    <div class="section-heading reveal">
                        <div>
                            <p class="eyebrow">Our Packages</p>
                            <h2>Flexible Packages for Every Need</h2>
                        </div>
                    </div>
                    <div class="package-tabs reveal" data-package-tabs>
                        @foreach ($packageTabs as $key => $label)
                            <button type="button" class="{{ $loop->first ? 'active' : '' }}" data-package-tab="{{ $key }}">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="package-panels">
                        @foreach ($packageTabs as $groupKey => $label)
                            <div class="pricing-grid package-panel {{ $loop->first ? 'active' : '' }}" data-package-panel="{{ $groupKey }}">
                                @foreach (($packageGroups[$groupKey] ?? []) as $package)
                                    <article class="price-card {{ ! empty($package['highlight']) ? 'featured' : '' }} reveal">
                                        @if (! empty($package['highlight']))
                                            <span class="ribbon">Popular</span>
                                        @endif
                                        <p class="plan-name">{{ $package['name'] }}</p>
                                        <h3>{{ $package['price'] }}<span>{{ $package['period'] }}</span></h3>
                                        <ul>
                                            @foreach ($lines($package['features']) as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                        <small>{{ $package['note'] ?? 'Best for brands with regular growth needs' }}</small>
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="combo-card reveal">
                    <h3>Recommended Combinations</h3>
                    <div class="combo-item">
                        <span>C1</span>
                        <strong>Content B + Management 1</strong>
                        <small>Rs 38k - Rs 44k / month</small>
                    </div>
                    <div class="combo-item">
                        <span>C2</span>
                        <strong>Content B + Management 2</strong>
                        <small>Rs 43k - Rs 50k / month</small>
                    </div>
                    <div class="combo-item">
                        <span>C3</span>
                        <strong>Content C + Management 2 + Ads</strong>
                        <small>Rs 60k - Rs 75k / month</small>
                    </div>
                    <a class="button button-primary" href="#contact">Request Custom Quote <span>-></span></a>
                </aside>
            </div>
        </section>

        <section class="work-testimonials section-shell" id="work">
            <div class="portfolio-panel reveal">
                <p class="eyebrow">Our Portfolio</p>
                <h2>Work That Speaks for Itself</h2>
                <div class="portfolio-strip">
                    @foreach ($content['portfolio'] as $item)
                        <article class="work-card">
                            <div class="work-image"><span>{{ substr($item['title'], 0, 1) }}</span></div>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['metric'] }}</p>
                        </article>
                    @endforeach
                </div>
                <a href="#contact">View Full Portfolio <span>-></span></a>
            </div>
            <div class="testimonial-panel reveal">
                <p class="eyebrow">Clients Love Us</p>
                <h2>What Our Clients Say</h2>
                @foreach (array_slice($content['testimonials'], 0, 1) as $testimonial)
                    <figure class="quote-card">
                        <blockquote>{{ $testimonial['quote'] }}</blockquote>
                        <figcaption>{{ $testimonial['name'] }}<span>{{ $testimonial['role'] }}</span></figcaption>
                    </figure>
                @endforeach
                <div class="dots"><span></span><span></span><span></span></div>
            </div>
        </section>

        <section class="blog-strip section-shell" id="blog">
            <div class="section-heading reveal">
                <div>
                    <p class="eyebrow">Blog</p>
                    <h2>Latest growth notes from Adxon</h2>
                </div>
            </div>
            <div class="blog-grid">
                @foreach ($content['blogs'] as $blog)
                    <article class="blog-card reveal">
                        <p>{{ $blog['category'] }}</p>
                        <h3>{{ $blog['title'] }}</h3>
                        <span>{{ $blog['excerpt'] }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="contact-cta" id="contact">
            <div class="section-shell cta-inner reveal">
                <div>
                    <h2>Ready to grow your brand?</h2>
                    <p>Let's build something amazing together.</p>
                </div>
                <form method="post" action="{{ route('enquiry.store') }}" class="quick-form">
                    @csrf
                    <input name="name" value="{{ old('name') }}" required placeholder="Name" autocomplete="name">
                    <input name="email" type="email" value="{{ old('email') }}" required placeholder="Email" autocomplete="email">
                    <input name="budget" value="{{ old('budget') }}" placeholder="Budget">
                    <input type="hidden" name="message" value="Quick consultation request">
                    <button class="button button-dark" type="submit">Get a Free Consultation <span>-></span></button>
                </form>
                <div class="cta-contact">
                    <span>Call Us<br><strong>{{ $settings['phone'] }}</strong></span>
                    <span>WhatsApp<br><strong>Chat with us</strong></span>
                </div>
            </div>
            @if (request('sent'))
                <p class="sent-toast">Enquiry received. We will get back shortly.</p>
            @endif
        </section>
    </main>

    <footer class="site-footer">
        <div>
            <a class="brand" href="#top">
                <span class="brand-mark">A</span>
                <span><strong>{{ $settings['brand'] }}</strong><small>Digital Agency</small></span>
            </a>
            <p>{{ $seo['description'] }}</p>
        </div>
        <nav>
            <strong>Quick Links</strong>
            <a href="#about">About Us</a>
            <a href="#services">Services</a>
            <a href="#pricing">Packages</a>
            <a href="#work">Portfolio</a>
            <a href="{{ route('admin.dashboard') }}">CMS</a>
        </nav>
        <nav>
            <strong>Our Services</strong>
            @foreach ($serviceHighlights as $service)
                <a href="#services">{{ $service['title'] }}</a>
            @endforeach
        </nav>
        <div>
            <strong>Contact Info</strong>
            <p>{{ $settings['location'] }}<br>{{ $settings['phone'] }}<br>{{ $settings['email'] }}</p>
        </div>
    </footer>

    <script src="{{ asset('assets/app.js') }}" defer></script>
</body>
</html>
