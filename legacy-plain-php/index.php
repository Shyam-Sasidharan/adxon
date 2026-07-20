<?php

declare(strict_types=1);

require __DIR__ . '/lib/cms.php';

$content = adxon_content();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'enquiry') {
    $content['enquiries'][] = [
        'name' => adxon_request_value('name'),
        'email' => adxon_request_value('email'),
        'budget' => adxon_request_value('budget'),
        'message' => adxon_request_value('message'),
        'created_at' => date('c'),
    ];
    adxon_save($content);
    header('Location: ?sent=1#contact');
    exit;
}

$settings = $content['settings'];
$seo = $content['seo'];
$hero = $content['hero'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seo['title']) ?></title>
    <meta name="description" content="<?= e($seo['description']) ?>">
    <meta name="keywords" content="<?= e($seo['keywords']) ?>">
    <meta property="og:title" content="<?= e($seo['title']) ?>">
    <meta property="og:description" content="<?= e($seo['description']) ?>">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <header class="site-header" data-nav>
        <a class="brand" href="#top" aria-label="Adxon home">
            <span class="brand-mark">A</span>
            <span><?= e($settings['brand']) ?></span>
        </a>
        <button class="menu-button" type="button" data-menu aria-label="Toggle navigation">
            <span></span><span></span>
        </button>
        <nav class="nav-links" data-links>
            <a href="#services">Services</a>
            <a href="#pricing">Pricing</a>
            <a href="#work">Work</a>
            <a href="#process">Process</a>
            <a href="#contact">Contact</a>
            <a class="nav-admin" href="admin/">CMS</a>
        </nav>
    </header>

    <main id="top">
        <section class="hero section-shell">
            <div class="hero-media" aria-hidden="true">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <div class="visual-board">
                    <div class="visual-topline"></div>
                    <div class="visual-grid">
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <div class="visual-chart">
                        <i></i><i></i><i></i><i></i><i></i>
                    </div>
                </div>
            </div>
            <div class="hero-copy reveal">
                <p class="eyebrow"><?= e($hero['eyebrow']) ?></p>
                <h1><?= e($hero['headline']) ?></h1>
                <p class="hero-subline"><?= e($hero['subline']) ?></p>
                <div class="hero-actions">
                    <a class="button button-primary" href="#contact"><?= e($hero['primary_button']) ?></a>
                    <a class="button button-ghost" href="#work"><?= e($hero['secondary_button']) ?></a>
                </div>
            </div>
            <div class="hero-proof reveal">
                <?php foreach ($content['stats'] as $stat): ?>
                    <div>
                        <strong><span data-counter="<?= e((string) $stat['value']) ?>">0</span><?= e($stat['suffix']) ?></strong>
                        <small><?= e($stat['label']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="about section-shell reveal" id="about">
            <p class="eyebrow">About Adxon</p>
            <div class="split">
                <h2>Strategy, creative, media, and conversion under one sharp roof.</h2>
                <p><?= e($settings['tagline']) ?> We build the brand layer and the revenue layer together, so every ad, page, and content system feels consistent from first impression to signed deal.</p>
            </div>
        </section>

        <section class="section-shell" id="services">
            <div class="section-heading reveal">
                <p class="eyebrow">Services</p>
                <h2>Built for brands that need taste and traction.</h2>
            </div>
            <div class="card-grid service-grid">
                <?php foreach ($content['services'] as $service): ?>
                    <article class="glass-card reveal">
                        <span class="card-index"><?= e($service['icon']) ?></span>
                        <h3><?= e($service['title']) ?></h3>
                        <p><?= e($service['summary']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-shell pricing" id="pricing">
            <div class="section-heading reveal">
                <p class="eyebrow">Packages</p>
                <h2>Flexible growth rooms for every stage.</h2>
            </div>
            <div class="pricing-grid">
                <?php foreach ($content['packages'] as $package): ?>
                    <article class="price-card <?= !empty($package['highlight']) ? 'featured' : '' ?> reveal">
                        <div>
                            <p class="plan-name"><?= e($package['name']) ?></p>
                            <h3><?= e($package['price']) ?><span><?= e($package['period']) ?></span></h3>
                        </div>
                        <ul>
                            <?php foreach (adxon_lines((string) $package['features']) as $feature): ?>
                                <li><?= e($feature) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a class="button <?= !empty($package['highlight']) ? 'button-primary' : 'button-ghost' ?>" href="#contact">Choose <?= e($package['name']) ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-shell" id="work">
            <div class="section-heading reveal">
                <p class="eyebrow">Portfolio</p>
                <h2>Recent growth systems with polished edges.</h2>
            </div>
            <div class="portfolio-grid">
                <?php foreach ($content['portfolio'] as $item): ?>
                    <article class="work-card reveal">
                        <div class="work-image">
                            <span><?= e(substr((string) $item['title'], 0, 1)) ?></span>
                        </div>
                        <div>
                            <p><?= e($item['category']) ?></p>
                            <h3><?= e($item['title']) ?></h3>
                            <span><?= e($item['metric']) ?></span>
                        </div>
                        <p><?= e($item['summary']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-shell process" id="process">
            <div class="section-heading reveal">
                <p class="eyebrow">Process</p>
                <h2>From signal to scale without the noise.</h2>
            </div>
            <div class="timeline">
                <?php foreach ($content['process'] as $index => $step): ?>
                    <article class="timeline-item reveal">
                        <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <h3><?= e($step['step']) ?></h3>
                        <p><?= e($step['summary']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-shell testimonials">
            <div class="section-heading reveal">
                <p class="eyebrow">Testimonials</p>
                <h2>Trusted by founders who care about the details.</h2>
            </div>
            <div class="testimonial-grid">
                <?php foreach ($content['testimonials'] as $testimonial): ?>
                    <figure class="quote-card reveal">
                        <blockquote><?= e($testimonial['quote']) ?></blockquote>
                        <figcaption><?= e($testimonial['name']) ?><span><?= e($testimonial['role']) ?></span></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-shell faq">
            <div class="section-heading reveal">
                <p class="eyebrow">FAQ</p>
                <h2>Clear answers before we start.</h2>
            </div>
            <div class="faq-list">
                <?php foreach ($content['faqs'] as $faq): ?>
                    <details class="reveal">
                        <summary><?= e($faq['question']) ?></summary>
                        <p><?= e($faq['answer']) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-shell contact" id="contact">
            <div class="contact-panel reveal">
                <div>
                    <p class="eyebrow">Contact</p>
                    <h2>Tell us what you want to grow next.</h2>
                    <p><?= e($settings['email']) ?> · <?= e($settings['phone']) ?> · <?= e($settings['location']) ?></p>
                    <?php if (isset($_GET['sent'])): ?>
                        <strong class="sent-message">Enquiry received. We will get back shortly.</strong>
                    <?php endif; ?>
                </div>
                <form method="post">
                    <input type="hidden" name="form" value="enquiry">
                    <label>Name <input name="name" required autocomplete="name"></label>
                    <label>Email <input name="email" type="email" required autocomplete="email"></label>
                    <label>Budget <input name="budget" placeholder="₹50k - ₹2L"></label>
                    <label>Project Notes <textarea name="message" required rows="5"></textarea></label>
                    <button class="button button-primary" type="submit"><?= e($settings['cta']) ?></button>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <span><?= e($settings['brand']) ?></span>
        <p><?= e($seo['description']) ?></p>
        <a href="admin/">Admin CMS</a>
    </footer>

    <script src="assets/app.js" defer></script>
</body>
</html>
