<?php

declare(strict_types=1);

session_start();
require __DIR__ . '/../lib/cms.php';

$content = adxon_content();
$message = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (hash_equals(ADXON_ADMIN_USER, adxon_request_value('username')) && hash_equals(ADXON_ADMIN_PASS, adxon_request_value('password'))) {
        $_SESSION['adxon_admin'] = true;
        header('Location: index.php');
        exit;
    }
    $message = 'Invalid login details.';
}

if (!adxon_is_admin()):
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adxon CMS Login</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="admin-body login-body">
    <main class="login-card">
        <a class="brand" href="../"><span class="brand-mark">A</span><span>Adxon CMS</span></a>
        <h1>Admin Login</h1>
        <?php if ($message): ?><p class="admin-alert"><?= e($message) ?></p><?php endif; ?>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="login">
            <label>Username <input name="username" required autofocus></label>
            <label>Password <input name="password" type="password" required></label>
            <button class="button button-primary" type="submit">Sign In</button>
        </form>
        <p class="login-hint">Default: admin / adxon@123</p>
    </main>
</body>
</html>
<?php
exit;
endif;

$collections = [
    'services' => ['title' => 'Services', 'fields' => ['icon', 'title', 'summary']],
    'packages' => ['title' => 'Packages', 'fields' => ['name', 'price', 'period', 'features', 'highlight']],
    'portfolio' => ['title' => 'Portfolio', 'fields' => ['title', 'category', 'summary', 'metric']],
    'testimonials' => ['title' => 'Testimonials', 'fields' => ['name', 'role', 'quote']],
    'blogs' => ['title' => 'Blogs', 'fields' => ['title', 'category', 'excerpt']],
    'faqs' => ['title' => 'FAQ', 'fields' => ['question', 'answer']],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'login') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_settings') {
        foreach (['brand', 'tagline', 'phone', 'email', 'location', 'cta'] as $field) {
            $content['settings'][$field] = adxon_request_value($field);
        }
        foreach (['title', 'description', 'keywords'] as $field) {
            $content['seo'][$field] = adxon_request_value('seo_' . $field);
        }
        foreach (['eyebrow', 'headline', 'subline', 'primary_button', 'secondary_button'] as $field) {
            $content['hero'][$field] = adxon_request_value('hero_' . $field);
        }
        $message = 'Settings saved.';
    }

    if ($action === 'save_collection') {
        $key = adxon_request_value('collection');
        if (isset($collections[$key])) {
            $rows = [];
            $postedRows = $_POST['rows'] ?? [];
            if (is_array($postedRows)) {
                foreach ($postedRows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $clean = [];
                    $hasValue = false;
                    foreach ($collections[$key]['fields'] as $field) {
                        if ($field === 'highlight') {
                            $clean[$field] = isset($row[$field]);
                            continue;
                        }
                        $clean[$field] = trim((string) ($row[$field] ?? ''));
                        $hasValue = $hasValue || $clean[$field] !== '';
                    }
                    if ($hasValue) {
                        $rows[] = $clean;
                    }
                }
            }
            $content[$key] = $rows;
            $message = $collections[$key]['title'] . ' saved.';
        }
    }

    if ($action === 'delete_enquiry') {
        $index = (int) ($_POST['index'] ?? -1);
        if (isset($content['enquiries'][$index])) {
            array_splice($content['enquiries'], $index, 1);
            $message = 'Enquiry removed.';
        }
    }

    adxon_save($content);
}

$active = $_GET['tab'] ?? 'dashboard';
if (!in_array($active, array_merge(['dashboard', 'settings', 'enquiries'], array_keys($collections)), true)) {
    $active = 'dashboard';
}

function admin_nav_class(string $active, string $tab): string
{
    return $active === $tab ? 'active' : '';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adxon CMS</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a class="brand" href="../"><span class="brand-mark">A</span><span>Adxon CMS</span></a>
        <nav>
            <a class="<?= admin_nav_class($active, 'dashboard') ?>" href="?tab=dashboard">Dashboard</a>
            <?php foreach ($collections as $key => $meta): ?>
                <a class="<?= admin_nav_class($active, $key) ?>" href="?tab=<?= e($key) ?>"><?= e($meta['title']) ?></a>
            <?php endforeach; ?>
            <a class="<?= admin_nav_class($active, 'enquiries') ?>" href="?tab=enquiries">Enquiries</a>
            <a class="<?= admin_nav_class($active, 'settings') ?>" href="?tab=settings">Settings & SEO</a>
        </nav>
        <a class="logout-link" href="?logout=1">Logout</a>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <p class="eyebrow">CMS Ready</p>
                <h1><?= e($active === 'dashboard' ? 'Dashboard' : ($collections[$active]['title'] ?? ucfirst((string) $active))) ?></h1>
            </div>
            <a class="button button-ghost" href="../" target="_blank">View Site</a>
        </div>

        <?php if ($message): ?><p class="admin-alert"><?= e($message) ?></p><?php endif; ?>

        <?php if ($active === 'dashboard'): ?>
            <section class="admin-metrics">
                <?php foreach (['services', 'packages', 'portfolio', 'testimonials', 'blogs', 'enquiries'] as $metric): ?>
                    <article>
                        <span><?= e((string) count($content[$metric])) ?></span>
                        <p><?= e(ucfirst($metric)) ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
            <section class="admin-panel">
                <h2>Lead Inbox</h2>
                <?php if (empty($content['enquiries'])): ?>
                    <p>No enquiries yet.</p>
                <?php else: ?>
                    <div class="admin-table">
                        <?php foreach (array_slice(array_reverse($content['enquiries']), 0, 5) as $enquiry): ?>
                            <div>
                                <strong><?= e($enquiry['name']) ?></strong>
                                <span><?= e($enquiry['email']) ?></span>
                                <p><?= e($enquiry['message']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (isset($collections[$active])): ?>
            <?php $meta = $collections[$active]; ?>
            <form method="post" class="admin-panel" data-repeat-form>
                <input type="hidden" name="action" value="save_collection">
                <input type="hidden" name="collection" value="<?= e($active) ?>">
                <div class="panel-title">
                    <h2>Edit <?= e($meta['title']) ?></h2>
                    <button class="button button-ghost" type="button" data-add-row>Add Item</button>
                </div>
                <div data-rows>
                    <?php foreach (array_values($content[$active]) as $index => $row): ?>
                        <fieldset class="cms-row">
                            <button class="row-remove" type="button" data-remove-row aria-label="Remove item">×</button>
                            <?php foreach ($meta['fields'] as $field): ?>
                                <?php if ($field === 'highlight'): ?>
                                    <label class="checkbox-label"><input type="checkbox" name="rows[<?= $index ?>][highlight]" <?= !empty($row['highlight']) ? 'checked' : '' ?>> Highlight package</label>
                                <?php elseif (in_array($field, ['summary', 'features', 'quote', 'excerpt', 'answer'], true)): ?>
                                    <label><?= e(ucwords(str_replace('_', ' ', $field))) ?><textarea name="rows[<?= $index ?>][<?= e($field) ?>]" rows="4"><?= e($row[$field] ?? '') ?></textarea></label>
                                <?php else: ?>
                                    <label><?= e(ucwords(str_replace('_', ' ', $field))) ?><input name="rows[<?= $index ?>][<?= e($field) ?>]" value="<?= e($row[$field] ?? '') ?>"></label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
                <template data-row-template>
                    <fieldset class="cms-row">
                        <button class="row-remove" type="button" data-remove-row aria-label="Remove item">×</button>
                        <?php foreach ($meta['fields'] as $field): ?>
                            <?php if ($field === 'highlight'): ?>
                                <label class="checkbox-label"><input type="checkbox" data-name="highlight"> Highlight package</label>
                            <?php elseif (in_array($field, ['summary', 'features', 'quote', 'excerpt', 'answer'], true)): ?>
                                <label><?= e(ucwords(str_replace('_', ' ', $field))) ?><textarea data-name="<?= e($field) ?>" rows="4"></textarea></label>
                            <?php else: ?>
                                <label><?= e(ucwords(str_replace('_', ' ', $field))) ?><input data-name="<?= e($field) ?>"></label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </fieldset>
                </template>
                <button class="button button-primary" type="submit">Save <?= e($meta['title']) ?></button>
            </form>
        <?php endif; ?>

        <?php if ($active === 'enquiries'): ?>
            <section class="admin-panel">
                <h2>Enquiries</h2>
                <?php if (empty($content['enquiries'])): ?>
                    <p>No enquiries yet.</p>
                <?php else: ?>
                    <div class="enquiry-list">
                        <?php foreach ($content['enquiries'] as $index => $enquiry): ?>
                            <article>
                                <div>
                                    <strong><?= e($enquiry['name']) ?></strong>
                                    <span><?= e($enquiry['email']) ?> · <?= e($enquiry['budget']) ?></span>
                                    <p><?= e($enquiry['message']) ?></p>
                                    <small><?= e(date('d M Y, h:i A', strtotime((string) $enquiry['created_at']))) ?></small>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_enquiry">
                                    <input type="hidden" name="index" value="<?= $index ?>">
                                    <button class="button button-ghost" type="submit">Delete</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($active === 'settings'): ?>
            <form method="post" class="admin-panel settings-grid">
                <input type="hidden" name="action" value="save_settings">
                <section>
                    <h2>Business</h2>
                    <?php foreach (['brand', 'tagline', 'phone', 'email', 'location', 'cta'] as $field): ?>
                        <label><?= e(ucwords(str_replace('_', ' ', $field))) ?><input name="<?= e($field) ?>" value="<?= e($content['settings'][$field]) ?>"></label>
                    <?php endforeach; ?>
                </section>
                <section>
                    <h2>Hero</h2>
                    <?php foreach (['eyebrow', 'headline', 'subline', 'primary_button', 'secondary_button'] as $field): ?>
                        <label><?= e(ucwords(str_replace('_', ' ', $field))) ?><input name="hero_<?= e($field) ?>" value="<?= e($content['hero'][$field]) ?>"></label>
                    <?php endforeach; ?>
                </section>
                <section>
                    <h2>SEO</h2>
                    <label>Meta Title <input name="seo_title" value="<?= e($content['seo']['title']) ?>"></label>
                    <label>Description <textarea name="seo_description" rows="4"><?= e($content['seo']['description']) ?></textarea></label>
                    <label>Keywords <textarea name="seo_keywords" rows="3"><?= e($content['seo']['keywords']) ?></textarea></label>
                </section>
                <button class="button button-primary" type="submit">Save Settings</button>
            </form>
        <?php endif; ?>
    </main>

    <script src="../assets/admin.js" defer></script>
</body>
</html>
