<?php

declare(strict_types=1);

const ADXON_DATA_FILE = __DIR__ . '/../data/content.json';
const ADXON_ADMIN_USER = 'admin';
const ADXON_ADMIN_PASS = 'adxon@123';

function adxon_defaults(): array
{
    return [
        'settings' => [
            'brand' => 'Adxon',
            'tagline' => 'Digital marketing, creative strategy, and conversion systems for ambitious brands.',
            'phone' => '+91 98765 43210',
            'email' => 'hello@adxon.agency',
            'location' => 'India',
            'cta' => 'Start a project',
        ],
        'seo' => [
            'title' => 'Adxon - Premium Digital Marketing & Creative Agency',
            'description' => 'Adxon builds premium digital marketing campaigns, brand systems, websites, and growth funnels for modern companies.',
            'keywords' => 'digital marketing agency, creative agency, SEO, branding, web design, performance marketing',
        ],
        'hero' => [
            'eyebrow' => 'Premium Digital Growth Studio',
            'headline' => 'Build attention into revenue.',
            'subline' => 'Adxon blends strategy, design, media, and automation into high-converting brand experiences that look expensive and work hard.',
            'primary_button' => 'Book Strategy Call',
            'secondary_button' => 'View Work',
        ],
        'stats' => [
            ['label' => 'Campaign ROAS Lift', 'value' => '4.8', 'suffix' => 'x'],
            ['label' => 'Brands Launched', 'value' => '120', 'suffix' => '+'],
            ['label' => 'Revenue Influenced', 'value' => '32', 'suffix' => 'Cr+'],
            ['label' => 'Retention', 'value' => '94', 'suffix' => '%'],
        ],
        'services' => [
            ['title' => 'Performance Marketing', 'summary' => 'Full-funnel paid media across Meta, Google, YouTube, and retargeting journeys.', 'icon' => '01'],
            ['title' => 'Brand & Creative Systems', 'summary' => 'Premium identity, campaign concepts, motion-ready visuals, and brand storytelling.', 'icon' => '02'],
            ['title' => 'SEO & Content Growth', 'summary' => 'Search architecture, editorial engines, technical SEO, and authority-building content.', 'icon' => '03'],
            ['title' => 'Web Design & CRO', 'summary' => 'Elegant, fast websites with analytics, landing pages, and conversion experiments.', 'icon' => '04'],
        ],
        'packages' => [
            ['name' => 'Launch', 'price' => '₹49,000', 'period' => '/mo', 'features' => "Brand audit\nSocial creative calendar\nBasic SEO setup\nMonthly reporting", 'highlight' => false],
            ['name' => 'Scale', 'price' => '₹99,000', 'period' => '/mo', 'features' => "Paid ads management\nLanding page optimization\nContent growth engine\nWeekly performance reviews", 'highlight' => true],
            ['name' => 'Dominance', 'price' => 'Custom', 'period' => '', 'features' => "Full growth squad\nCreative production\nMarketing automation\nExecutive strategy room", 'highlight' => false],
        ],
        'portfolio' => [
            ['title' => 'Auric Labs', 'category' => 'SaaS Launch', 'summary' => 'Positioning, website, and acquisition system for a B2B analytics product.', 'metric' => '212% qualified lead growth'],
            ['title' => 'Noir Casa', 'category' => 'Luxury Commerce', 'summary' => 'Premium ecommerce relaunch with cinematic art direction and retention flows.', 'metric' => '3.6x return on ad spend'],
            ['title' => 'Vanta Clinics', 'category' => 'Local Growth', 'summary' => 'Search-first clinic funnel with trust-led UX and appointment automation.', 'metric' => '68% lower cost per lead'],
        ],
        'process' => [
            ['step' => 'Discover', 'summary' => 'Audit the market, audience, offer, analytics, and growth constraints.'],
            ['step' => 'Design', 'summary' => 'Shape the positioning, creative direction, landing flow, and content system.'],
            ['step' => 'Deploy', 'summary' => 'Launch campaigns, pages, tracking, automation, and reporting workflows.'],
            ['step' => 'Optimize', 'summary' => 'Iterate weekly using experiments, heatmaps, attribution, and sales feedback.'],
        ],
        'testimonials' => [
            ['name' => 'Riya Mehta', 'role' => 'Founder, Noir Casa', 'quote' => 'Adxon made our brand feel truly premium and gave the performance numbers to match.'],
            ['name' => 'Arjun Rao', 'role' => 'CEO, Auric Labs', 'quote' => 'The team moved like a strategy partner, not a vendor. The website and campaigns changed our pipeline quality.'],
        ],
        'faqs' => [
            ['question' => 'Can Adxon handle both creative and ads?', 'answer' => 'Yes. The model combines positioning, creative production, media buying, SEO, and conversion optimization.'],
            ['question' => 'Do you build websites too?', 'answer' => 'Yes. Adxon designs and builds fast, responsive marketing websites and landing pages focused on lead generation.'],
            ['question' => 'How soon can campaigns launch?', 'answer' => 'Most launch plans begin within two weeks after discovery, tracking setup, and creative approval.'],
        ],
        'blogs' => [
            ['title' => 'How Premium Brands Turn Attention Into Demand', 'category' => 'Strategy', 'excerpt' => 'A practical look at positioning, signal quality, and campaigns that compound.'],
            ['title' => 'Landing Pages That Make Paid Media Cheaper', 'category' => 'CRO', 'excerpt' => 'The structural decisions that improve trust, speed, and conversion intent.'],
        ],
        'enquiries' => [],
    ];
}

function adxon_ensure_storage(): void
{
    $dir = dirname(ADXON_DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    if (!file_exists(ADXON_DATA_FILE)) {
        adxon_save(adxon_defaults());
    }
}

function adxon_content(): array
{
    adxon_ensure_storage();
    $raw = file_get_contents(ADXON_DATA_FILE);
    $data = json_decode((string) $raw, true);

    if (!is_array($data)) {
        return adxon_defaults();
    }

    return array_replace_recursive(adxon_defaults(), $data);
}

function adxon_save(array $data): void
{
    $dir = dirname(ADXON_DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    file_put_contents(ADXON_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function adxon_lines(string $value): array
{
    $lines = preg_split('/\R/', $value) ?: [];
    return array_values(array_filter(array_map('trim', $lines), static fn ($line) => $line !== ''));
}

function adxon_slug(string $value): string
{
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '', '-'));
    return $slug !== '' ? $slug : 'item';
}

function adxon_request_value(string $key, string $fallback = ''): string
{
    return trim((string) ($_POST[$key] ?? $fallback));
}

function adxon_is_admin(): bool
{
    return isset($_SESSION['adxon_admin']) && $_SESSION['adxon_admin'] === true;
}

