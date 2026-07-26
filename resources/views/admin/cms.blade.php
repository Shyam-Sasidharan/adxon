@php
    $titles = [
        'dashboard' => 'Dashboard',
        'leads' => 'Lead Center',
        'invoices' => 'Invoice Management',
        'users' => 'User Access',
        'reports' => 'Reports',
        'search' => 'Global Search',
        'enquiries' => 'Website Enquiries',
        'settings' => 'Settings & SEO',
        'no_access' => 'No Access',
    ];
    $title = $titles[$active] ?? ($collections[$active]['title'] ?? ucfirst($active));
    $analytics = $adminData['analytics'];
    $canAccess = $canAccess ?? fn (string $permission): bool => true;
    $permissions = ['Dashboard', 'Analytics', 'CRM', 'Lead View', 'Lead Edit', 'Lead Delete', 'Invoices', 'Payments', 'Payment Analytics', 'CMS', 'Reports', 'User Management', 'Audit Logs', 'Global Search'];
    $maxTraffic = max($analytics['traffic_trend']);
    $maxGrowth = max($analytics['visitor_growth']);
    $maxRevenueTrend = max($analytics['revenue_trend']);
    $maxFunnel = max($analytics['funnel']);
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adxon CMS</title>
    <link rel="icon" href="{{ asset('assets/brand/adxon-mark-dark.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/brand/adxon-full-light.png') }}" alt="Adxon CMS"></a>
        <nav>
            @if ($canAccess('Dashboard'))
                <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            @endif
            @if ($canAccess('Lead View'))
                <a class="{{ $active === 'leads' ? 'active' : '' }}" href="{{ route('admin.leads') }}">Lead Center</a>
            @endif
            @if ($canAccess('Invoices'))
                <a class="{{ $active === 'invoices' ? 'active' : '' }}" href="{{ route('admin.invoices') }}">Invoices</a>
            @endif
            @if ($canAccess('User Management'))
                <a class="{{ $active === 'users' ? 'active' : '' }}" href="{{ route('admin.users') }}">Users & Roles</a>
            @endif
            @if ($canAccess('Reports'))
                <a class="{{ $active === 'reports' ? 'active' : '' }}" href="{{ route('admin.reports') }}">Reports</a>
            @endif
            @if ($canAccess('Global Search'))
                <a class="{{ $active === 'search' ? 'active' : '' }}" href="{{ route('admin.search') }}">Global Search</a>
            @endif
            @if ($canAccess('CMS'))
                <span class="sidebar-label">CMS</span>
                @foreach ($collections as $key => $meta)
                    <a class="{{ $active === $key ? 'active' : '' }}" href="{{ route('admin.collection', $key) }}">{{ $meta['title'] }}</a>
                @endforeach
                <a class="{{ $active === 'enquiries' ? 'active' : '' }}" href="{{ route('admin.enquiries') }}">Enquiries</a>
                <a class="{{ $active === 'settings' ? 'active' : '' }}" href="{{ route('admin.settings') }}">Settings & SEO</a>
            @endif
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
                @if (session('adxon_user'))
                    <p class="admin-userline">Logged in as {{ session('adxon_user.name') }} / {{ session('adxon_user.role') }}</p>
                @endif
            </div>
            <div class="admin-actions">
                @if ($canAccess('Global Search'))
                    <form class="global-search" method="get" action="{{ route('admin.search') }}">
                        <input name="q" value="{{ $query ?? '' }}" placeholder="Search leads, invoices, users">
                    </form>
                @endif
                <button class="theme-toggle" type="button" data-theme-toggle>Mode</button>
                <a class="button button-ghost" href="{{ route('home') }}" target="_blank">View Site</a>
            </div>
        </div>

        @if ($errors->has('access'))
            <p class="admin-alert">{{ $errors->first('access') }}</p>
        @endif

        @if (session('status'))
            <p class="admin-alert">{{ session('status') }}</p>
        @endif

        @if ($active === 'no_access')
            <section class="admin-panel">
                <h2>No modules enabled</h2>
                <p>Your user account is active, but no admin features are enabled for this role. Please ask an owner to update your permissions.</p>
            </section>
        @endif

        @if ($active === 'dashboard')
            <section class="filter-pills date-filters">
                @foreach (['Today', 'Yesterday', 'This Week', 'Last Week', 'This Month', 'Last Month', 'Current Year', 'Custom Range'] as $filter)
                    <span>{{ $filter }}</span>
                @endforeach
            </section>
            @if ($canAccess('Analytics'))
                <section class="admin-metrics">
                    <article><span>{{ $analytics['total_visitors'] }}</span><p>Total Visitors</p></article>
                    <article><span>{{ $analytics['unique_visitors'] }}</span><p>Unique Visitors</p></article>
                    <article><span>{{ $analytics['returning_visitors'] }}</span><p>Returning</p></article>
                    <article><span>{{ $analytics['active_users'] }}</span><p>Active Users</p></article>
                    <article><span>Rs {{ number_format($adminStats['revenue']) }}</span><p>Revenue</p></article>
                    <article><span>{{ $analytics['bounce_rate'] }}</span><p>Bounce Rate</p></article>
                </section>
                <section class="admin-grid two-col">
                    <article class="admin-panel">
                        <h2>Website Traffic</h2>
                        <div class="spark-chart">
                            @foreach ($analytics['traffic_trend'] as $value)
                                <i style="height: {{ max(8, round(($value / $maxTraffic) * 100)) }}%"></i>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Visitor Growth</h2>
                        <div class="spark-chart growth">
                            @foreach ($analytics['visitor_growth'] as $value)
                                <i style="height: {{ max(8, round(($value / $maxGrowth) * 100)) }}%"></i>
                            @endforeach
                        </div>
                    </article>
                </section>
                <section class="admin-grid three-col">
                    <article class="admin-panel">
                        <h2>Traffic Sources</h2>
                        <div class="source-list">
                            @foreach ($analytics['sources'] as $source => $percent)
                                <div><span>{{ $source }}</span><strong>{{ $percent }}%</strong><i style="width: {{ $percent }}%"></i></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Visitor Analytics</h2>
                        <div class="detail-list">
                            <p><strong>Average Session:</strong> {{ $analytics['avg_session'] }}</p>
                            <p><strong>Average Page Time:</strong> {{ $analytics['avg_page_time'] }}</p>
                            <p><strong>Bounce Rate:</strong> {{ $analytics['bounce_rate'] }}</p>
                            <p><strong>Top Pages:</strong> {{ implode(', ', $analytics['top_pages']) }}</p>
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Device Analytics</h2>
                        <div class="donut-card" style="--a: {{ $analytics['devices']['Mobile'] }}%; --b: {{ $analytics['devices']['Desktop'] }}%">
                            <span>{{ $analytics['devices']['Mobile'] }}%</span>
                        </div>
                        <div class="mini-legend">
                            @foreach ($analytics['devices'] as $device => $percent)
                                <span>{{ $device }} {{ $percent }}%</span>
                            @endforeach
                        </div>
                    </article>
                </section>
                <section class="admin-grid three-col">
                    <article class="admin-panel">
                        <h2>Live Visitor Map</h2>
                        <div class="location-grid">
                            @foreach ($analytics['locations'] as $location => $percent)
                                <span>{{ $location }} <strong>{{ $percent }}%</strong></span>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Lead Conversion Funnel</h2>
                        <div class="funnel-list">
                            @foreach ($analytics['funnel'] as $step => $value)
                                <div><span>{{ $step }}</span><strong>{{ $value }}</strong><i style="width: {{ max(8, round(($value / $maxFunnel) * 100)) }}%"></i></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Notifications</h2>
                        <div class="admin-table">
                            @foreach ($adminData['notifications'] as $notification)
                                <div><strong>{{ $notification['message'] }}</strong><span>{{ $notification['date'] }}</span></div>
                            @endforeach
                        </div>
                    </article>
                </section>
                <section class="admin-grid two-col">
                    <article class="admin-panel">
                        <h2>Revenue Overview</h2>
                        <div class="spark-chart revenue">
                            @foreach ($analytics['revenue_trend'] as $value)
                                <i style="height: {{ max(8, round(($value / $maxRevenueTrend) * 100)) }}%"></i>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Browser Analytics</h2>
                        <div class="source-list">
                            @foreach ($analytics['browsers'] as $browser => $percent)
                                <div><span>{{ $browser }}</span><strong>{{ $percent }}%</strong><i style="width: {{ $percent }}%"></i></div>
                            @endforeach
                        </div>
                    </article>
                </section>
            @else
                <section class="admin-panel">
                    <h2>Dashboard Access Enabled</h2>
                    <p>Analytics widgets are hidden for this role.</p>
                </section>
            @endif
        @endif

        @if ($active === 'leads')
            <section class="filter-pills date-filters">
                @foreach (['Date', 'Date Range', 'Stage', 'Assigned User', 'Lead Source', 'Service', 'Status', 'Package', 'Search'] as $filter)
                    <span>{{ $filter }}</span>
                @endforeach
            </section>
            <section class="admin-grid {{ $canAccess('Lead Edit') ? 'two-col' : '' }}">
                @if ($canAccess('Lead Edit'))
                    <form method="post" action="{{ route('admin.leads.save') }}" class="admin-panel form-grid">
                        @csrf
                        <h2>Add Lead</h2>
                        <label>Name <input name="name" required></label>
                        <label>Email <input name="email" type="email"></label>
                        <label>Phone <input name="phone"></label>
                        <label>Business Name <input name="business"></label>
                        <label>Location <input name="location"></label>
                        <label>Service <input name="service"></label>
                        <label>Package <input name="package"></label>
                        <label>Source <input name="source" value="Website"></label>
                        <label>Stage
                            <select name="stage">
                                @foreach ($adminData['lead_stages'] as $stage)
                                    <option>{{ $stage }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Assigned User <input name="assigned_to"></label>
                        <label>Reminder <input name="reminder" type="date"></label>
                        <label class="wide">Notes <textarea name="notes" rows="3"></textarea></label>
                        <button class="button button-primary" type="submit">Save Lead</button>
                    </form>
                @endif
                <section class="admin-panel">
                    <h2>Lead Pipeline</h2>
                    <div class="kanban-grid">
                        @foreach ($adminData['lead_stages'] as $stage)
                            <article>
                                <strong>{{ $stage }}</strong>
                                @foreach (array_filter($adminData['leads'], fn ($lead) => ($lead['stage'] ?? '') === $stage) as $lead)
                                    <span>{{ $lead['name'] }}<small>{{ $lead['service'] }}</small></span>
                                @endforeach
                            </article>
                        @endforeach
                    </div>
                </section>
            </section>
            <section class="admin-panel">
                <h2>Lead List</h2>
                <div class="responsive-table">
                    <table>
                        <thead><tr><th>Date</th><th>Lead</th><th>Contact</th><th>Service</th><th>Stage</th><th>Assigned</th><th>Reminder</th><th>Details</th></tr></thead>
                        <tbody>
                            @foreach ($adminData['leads'] as $lead)
                                <tr>
                                    <td>{{ $lead['date'] }}</td>
                                    <td><strong>{{ $lead['name'] }}</strong><small>{{ $lead['business'] }} - {{ $lead['location'] }}</small></td>
                                    <td>{{ $lead['email'] }}<small>{{ $lead['phone'] }}</small></td>
                                    <td>{{ $lead['service'] }}<small>{{ $lead['package'] }} / {{ $lead['source'] }}</small></td>
                                    <td>
                                        @if ($canAccess('Lead Edit'))
                                            <form method="post" action="{{ route('admin.leads.stage', $lead['id']) }}">
                                                @csrf
                                                @method('patch')
                                                <select name="stage" onchange="this.form.submit()">
                                                    @foreach ($adminData['lead_stages'] as $stage)
                                                        <option {{ $lead['stage'] === $stage ? 'selected' : '' }}>{{ $stage }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            {{ $lead['stage'] }}
                                        @endif
                                    </td>
                                    <td>{{ $lead['assigned_to'] }}</td>
                                    <td>{{ $lead['reminder'] }}</td>
                                    <td>
                                        <details class="row-details">
                                            <summary>View</summary>
                                            <p><strong>Notes:</strong> {{ $lead['notes'] }}</p>
                                            <p><strong>Activity:</strong> {{ implode(', ', $lead['activity']) }}</p>
                                            <p><strong>Actions:</strong> Email / WhatsApp / Upload / Follow-up</p>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($active === 'invoices')
            <section class="admin-metrics">
                <article><span>Rs {{ number_format($adminStats['revenue']) }}</span><p>Total Revenue</p></article>
                <article><span>Rs {{ number_format($adminStats['balance']) }}</span><p>Pending Payments</p></article>
                <article><span>{{ count(array_filter($adminData['invoices'], fn ($invoice) => $invoice['status'] === 'Paid')) }}</span><p>Paid Invoices</p></article>
                <article><span>{{ count(array_filter($adminData['invoices'], fn ($invoice) => $invoice['status'] === 'Overdue')) }}</span><p>Overdue</p></article>
                <article><span>{{ count($adminData['invoices']) }}</span><p>Total Invoices</p></article>
                <article><span>CSV</span><p>Export Ready</p></article>
            </section>
            @if ($canAccess('Payment Analytics'))
                <section class="admin-grid three-col">
                    <article class="admin-panel">
                        <h2>Revenue Trend</h2>
                        <div class="spark-chart revenue">
                            @foreach ($analytics['revenue_trend'] as $value)
                                <i style="height: {{ max(8, round(($value / $maxRevenueTrend) * 100)) }}%"></i>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Payment Status</h2>
                        <div class="source-list">
                            @foreach ($analytics['payment_status'] as $status => $percent)
                                <div><span>{{ $status }}</span><strong>{{ $percent }}%</strong><i style="width: {{ $percent }}%"></i></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="admin-panel">
                        <h2>Reminder Generator</h2>
                        <p>Generate email or WhatsApp reminders with today's date and a custom note before sending.</p>
                        <button class="button button-ghost" type="button">Draft Reminder</button>
                    </article>
                </section>
            @endif
            <form method="post" action="{{ route('admin.invoices.save') }}" class="admin-panel form-grid">
                @csrf
                <h2>Create Invoice</h2>
                <label>Customer <input name="customer" required></label>
                <label>Email <input name="email" type="email"></label>
                <label>Phone <input name="phone"></label>
                <label>Business <input name="business"></label>
                <label>Billing Address <input name="billing_address"></label>
                <label>GST Number <input name="gst"></label>
                <label>Invoice Date <input name="invoice_date" type="date" value="{{ now()->toDateString() }}"></label>
                <label>Due Date <input name="due_date" type="date" value="{{ now()->addDays(15)->toDateString() }}"></label>
                <label>Service Details <input name="service"></label>
                <label>Billing Type
                    <select name="billing_type">
                        @foreach (['One-Time Payment', 'Monthly Subscription', 'Quarterly', 'Half-Yearly', 'Yearly', 'Custom'] as $type)
                            <option>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Status
                    <select name="status">
                        @foreach (['Paid', 'Partially Paid', 'Advance Paid', 'Pending', 'Overdue'] as $status)
                            <option>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Total <input name="total" type="number" min="0" required></label>
                <label>Paid Amount <input name="paid" type="number" min="0" value="0"></label>
                <button class="button button-primary" type="submit">Create Invoice</button>
            </form>
            <section class="admin-panel">
                <h2>Invoice List</h2>
                <div class="responsive-table">
                    <table>
                        <thead><tr><th>Invoice</th><th>Customer</th><th>Billing</th><th>Status</th><th>Total</th><th>Paid</th><th>Balance</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach ($adminData['invoices'] as $invoice)
                                <tr>
                                    <td>{{ $invoice['invoice_no'] }}<small>{{ $invoice['invoice_date'] }} / Due {{ $invoice['due_date'] }}</small></td>
                                    <td>{{ $invoice['customer'] }}<small>{{ $invoice['email'] }}</small></td>
                                    <td>{{ $invoice['service'] }}<small>{{ $invoice['billing_type'] }}</small></td>
                                    <td>{{ $invoice['status'] }}</td>
                                    <td>Rs {{ number_format($invoice['total']) }}</td>
                                    <td>Rs {{ number_format($invoice['paid']) }}</td>
                                    <td>Rs {{ number_format($invoice['balance']) }}</td>
                                    <td><span class="table-actions">Preview PDF Email WhatsApp Print Duplicate</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($active === 'users')
            <section class="admin-grid two-col">
                <form method="post" action="{{ route('admin.users.save') }}" class="admin-panel form-grid">
                    @csrf
                    <h2>Create User</h2>
                    <label>Name <input name="name" required></label>
                    <label>Email <input name="email" type="email" required></label>
                    <label>Password <input name="password" type="password" minlength="6" required></label>
                    <label>Role
                        <select name="role">
                            @foreach (array_keys($adminData['roles']) as $role)
                                <option>{{ $role }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Status
                        <select name="status"><option>Active</option><option>Inactive</option></select>
                    </label>
                    <div class="permission-grid wide">
                        @foreach ($permissions as $permission)
                            <label class="checkbox-label"><input type="checkbox" name="permissions[]" value="{{ $permission }}"> {{ $permission }}</label>
                        @endforeach
                    </div>
                    <label class="checkbox-label wide"><input type="checkbox" name="send_login_email" value="1" checked> Email login details to this user</label>
                    <button class="button button-primary" type="submit">Save User</button>
                </form>
                <section class="admin-panel">
                    <h2>Roles</h2>
                    <div class="detail-list">
                        @foreach ($adminData['roles'] as $role => $items)
                            <p><strong>{{ $role }}:</strong> {{ implode(', ', $items) }}</p>
                        @endforeach
                    </div>
                </section>
            </section>
            <section class="admin-panel">
                <h2>Users</h2>
                <div class="admin-table">
                    @foreach ($adminData['users'] as $user)
                        <div><strong>{{ $user['name'] }} - {{ $user['role'] }}</strong><span>{{ $user['email'] }} / {{ $user['status'] }}</span><p>{{ implode(', ', $user['permissions']) }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($active === 'reports')
            <section class="admin-grid two-col">
                <article class="admin-panel">
                    <h2>Available Reports</h2>
                    <div class="report-links">
                        <a class="button button-ghost" href="{{ route('admin.reports.export', 'analytics') }}">Website Analytics CSV</a>
                        <a class="button button-ghost" href="{{ route('admin.reports.export', 'leads') }}">Lead Report CSV</a>
                        <a class="button button-ghost" href="{{ route('admin.reports.export', 'sales') }}">Sales Report CSV</a>
                    </div>
                </article>
                <article class="admin-panel">
                    <h2>Report Filters</h2>
                    <div class="filter-pills">
                        @foreach (['Today', 'Yesterday', 'This Week', 'Last Week', 'This Month', 'Last Month', 'Current Year', 'Custom Period'] as $filter)
                            <span>{{ $filter }}</span>
                        @endforeach
                    </div>
                </article>
            </section>
            @if ($canAccess('Audit Logs'))
                <section class="admin-panel">
                    <h2>Audit Logs</h2>
                    <div class="admin-table">
                        @foreach (array_reverse($adminData['audit_logs']) as $log)
                            <div><strong>{{ $log['action'] }}</strong><span>{{ $log['user'] }} / {{ $log['date'] }}</span><p>{{ $log['subject'] }}</p></div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif

        @if ($active === 'search')
            <section class="admin-panel">
                <h2>Search Results</h2>
                <p>Showing results for "{{ $query ?? '' }}" across customers, leads, invoices, users, pages, and reports.</p>
                <div class="admin-table">
                    @forelse (($results ?? []) as $result)
                        <div><strong>{{ $result['module'] }} - {{ $result['title'] }}</strong><span>{{ $result['meta'] }}</span></div>
                    @empty
                        <div><strong>No results found</strong><span>Try a lead name, invoice number, email, user, or service.</span></div>
                    @endforelse
                </div>
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
