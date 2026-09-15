<nav aria-label="Workspace navigation">
    <span class="sidebar-label">Workspace</span>
    @foreach ([['Dashboard', 'dashboard', 'admin.dashboard', 'Overview', 'layout-dashboard'], ['Campaigns', 'campaigns', 'admin.platform.campaigns', 'Campaigns', 'megaphone'], ['Lead View', 'lead_list', 'admin.leads.list', 'Leads', 'contact'], ['Clients', 'clients', 'admin.platform.clients', 'Clients', 'building-2']] as [$permission, $key, $route, $label, $icon])
        @if ($canAccess($permission))<a class="{{ $active === $key ? 'active' : '' }}" href="{{ route($route) }}"><i data-lucide="{{ $icon }}"></i>{{ $label }}</a>@endif
    @endforeach
    @if ($canAccess('Analytics'))
        <span class="sidebar-label">Performance</span>
        @foreach (['analytics' => ['chart-no-axes-combined', 'Analytics'], 'seo' => ['search', 'SEO'], 'social' => ['messages-square', 'Social media'], 'ads' => ['mouse-pointer-2', 'Advertising']] as $key => [$icon, $label])<a class="{{ $active === $key ? 'active' : '' }}" href="{{ route('admin.platform.'.$key) }}"><i data-lucide="{{ $icon }}"></i>{{ $label }}</a>@endforeach
    @endif
    @if ($canAccess('Reports'))<a class="{{ $active === 'reports' ? 'active' : '' }}" href="{{ route('admin.reports') }}"><i data-lucide="file-chart-column"></i>Reports</a>@endif
    <span class="sidebar-label">Manage</span>
    @if ($canAccess('Lead Edit'))<a class="{{ $active === 'leads' ? 'active' : '' }}" href="{{ route('admin.leads') }}"><i data-lucide="columns-3"></i>Lead pipeline</a>@endif
    @if ($canAccess('Invoices'))<a class="{{ $active === 'invoice_list' ? 'active' : '' }}" href="{{ route('admin.invoices.list') }}"><i data-lucide="receipt"></i>Invoices</a><a class="{{ $active === 'invoices' ? 'active' : '' }}" href="{{ route('admin.invoices') }}"><i data-lucide="circle-plus"></i>Create invoice</a>@endif
    @if ($canAccess('CMS'))
        <a class="{{ $active === 'messages' ? 'active' : '' }}" href="{{ route('admin.platform.messages') }}"><i data-lucide="inbox"></i>Messages</a>
        <div class="sidebar-group {{ isset($collections[$active]) ? 'open' : '' }}" data-sidebar-group><button type="button" class="sidebar-toggle" data-sidebar-toggle aria-expanded="{{ isset($collections[$active]) ? 'true' : 'false' }}"><span><i data-lucide="panels-top-left"></i>Content</span><i data-lucide="chevron-down"></i></button><div class="sidebar-menu">@foreach ($collections as $key => $meta)<a class="{{ $active === $key ? 'active' : '' }}" href="{{ route('admin.collection', $key) }}">{{ $meta['title'] }}</a>@endforeach<a href="{{ route('admin.enquiries') }}">Website enquiries</a></div></div>
    @endif
    @if ($canAccess('User Management'))<a class="{{ $active === 'users' ? 'active' : '' }}" href="{{ route('admin.users') }}"><i data-lucide="users"></i>Team & roles</a>@endif
    @if ($canAccess('Audit Logs'))<a class="{{ $active === 'audit_logs' ? 'active' : '' }}" href="{{ route('admin.reports.audit') }}"><i data-lucide="history"></i>Activity</a>@endif
    @if ($canAccess('CMS'))<a class="{{ $active === 'settings' ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i data-lucide="settings-2"></i>Website settings</a>@endif
    @if ($canAccess('Dashboard'))<a class="{{ $active === 'preferences' ? 'active' : '' }}" href="{{ route('admin.platform.preferences') }}"><i data-lucide="settings"></i>Account & settings</a>@endif
</nav>
