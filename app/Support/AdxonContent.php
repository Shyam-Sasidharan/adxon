<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AdxonContent
{
    public const SECTIONS = [
        'top' => 'Home', 'about' => 'About', 'services' => 'Services',
        'solutions' => 'Solutions', 'work' => 'Case Studies', 'pricing' => 'Pricing',
        'blog' => 'Resources', 'contact' => 'Contact', 'trust' => 'Brand Statistics & Client Logos',
        'results' => 'Performance Results', 'process' => 'Our Process',
        'testimonials' => 'Testimonials', 'faqs' => 'FAQ',
    ];

    public static function sectionActive(array $content, string $section): bool
    {
        return (bool) ($content['sections'][$section] ?? true);
    }

    public const FONT_FAMILIES = [
        'default' => 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'arial' => 'Arial, Helvetica, sans-serif',
        'verdana' => 'Verdana, Geneva, sans-serif',
        'georgia' => 'Georgia, "Times New Roman", serif',
        'times' => '"Times New Roman", Times, serif',
        'courier' => '"Courier New", Courier, monospace',
    ];

    public static function bannerUrl(array $content): string
    {
        $path = $content['hero']['image_path'] ?? '';

        return asset(preg_match('/^uploads\/banners\/[a-f0-9-]+\.(jpg|jpeg|png|webp)$/', $path) ? $path : 'assets/studio.jpg');
    }

    public static function imageUrl(?string $url): ?string
    {
        return $url && filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }

    public function all(): array
    {
        $this->ensureStorage();

        $content = json_decode((string) File::get($this->path()), true);

        if (! is_array($content)) {
            return $this->defaults();
        }

        $defaults = $this->defaults();
        $merged = array_replace($defaults, $content);

        foreach (['settings', 'seo', 'hero', 'sections'] as $group) {
            $merged[$group] = array_replace($defaults[$group], $content[$group] ?? []);
        }

        if (array_column($merged['process'], 'step') === ['Discover', 'Design', 'Deploy', 'Optimize']) {
            $legacy = $merged['process'];
            $merged['process'] = [
                $legacy[0],
                ['step' => 'Analyze', 'summary' => 'Review your audience, competitors, and existing performance to find the right opportunities.'],
                ['step' => 'Strategize', 'summary' => $legacy[1]['summary']],
                ['step' => 'Execute', 'summary' => $legacy[2]['summary']],
                $legacy[3],
                ['step' => 'Scale', 'summary' => 'Build on what works and expand your strongest campaigns with a clear plan.'],
            ];
        }

        return $merged;
    }

    public function save(array $content): void
    {
        File::ensureDirectoryExists(dirname($this->path()));
        File::put($this->path(), json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function appendEnquiry(array $enquiry): void
    {
        $content = $this->all();
        $content['enquiries'][] = $enquiry;
        $this->save($content);
    }

    public function collections(): array
    {
        return [
            'services' => ['title' => 'Services', 'fields' => ['icon', 'title', 'summary', 'benefits']],
            'packages' => ['title' => 'Packages', 'fields' => ['name', 'price', 'period', 'features', 'highlight']],
            'package_content' => ['title' => 'Package - Content Production', 'fields' => ['name', 'price', 'period', 'features', 'note', 'highlight']],
            'package_social' => ['title' => 'Package - Social Media', 'fields' => ['name', 'price', 'period', 'features', 'note', 'highlight']],
            'package_ads' => ['title' => 'Package - Paid Ads', 'fields' => ['name', 'price', 'period', 'features', 'note', 'highlight']],
            'package_combo' => ['title' => 'Package - Combinations', 'fields' => ['name', 'price', 'period', 'features', 'note', 'highlight']],
            'portfolio' => ['title' => 'Case Studies', 'fields' => ['title', 'category', 'summary', 'metric', 'image_url', 'client', 'challenge', 'strategy', 'services', 'growth']],
            'testimonials' => ['title' => 'Testimonials', 'fields' => ['name', 'role', 'quote', 'photo_url', 'rating']],
            'blogs' => ['title' => 'Resources', 'fields' => ['title', 'category', 'excerpt', 'body', 'image_url']],
            'client_logos' => ['title' => 'Client Logos', 'fields' => ['name', 'image_url']],
            'results' => ['title' => 'Performance Results', 'fields' => ['label', 'value', 'suffix', 'context']],
            'stats' => ['title' => 'Brand Statistics', 'fields' => ['label', 'value', 'suffix']],
            'process' => ['title' => 'Our Process', 'fields' => ['step', 'summary']],
            'faqs' => ['title' => 'FAQ', 'fields' => ['question', 'answer']],
        ];
    }

    public function saveCollection(string $key, array $rows): void
    {
        $collections = $this->collections();

        if (! isset($collections[$key])) {
            return;
        }

        $cleanRows = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $clean = [];
            $hasValue = false;

            foreach ($collections[$key]['fields'] as $field) {
                if ($field === 'highlight') {
                    $clean[$field] = Arr::has($row, $field);

                    continue;
                }

                $clean[$field] = trim((string) ($row[$field] ?? ''));
                $hasValue = $hasValue || $clean[$field] !== '';
            }

            if ($hasValue) {
                $cleanRows[] = $clean;
            }
        }

        $content = $this->all();

        if (str_starts_with($key, 'package_') && $key !== 'packages') {
            $group = str_replace('package_', '', $key);
            $content['package_groups'][$group] = $cleanRows;
        } else {
            $content[$key] = $cleanRows;
        }

        $this->save($content);
    }

    public function withAdminAliases(): array
    {
        $content = $this->all();

        foreach (['content', 'social', 'ads', 'combo'] as $group) {
            $content['package_'.$group] = $content['package_groups'][$group] ?? [];
        }

        return $content;
    }

    public function deleteEnquiry(int $index): void
    {
        $content = $this->all();

        if (isset($content['enquiries'][$index])) {
            array_splice($content['enquiries'], $index, 1);
            $this->save($content);
        }
    }

    public function saveSettings(array $payload): void
    {
        validator($payload, [
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=1,min_height=1'],
            'hero_reset_image' => ['sometimes', 'boolean'],
            'sections' => ['sometimes', 'array:'.implode(',', array_keys(self::SECTIONS))],
            'sections.*' => ['required', 'boolean'],
            'font_family' => ['sometimes', 'required', Rule::in(array_keys(self::FONT_FAMILIES))],
            'font_size' => ['sometimes', 'required', 'integer', 'between:12,24'],
            'font_style' => ['sometimes', 'required', Rule::in(['normal', 'italic'])],
            'instagram_url' => ['nullable', 'url:http,https'],
            'linkedin_url' => ['nullable', 'url:http,https'],
            'facebook_url' => ['nullable', 'url:http,https'],
            'privacy_url' => ['nullable', 'url:http,https'],
            'terms_url' => ['nullable', 'url:http,https'],
        ])->validate();
        $content = $this->all();

        if (isset($payload['sections'])) {
            foreach ($payload['sections'] as $section => $enabled) {
                $content['sections'][$section] = (bool) $enabled;
            }
        }

        foreach (['font_family', 'font_size', 'font_style', 'instagram_url', 'linkedin_url', 'facebook_url', 'privacy_url', 'terms_url'] as $field) {
            if (array_key_exists($field, $payload)) {
                $content['settings'][$field] = $payload[$field] ?? '';
            }
        }

        foreach (['brand', 'tagline', 'phone', 'email', 'location', 'cta'] as $field) {
            if (array_key_exists($field, $payload)) {
                $content['settings'][$field] = trim((string) ($payload[$field] ?? ''));
            }
        }

        foreach (['title', 'description', 'keywords'] as $field) {
            if (array_key_exists('seo_'.$field, $payload)) {
                $content['seo'][$field] = trim((string) ($payload['seo_'.$field] ?? ''));
            }
        }

        foreach (['eyebrow', 'headline', 'subline', 'primary_button', 'secondary_button'] as $field) {
            if (array_key_exists('hero_'.$field, $payload)) {
                $content['hero'][$field] = trim((string) ($payload['hero_'.$field] ?? ''));
            }
        }

        if (($payload['hero_image'] ?? null) instanceof UploadedFile) {
            $image = $payload['hero_image'];
            $directory = public_path('uploads/banners');
            File::ensureDirectoryExists($directory);
            $filename = Str::uuid().'.'.$image->extension();
            $image->move($directory, $filename);
            $content['hero']['image_path'] = 'uploads/banners/'.$filename;
        } elseif (! empty($payload['hero_reset_image'])) {
            $content['hero']['image_path'] = '';
        }

        $this->save($content);
    }

    public function lines(?string $value): array
    {
        $lines = preg_split('/\R/', (string) $value) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }

    private function ensureStorage(): void
    {
        if (! File::exists($this->path())) {
            $this->save($this->defaults());
        }
    }

    private function path(): string
    {
        return storage_path('app/adxon/content.json');
    }

    private function defaults(): array
    {
        return [
            'sections' => array_fill_keys(array_keys(self::SECTIONS), true),
            'settings' => [
                'brand' => 'Adxon',
                'tagline' => 'Creative content and result-driven marketing strategies that grow your brand.',
                'phone' => '+91 98765 43210',
                'email' => 'hello@adxonagency.com',
                'location' => 'Palakkad, Kerala, India',
                'cta' => 'Get a Quote',
                'font_family' => 'default',
                'font_size' => 16,
                'font_style' => 'normal',
                'instagram_url' => '',
                'linkedin_url' => '',
                'facebook_url' => '',
                'privacy_url' => '',
                'terms_url' => '',
            ],
            'seo' => [
                'title' => 'Adxon - Premium Digital Marketing & Creative Agency',
                'description' => 'Adxon creates high-quality content, social media management, paid ads, and growth-focused digital marketing for ambitious brands.',
                'keywords' => 'digital marketing agency, content production, social media management, paid ads, branding, creative agency',
            ],
            'hero' => [
                'image_path' => '',
                'eyebrow' => 'Premium Digital Growth Studio',
                'headline' => 'Build attention into revenue.',
                'subline' => 'From powerful content to result-driven marketing, we help brands stand out and scale up.',
                'primary_button' => 'Book Strategy Call',
                'secondary_button' => 'View Work',
            ],
            'stats' => [
                ['label' => 'Happy Clients', 'value' => '50', 'suffix' => '+'],
                ['label' => 'Projects Done', 'value' => '200', 'suffix' => '+'],
                ['label' => 'Years Experience', 'value' => '3', 'suffix' => '+'],
                ['label' => 'Retention', 'value' => '94', 'suffix' => '%'],
            ],
            'services' => [
                ['title' => 'Content Production', 'summary' => 'High-quality photo and video content that tells your brand story.', 'icon' => 'CP'],
                ['title' => 'Social Media Management', 'summary' => 'Plan, post, engage, and grow your social presence with consistency.', 'icon' => 'SM'],
                ['title' => 'Paid Ads & Lead Generation', 'summary' => 'Targeted ad campaigns that bring leads and boost sales.', 'icon' => 'AD'],
                ['title' => 'Branding & Design', 'summary' => 'Premium visuals, campaign assets, and identity systems for stronger recall.', 'icon' => 'BD'],
                ['title' => 'Website Development', 'summary' => 'Fast, responsive websites that showcase your brand and turn visitors into customers.', 'icon' => 'WD', 'benefits' => "Custom website design\nResponsive layouts for every device\nPerformance and search optimization"],
                ['title' => 'App Development', 'summary' => 'Custom mobile and web applications that simplify workflows and connect your business with customers.', 'icon' => 'AP', 'benefits' => "Mobile and web applications\nIntuitive user experiences\nBusiness integrations and scalable functionality"],
            ],
            'packages' => [
                ['name' => 'Basic Production', 'price' => 'Rs 18,000 - Rs 20,000', 'period' => '/ month or shoot', 'features' => "1 shoot day photo + video\n2 professional reels\n8 edited photos\nColor correction and grading\nDelivery in social-ready formats", 'highlight' => false],
                ['name' => 'Active Production', 'price' => 'Rs 28,000 - Rs 32,000', 'period' => '/ month or shoot', 'features' => "1-2 shoot days\n4 reels with walkthroughs\n12-15 edited photos\nCustomized reel cover\nVisual consistency", 'highlight' => true],
                ['name' => 'High-Volume Production', 'price' => 'Rs 40,000 - Rs 50,000', 'period' => '/ month or shoot', 'features' => "2 shoot days\n6-8 reels\n20+ photos\nPriority delivery\nIdeal for launches and large projects", 'highlight' => false],
            ],
            'package_groups' => [
                'content' => [
                    ['name' => 'Basic Production', 'price' => 'Rs 18,000 - Rs 20,000', 'period' => '/ month or shoot', 'features' => "1 shoot day photo + video\n2 professional reels\n8 edited photos\nColor correction and grading\nDelivery in social-ready formats", 'highlight' => false, 'note' => 'Best for clients managing their own posting'],
                    ['name' => 'Active Production', 'price' => 'Rs 28,000 - Rs 32,000', 'period' => '/ month or shoot', 'features' => "1-2 shoot days\n4 reels with walkthroughs\n12-15 edited photos\nCustomized reel cover\nVisual consistency", 'highlight' => true, 'note' => 'Best for brands with regular content needs'],
                    ['name' => 'High-Volume Production', 'price' => 'Rs 40,000 - Rs 50,000', 'period' => '/ month or shoot', 'features' => "2 shoot days\n6-8 reels\n20+ photos\nPriority delivery\nIdeal for launches and large projects", 'highlight' => false, 'note' => 'Best for launches and large projects'],
                ],
                'social' => [
                    ['name' => 'Starter Management', 'price' => 'Rs 15,000 - Rs 18,000', 'period' => '/ month', 'features' => "12 posts per month\nCaption writing\nBasic hashtag research\nProfile optimization\nMonthly report", 'highlight' => false, 'note' => 'Best for consistent basic presence'],
                    ['name' => 'Growth Management', 'price' => 'Rs 25,000 - Rs 30,000', 'period' => '/ month', 'features' => "20 posts per month\nReel planning\nCommunity engagement\nContent calendar\nWeekly performance review", 'highlight' => true, 'note' => 'Best for active brand growth'],
                    ['name' => 'Premium Management', 'price' => 'Rs 40,000 - Rs 55,000', 'period' => '/ month', 'features' => "30+ content pieces\nCreative direction\nInfluencer coordination\nAdvanced reporting\nPriority support", 'highlight' => false, 'note' => 'Best for brands scaling social authority'],
                ],
                'ads' => [
                    ['name' => 'Lead Starter', 'price' => 'Rs 18,000 - Rs 22,000', 'period' => '/ month', 'features' => "Meta ads setup\nCampaign management\nBasic creatives\nLead form optimization\nMonthly report", 'highlight' => false, 'note' => 'Ad spend excluded'],
                    ['name' => 'Sales Accelerator', 'price' => 'Rs 30,000 - Rs 40,000', 'period' => '/ month', 'features' => "Meta + Google ads\nRetargeting setup\nLanding page suggestions\nA/B testing\nWeekly optimization", 'highlight' => true, 'note' => 'Best for lead generation and sales'],
                    ['name' => 'Performance Pro', 'price' => 'Rs 55,000 - Rs 75,000', 'period' => '/ month', 'features' => "Full funnel campaigns\nCreative testing\nConversion tracking\nAdvanced analytics\nStrategy calls", 'highlight' => false, 'note' => 'Best for aggressive scaling'],
                ],
                'combo' => [
                    ['name' => 'Content + Management 1', 'price' => 'Rs 38,000 - Rs 44,000', 'period' => '/ month', 'features' => "Basic production\nStarter management\nContent calendar\nMonthly report\nSocial-ready delivery", 'highlight' => false, 'note' => 'Best starter combination'],
                    ['name' => 'Content + Management 2', 'price' => 'Rs 43,000 - Rs 50,000', 'period' => '/ month', 'features' => "Active production\nGrowth management\nReel planning\nEngagement support\nWeekly review", 'highlight' => true, 'note' => 'Most recommended combination'],
                    ['name' => 'Content + Management + Ads', 'price' => 'Rs 60,000 - Rs 75,000', 'period' => '/ month', 'features' => "High-volume content\nPremium management\nPaid ads handling\nLead optimization\nGrowth reporting", 'highlight' => false, 'note' => 'Best for complete growth'],
                ],
            ],
            'portfolio' => [
                ['title' => 'Interior Campaign', 'category' => 'Content Production', 'summary' => 'Premium visual content for interiors, architecture, and lifestyle brands.', 'metric' => 'High-quality brand visuals'],
                ['title' => 'Real Estate Growth', 'category' => 'Lead Generation', 'summary' => 'Social campaigns and ad creatives for property enquiries and launches.', 'metric' => 'Better quality enquiries'],
                ['title' => 'Food & Product Shoots', 'category' => 'Creative Production', 'summary' => 'Scroll-stopping photo and short-form video assets for premium products.', 'metric' => 'Stronger social engagement'],
            ],
            'process' => [
                ['step' => 'Discover', 'summary' => 'Audit the market, audience, offer, analytics, and growth constraints.'],
                ['step' => 'Design', 'summary' => 'Shape the positioning, creative direction, landing flow, and content system.'],
                ['step' => 'Deploy', 'summary' => 'Launch campaigns, pages, tracking, automation, and reporting workflows.'],
                ['step' => 'Optimize', 'summary' => 'Iterate weekly using experiments, heatmaps, attribution, and sales feedback.'],
            ],
            'testimonials' => [
                ['name' => 'Arun Prakash', 'role' => 'Marketing Head, BuildPro', 'quote' => 'Adxon transformed our social media presence. Their content quality and strategy are top-notch.'],
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
            'client_logos' => [],
            'results' => [],
        ];
    }
}
