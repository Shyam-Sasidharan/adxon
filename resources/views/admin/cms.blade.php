@php
    $title = $active === 'dashboard' ? 'Dashboard' : ($collections[$active]['title'] ?? ucfirst($active));
    $metricKeys = ['services', 'packages', 'portfolio', 'testimonials', 'blogs', 'enquiries'];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adxon CMS</title>
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">A</span><span>Adxon CMS</span></a>
        <nav>
            <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            @foreach ($collections as $key => $meta)
                <a class="{{ $active === $key ? 'active' : '' }}" href="{{ route('admin.collection', $key) }}">{{ $meta['title'] }}</a>
            @endforeach
            <a class="{{ $active === 'enquiries' ? 'active' : '' }}" href="{{ route('admin.enquiries') }}">Enquiries</a>
            <a class="{{ $active === 'settings' ? 'active' : '' }}" href="{{ route('admin.settings') }}">Settings & SEO</a>
        </nav>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button class="logout-link" type="submit">Logout</button>
        </form>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <p class="eyebrow">CMS Ready</p>
                <h1>{{ $title }}</h1>
            </div>
            <a class="button button-ghost" href="{{ route('home') }}" target="_blank">View Site</a>
        </div>

        @if (session('status'))
            <p class="admin-alert">{{ session('status') }}</p>
        @endif

        @if ($active === 'dashboard')
            <section class="admin-metrics">
                @foreach ($metricKeys as $metric)
                    <article>
                        <span>{{ count($content[$metric]) }}</span>
                        <p>{{ ucfirst($metric) }}</p>
                    </article>
                @endforeach
            </section>
            <section class="admin-panel">
                <h2>Lead Inbox</h2>
                @if (empty($content['enquiries']))
                    <p>No enquiries yet.</p>
                @else
                    <div class="admin-table">
                        @foreach (array_slice(array_reverse($content['enquiries']), 0, 5) as $enquiry)
                            <div>
                                <strong>{{ $enquiry['name'] }}</strong>
                                <span>{{ $enquiry['email'] }}</span>
                                <p>{{ $enquiry['message'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if (isset($collections[$active]))
            @php($meta = $collections[$active])
            <form method="post" action="{{ route('admin.collection.save', $active) }}" class="admin-panel" data-repeat-form>
                @csrf
                <div class="panel-title">
                    <h2>Edit {{ $meta['title'] }}</h2>
                    <button class="button button-ghost" type="button" data-add-row>Add Item</button>
                </div>
                <div data-rows>
                    @foreach (array_values($content[$active]) as $index => $row)
                        <fieldset class="cms-row">
                            <button class="row-remove" type="button" data-remove-row aria-label="Remove item">&times;</button>
                            @foreach ($meta['fields'] as $field)
                                @if ($field === 'highlight')
                                    <label class="checkbox-label"><input type="checkbox" name="rows[{{ $index }}][highlight]" {{ ! empty($row['highlight']) ? 'checked' : '' }}> Highlight package</label>
                                @elseif (in_array($field, ['summary', 'features', 'quote', 'excerpt', 'answer', 'note'], true))
                                    <label>{{ ucwords(str_replace('_', ' ', $field)) }}<textarea name="rows[{{ $index }}][{{ $field }}]" rows="4">{{ $row[$field] ?? '' }}</textarea></label>
                                @else
                                    <label>{{ ucwords(str_replace('_', ' ', $field)) }}<input name="rows[{{ $index }}][{{ $field }}]" value="{{ $row[$field] ?? '' }}"></label>
                                @endif
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
                <template data-row-template>
                    <fieldset class="cms-row">
                        <button class="row-remove" type="button" data-remove-row aria-label="Remove item">&times;</button>
                        @foreach ($meta['fields'] as $field)
                            @if ($field === 'highlight')
                                <label class="checkbox-label"><input type="checkbox" data-name="highlight"> Highlight package</label>
                            @elseif (in_array($field, ['summary', 'features', 'quote', 'excerpt', 'answer', 'note'], true))
                                <label>{{ ucwords(str_replace('_', ' ', $field)) }}<textarea data-name="{{ $field }}" rows="4"></textarea></label>
                            @else
                                <label>{{ ucwords(str_replace('_', ' ', $field)) }}<input data-name="{{ $field }}"></label>
                            @endif
                        @endforeach
                    </fieldset>
                </template>
                <button class="button button-primary" type="submit">Save {{ $meta['title'] }}</button>
            </form>
        @endif

        @if ($active === 'enquiries')
            <section class="admin-panel">
                <h2>Enquiries</h2>
                @if (empty($content['enquiries']))
                    <p>No enquiries yet.</p>
                @else
                    <div class="enquiry-list">
                        @foreach ($content['enquiries'] as $index => $enquiry)
                            <article>
                                <div>
                                    <strong>{{ $enquiry['name'] }}</strong>
                                    <span>{{ $enquiry['email'] }} &middot; {{ $enquiry['budget'] }}</span>
                                    <p>{{ $enquiry['message'] }}</p>
                                    <small>{{ \Carbon\Carbon::parse($enquiry['created_at'])->format('d M Y, h:i A') }}</small>
                                </div>
                                <form method="post" action="{{ route('admin.enquiries.delete', $index) }}">
                                    @csrf
                                    @method('delete')
                                    <button class="button button-ghost" type="submit">Delete</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($active === 'settings')
            <form method="post" action="{{ route('admin.settings.save') }}" class="admin-panel settings-grid">
                @csrf
                <section>
                    <h2>Business</h2>
                    @foreach (['brand', 'tagline', 'phone', 'email', 'location', 'cta'] as $field)
                        <label>{{ ucwords(str_replace('_', ' ', $field)) }}<input name="{{ $field }}" value="{{ $content['settings'][$field] }}"></label>
                    @endforeach
                </section>
                <section>
                    <h2>Hero</h2>
                    @foreach (['eyebrow', 'headline', 'subline', 'primary_button', 'secondary_button'] as $field)
                        <label>{{ ucwords(str_replace('_', ' ', $field)) }}<input name="hero_{{ $field }}" value="{{ $content['hero'][$field] }}"></label>
                    @endforeach
                </section>
                <section>
                    <h2>SEO</h2>
                    <label>Meta Title <input name="seo_title" value="{{ $content['seo']['title'] }}"></label>
                    <label>Description <textarea name="seo_description" rows="4">{{ $content['seo']['description'] }}</textarea></label>
                    <label>Keywords <textarea name="seo_keywords" rows="3">{{ $content['seo']['keywords'] }}</textarea></label>
                </section>
                <button class="button button-primary" type="submit">Save Settings</button>
            </form>
        @endif
    </main>

    <script src="{{ asset('assets/admin.js') }}" defer></script>
</body>
</html>
