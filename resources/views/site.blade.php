@php
    $settings = $content['settings'];
    $seo = $content['seo'];
    $hero = $content['hero'];
    $packageGroups = $content['package_groups'] ?? ['content' => $content['packages']];
    $packageTabs = ['content' => 'Content production', 'social' => 'Social media', 'ads' => 'Paid advertising', 'combo' => 'Complete growth'];
    $projectImages = ['interiors.jpg', 'architecture.jpg', 'food.jpg'];
@endphp
<!doctype html>
<html lang="en">
<head>@include('marketing.head')</head>
<body class="marketing">
    @include('marketing.header')
    <main id="main">
        <section class="agency-hero" id="top">
            <img class="hero-photo" src="{{ asset('assets/studio.jpg') }}" alt="" fetchpriority="high">
            <div class="shell hero-content">
                <p class="eyebrow"><span class="status-dot"></span>{{ $hero['eyebrow'] }}</p>
                <h1>{{ $settings['brand'] }}<span>{{ $hero['headline'] }}</span></h1>
                <p class="hero-description">{{ $hero['subline'] }}</p>
                <div class="action-row">
                    <a class="button primary" href="#contact">{{ $hero['primary_button'] }} <i data-lucide="arrow-up-right"></i></a>
                    <a class="button outline-white" href="#services">Explore our services <i data-lucide="arrow-right"></i></a>
                </div>
                <div class="hero-signoff"><span>STRATEGY / CREATIVE / PERFORMANCE</span><a href="#work">{{ $hero['secondary_button'] }} <i data-lucide="arrow-down"></i></a></div>
            </div>
        </section>

        <section class="trust-band">
            <div class="shell"><p class="eyebrow">Built for growing brands.<br>Backed by real work.</p><div class="trust-stats">
                @foreach ($content['stats'] as $stat)<div><strong><span data-counter="{{ $stat['value'] }}">{{ $stat['value'] }}</span>{{ $stat['suffix'] }}</strong><span>{{ $stat['label'] }}</span></div>@endforeach
            </div></div>
            @if (!empty($content['client_logos']))<div class="shell client-logo-strip">@foreach ($content['client_logos'] as $client)@if($url = \App\Support\AdxonContent::imageUrl($client['image_url'] ?? null))<img src="{{ $url }}" alt="{{ $client['name'] }}" loading="lazy">@else<span>{{ $client['name'] }}</span>@endif @endforeach</div>@endif
        </section>

        <section class="shell section about-layout" id="about">
            <div><p class="eyebrow">01 / The Adxon approach</p><h2>Good creativity.<br>Better business.</h2></div>
            <div><p class="large-copy">We connect the dots between your brand, your audience, and your next stage of growth.</p><p>From content production to social media and paid campaigns, our team brings strategy and execution together. One partner, a clear direction, and work that moves your business forward.</p><a class="text-link" href="#contact">Meet your next growth partner <i data-lucide="arrow-up-right"></i></a></div>
        </section>

        <section class="service-band" id="services"><div class="shell section">
            <div class="section-heading"><div><p class="eyebrow">02 / What we do</p><h2>Everything your brand<br>needs to go further.</h2></div><p>Strong ideas. Thoughtful execution.<br>A team invested in your growth.</p></div>
            <div class="services-grid">
                @foreach ($content['services'] as $service)
                    <article class="service-tile">
                        <div class="tile-top"><i data-lucide="{{ ['clapperboard', 'messages-square', 'chart-no-axes-combined', 'pen-tool'][$loop->index % 4] }}"></i><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <h3>{{ $service['title'] }}</h3><p>{{ $service['summary'] }}</p>
                        <details><summary>Learn more <i data-lucide="plus"></i></summary>
                            <ul class="benefit-list">@foreach ($lines($service['benefits'] ?? "A strategy tailored to your brand\nClear deliverables and reporting") as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>
                            <a href="#contact" class="text-link" data-service="{{ $service['title'] }}">Discuss your project <i data-lucide="arrow-right"></i></a>
                        </details>
                    </article>
                @endforeach
            </div>
        </div></section>

        <section class="shell section" id="solutions">
            <div class="section-heading"><div><p class="eyebrow">03 / Built around your goals</p><h2>A strategy with<br>your business at its heart.</h2></div><a class="button secondary" href="#contact">Find your solution <i data-lucide="arrow-up-right"></i></a></div>
            <div class="solution-rows">
                @foreach (['Build your presence' => 'A distinctive identity and consistent content that help the right people discover your brand.', 'Turn attention into enquiries' => 'Focused campaigns and clear landing experiences that turn interest into conversations.', 'Make every month count' => 'Transparent reporting, creative testing, and practical improvements as your business grows.'] as $name => $description)
                    <div><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $name }}</h3><p>{{ $description }}</p><i data-lucide="arrow-up-right"></i></div>
                @endforeach
            </div>
        </section>

        @if (!empty($content['results']))
            <section class="results-band"><div class="shell section"><div class="section-heading"><div><p class="eyebrow">Measured. Reported. Improved.</p><h2>Progress you can point to.</h2></div></div><div class="result-grid">
                @foreach ($content['results'] as $result)<article><strong><span data-counter="{{ $result['value'] }}">{{ $result['value'] }}</span>{{ $result['suffix'] }}</strong><h3>{{ $result['label'] }}</h3><p>{{ $result['context'] ?? '' }}</p></article>@endforeach
            </div></div></section>
        @endif

        <section class="work-band" id="work"><div class="shell section">
            <div class="section-heading"><div><p class="eyebrow">04 / Selected work</p><h2>Different brands.<br>The same commitment.</h2></div><a class="text-link" href="#contact">Your project could be next <i data-lucide="arrow-up-right"></i></a></div>
            <div class="work-grid">
                @foreach ($content['portfolio'] as $index => $item)
                    <article class="project">
                        <a class="project-visual" href="{{ route('site.detail', ['kind' => 'portfolio', 'index' => $index]) }}" aria-label="View {{ $item['title'] }}"><img src="{{ \App\Support\AdxonContent::imageUrl($item['image_url'] ?? null) ?? asset('assets/'.$projectImages[$loop->index % 3]) }}" alt="{{ $item['category'] }}" loading="lazy"><span>{{ $item['category'] }}</span></a>
                        <div class="project-title"><h3>{{ $item['title'] }}</h3><i data-lucide="arrow-up-right"></i></div><p>{{ $item['metric'] }}</p>
                        <a class="text-link" href="{{ route('site.detail', ['kind' => 'portfolio', 'index' => $index]) }}">View case study <i data-lucide="arrow-right"></i></a>
                    </article>
                @endforeach
            </div>
        </div></section>

        <section class="shell section" id="process">
            <div class="section-heading"><div><p class="eyebrow">05 / From first call to next milestone</p><h2>Clear steps. Shared ambition.</h2></div></div>
            <div class="process-grid">
                @foreach ($content['process'] as $step)<article><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $step['step'] }}</h3><p>{{ $step['summary'] }}</p></article>@endforeach
            </div>
        </section>

        <section class="testimonial-band"><div class="shell section">
            <p class="eyebrow">In our clients' words</p>
            <div class="testimonial-grid">
                @foreach ($content['testimonials'] as $testimonial)
                    <figure><i data-lucide="quote"></i><blockquote>{{ $testimonial['quote'] }}</blockquote>
                        @if (is_numeric($testimonial['rating'] ?? null) && $testimonial['rating'] >= 1 && $testimonial['rating'] <= 5)<div class="rating" aria-label="{{ $testimonial['rating'] }} out of 5 stars">@for($star = 0; $star < (int) $testimonial['rating']; $star++)<i data-lucide="star"></i>@endfor</div>@endif
                        <figcaption>@if ($photo = \App\Support\AdxonContent::imageUrl($testimonial['photo_url'] ?? null))<img class="avatar" src="{{ $photo }}" alt="{{ $testimonial['name'] }}" loading="lazy">@else<span class="avatar">{{ substr($testimonial['name'], 0, 1) }}</span>@endif<div><strong>{{ $testimonial['name'] }}</strong><small>{{ $testimonial['role'] }}</small></div></figcaption>
                    </figure>
                @endforeach
            </div>
        </div></section>

        <section class="shell section" id="pricing">
            <div class="section-heading"><div><p class="eyebrow">06 / Invest in your next chapter</p><h2>The right support.<br>At the right scale.</h2></div><p>Clear packages for content, social, and performance.<br>Need something different? Let's build it together.</p></div>
            <div class="segmented" role="tablist" aria-label="Package category">
                @foreach ($packageTabs as $key => $label)<button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-tab="{{ $key }}">{{ $label }}</button>@endforeach
            </div>
            @foreach ($packageTabs as $key => $label)
                <div class="price-grid" id="panel-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}" data-panel="{{ $key }}" @if(!$loop->first) hidden @endif>
                    @forelse ($packageGroups[$key] ?? [] as $package)
                        <article class="pricing-card {{ !empty($package['highlight']) ? 'recommended' : '' }}">
                            <span class="plan-badge">{{ !empty($package['highlight']) ? 'Recommended' : $label }}</span>
                            <h3>{{ $package['name'] }}</h3><p class="price">{{ $package['price'] }}<small>{{ $package['period'] }}</small></p>
                            <ul>@foreach ($lines($package['features']) as $feature)<li><i data-lucide="check"></i>{{ $feature }}</li>@endforeach</ul>
                            <p class="package-note">{{ $package['note'] ?? '' }}</p>
                            <a href="#contact" class="button {{ !empty($package['highlight']) ? 'primary' : 'secondary' }}" data-package="{{ $package['name'] }}">Choose package <i data-lucide="arrow-up-right"></i></a>
                        </article>
                    @empty<p>Contact us for a tailored package.</p>@endforelse
                </div>
            @endforeach
        </section>

        <section class="resource-band" id="blog"><div class="shell section">
            <div class="section-heading"><div><p class="eyebrow">07 / Ideas worth your time</p><h2>A little insight.<br>A lot of possibility.</h2></div>
                <label class="filter-label">Category<select data-blog-filter><option value="">All resources</option>@foreach (array_unique(array_column($content['blogs'], 'category')) as $category)<option>{{ $category }}</option>@endforeach</select></label>
            </div>
            <div class="resource-grid">
                @foreach ($content['blogs'] as $index => $blog)<article class="resource-card" data-category="{{ $blog['category'] }}"><span class="eyebrow">{{ $blog['category'] }}</span><h3>{{ $blog['title'] }}</h3><p>{{ $blog['excerpt'] }}</p><a href="{{ route('site.detail', ['kind' => 'blogs', 'index' => $index]) }}" class="text-link">Read article <i data-lucide="arrow-up-right"></i></a></article>@endforeach
            </div>
        </div></section>

        <section class="shell section faq-layout"><div><p class="eyebrow">A few good questions</p><h2>Before we begin.</h2></div><div>@foreach ($content['faqs'] as $faq)<details class="faq"><summary>{{ $faq['question'] }}<i data-lucide="plus"></i></summary><p>{{ $faq['answer'] }}</p></details>@endforeach</div></section>

        <section class="contact-band" id="contact"><div class="shell section contact-layout">
            <div><p class="eyebrow">Your next chapter starts here</p><h2>Ready to grow<br>your business?</h2><p>Let's build a digital marketing strategy that delivers measurable results.</p>
                <a class="text-link" href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }} <i data-lucide="arrow-up-right"></i></a><p>{{ $settings['phone'] }}<br>{{ $settings['location'] }}</p>
            </div>
            <form method="post" action="{{ route('enquiry.store') }}" class="consultation-form">
                @csrf
                <h3>Book a free consultation</h3>
                @if (request('sent'))<p class="alert success" role="status">Thank you. Your enquiry has been received.</p>@endif
                @if ($errors->any())<div class="alert" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <label>Your name<input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"></label>
                <label>Email address<input name="email" type="email" value="{{ old('email') }}" required maxlength="160" autocomplete="email"></label>
                <label>Monthly budget<input name="budget" value="{{ old('budget') }}" maxlength="120" placeholder="e.g. Rs 30,000 - Rs 50,000"></label>
                <label>What do you have in mind?<textarea name="message" rows="3" required maxlength="2000">{{ old('message') }}</textarea></label>
                <button class="button primary" type="submit">Book a free consultation <i data-lucide="arrow-up-right"></i></button>
            </form>
        </div></section>
    </main>
    @include('marketing.footer')
</body>
</html>
