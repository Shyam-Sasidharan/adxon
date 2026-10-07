@php
    $settings = $content['settings'];
    $seo = $content['seo'];
    $pageTitle = $item['title'].' | '.$settings['brand'];
    $pageDescription = $item['summary'] ?? $item['excerpt'];
@endphp
<!doctype html>
<html lang="en">
<head>@include('marketing.head')</head>
<body class="marketing">
    @include('marketing.header')
    <main id="main" class="shell section detail-page">
        <a class="text-link" href="{{ route('home') }}#{{ $kind === 'portfolio' ? 'work' : 'blog' }}"><i data-lucide="arrow-left"></i>Back to {{ $kind === 'portfolio' ? 'selected work' : 'resources' }}</a>
        <p class="eyebrow">{{ $item['category'] }}</p>
        <h1>{{ $item['title'] }}</h1>
        <p class="detail-intro">{{ $pageDescription }}</p>
        @if ($image = \App\Support\AdxonContent::projectImageUrl($item['image_url'] ?? null))<img class="detail-image" src="{{ $image }}" alt="{{ $item['title'] }}">@endif
        @if ($kind === 'portfolio')
            <div class="case-facts">
                @foreach (['client' => 'Client', 'category' => 'Industry / Category', 'services' => 'Services', 'metric' => 'Outcome', 'growth' => 'Growth'] as $key => $label)
                    @if (!empty($item[$key]))<div><span class="eyebrow">{{ $label }}</span><p>{{ $item[$key] }}</p></div>@endif
                @endforeach
            </div>
            @foreach (['challenge' => 'The challenge', 'strategy' => 'The strategy'] as $key => $label)
                @if (!empty($item[$key]))<section class="detail-copy"><h2>{{ $label }}</h2><p>{{ $item[$key] }}</p></section>@endif
            @endforeach
        @elseif (!empty($item['body']))
            <article class="detail-copy"><p>{{ $item['body'] }}</p></article>
        @endif
        <section class="detail-next"><h2>Let's talk about your next chapter.</h2><a class="button primary" href="{{ route('home') }}#contact">Book a consultation <i data-lucide="arrow-up-right"></i></a></section>
    </main>
    @include('marketing.footer')
</body>
</html>
