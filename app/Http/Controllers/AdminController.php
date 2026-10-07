<?php

namespace App\Http\Controllers;

use App\Support\AdxonAdminData;
use App\Support\AdxonContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AdminController extends Controller
{
    public function loginForm(Request $request): RedirectResponse|View
    {
        if ($this->isLoggedIn($request)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request, AdxonAdminData $adminData): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = env('ADXON_ADMIN_USER', 'admin');
        $password = env('ADXON_ADMIN_PASSWORD', 'adxon@123');

        if (hash_equals($user, $credentials['username']) && hash_equals($password, $credentials['password'])) {
            $request->session()->regenerate();
            $request->session()->put('adxon_admin', true);
            $request->session()->put('adxon_user', [
                'name' => $adminData->preferences($user)['display_name'] ?: 'Admin',
                'email' => $user,
                'role' => 'Owner',
                'permissions' => ['*'],
            ]);

            return redirect()->route('admin.dashboard');
        }

        $createdUser = $adminData->findActiveUserByEmail($credentials['username']);

        if ($createdUser && Hash::check($credentials['password'], (string) $createdUser['password_hash'])) {
            $request->session()->regenerate();
            $request->session()->put('adxon_admin', true);
            $request->session()->put('adxon_user', [
                'name' => $adminData->preferences((string) $createdUser['email'])['display_name'] ?: ($createdUser['name'] ?? 'User'),
                'email' => $createdUser['email'] ?? '',
                'role' => $createdUser['role'] ?? '',
                'permissions' => $createdUser['permissions'] ?? [],
            ]);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid login details.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('adxon_admin');
        $request->session()->forget('adxon_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        if (! $this->can($request, 'Dashboard')) {
            $fallback = $this->fallbackRoute($request);

            if ($fallback && $fallback !== 'admin.dashboard') {
                return redirect()->route($fallback)->withErrors(['access' => 'Your role does not include Dashboard access.']);
            }

            return $this->view('no_access', $content, $adminData);
        }

        return $this->view('dashboard', $content, $adminData);
    }

    public function collection(Request $request, AdxonContent $content, AdxonAdminData $adminData, string $collection): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'CMS')) {
            return $redirect;
        }

        if (! isset($content->collections()[$collection])) {
            abort(404);
        }

        return $this->view($collection, $content, $adminData);
    }

    public function saveCollection(Request $request, AdxonContent $content, string $collection): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'CMS')) {
            return $redirect;
        }

        $content->saveCollection($collection, $request->all()['rows'] ?? []);

        return redirect()->route('admin.collection', $collection)->with('status', $content->collections()[$collection]['title'].' saved.');
    }

    public function enquiries(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'CMS')) {
            return $redirect;
        }

        return $this->view('enquiries', $content, $adminData);
    }

    public function deleteEnquiry(Request $request, AdxonContent $content, int $index): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'CMS')) {
            return $redirect;
        }

        $content->deleteEnquiry($index);

        return redirect()->route('admin.enquiries')->with('status', 'Enquiry removed.');
    }

    public function settings(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'CMS')) {
            return $redirect;
        }

        return $this->view('settings', $content, $adminData);
    }

    public function saveSettings(Request $request, AdxonContent $content): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'CMS')) {
            return $redirect;
        }

        $content->saveSettings($request->all());

        return redirect()->route('admin.settings')->with('status', 'Settings saved.');
    }

    public function leads(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Lead View')) {
            return $redirect;
        }

        return $this->view('leads', $content, $adminData);
    }

    public function leadList(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Lead View')) {
            return $redirect;
        }

        return $this->view('lead_list', $content, $adminData);
    }

    public function saveLead(Request $request, AdxonAdminData $adminData): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'Lead Edit')) {
            return $redirect;
        }

        $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'campaign_id' => ['nullable', Rule::in(array_column($adminData->all()['campaigns'], 'id'))],
            'score' => ['nullable', 'integer', 'between:0,100'],
            'stage' => ['sometimes', 'required', Rule::in($adminData->all()['lead_stages'])],
        ]);

        $adminData->addLead($request->all());

        return redirect()->route('admin.leads')->with('status', 'Lead saved.');
    }

    public function updateLeadStage(Request $request, AdxonAdminData $adminData, string $lead): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'Lead Edit')) {
            return $redirect;
        }

        $request->validate(['stage' => ['required', Rule::in($adminData->all()['lead_stages'])]]);
        $adminData->updateLeadStage($lead, (string) $request->input('stage'));

        return redirect()->route('admin.leads')->with('status', 'Lead stage updated.');
    }

    public function invoices(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Invoices')) {
            return $redirect;
        }

        return $this->view('invoices', $content, $adminData);
    }

    public function invoiceList(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Invoices')) {
            return $redirect;
        }

        return $this->view('invoice_list', $content, $adminData);
    }

    public function saveInvoice(Request $request, AdxonAdminData $adminData): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'Invoices')) {
            return $redirect;
        }

        $request->validate([
            'customer' => ['required', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:180'],
            'total' => ['required', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $adminData->addInvoice($request->all());

        return redirect()->route('admin.invoices')->with('status', 'Invoice created.');
    }

    public function users(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'User Management')) {
            return $redirect;
        }

        return $this->view('users', $content, $adminData);
    }

    public function saveUser(Request $request, AdxonAdminData $adminData): RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'User Management')) {
            return $redirect;
        }

        $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180'],
            'password' => ['required', 'string', 'min:6', 'max:120'],
            'send_login_email' => ['nullable', 'boolean'],
        ]);

        $createdUser = $adminData->addUser($request->all());

        if ($request->boolean('send_login_email')) {
            try {
                $this->sendUserLoginEmail($createdUser, (string) $request->input('password'));

                return redirect()->route('admin.users')->with('status', 'User saved and login email sent.');
            } catch (Throwable $exception) {
                Log::error('Adxon user login email failed', [
                    'email' => $createdUser['email'] ?? '',
                    'message' => $exception->getMessage(),
                ]);

                return redirect()->route('admin.users')->with('status', 'User saved, but email could not be sent. Please check mail settings.');
            }
        }

        return redirect()->route('admin.users')->with('status', 'User saved.');
    }

    public function reports(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Reports')) {
            return $redirect;
        }

        return $this->view('reports', $content, $adminData);
    }

    public function auditLogs(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Audit Logs')) {
            return $redirect;
        }

        return $this->view('audit_logs', $content, $adminData);
    }

    public function search(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse|View
    {
        if ($redirect = $this->requirePermission($request, 'Global Search')) {
            return $redirect;
        }

        return $this->view('search', $content, $adminData, [
            'query' => (string) $request->query('q', ''),
            'results' => $adminData->search((string) $request->query('q', '')),
        ]);
    }

    public function exportReport(Request $request, AdxonAdminData $adminData, string $type): Response|RedirectResponse
    {
        if ($redirect = $this->requirePermission($request, 'Reports')) {
            return $redirect;
        }

        $rows = $adminData->report($type);
        $csv = implode("\n", array_map(fn ($row) => implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"', $row)), $rows));

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="adxon-'.$type.'-report.csv"',
        ]);
    }

    protected function view(string $active, AdxonContent $content, AdxonAdminData $adminData, array $extra = []): View
    {
        $request = request();

        return view('admin.cms', [
            'active' => $active,
            'content' => $content->withAdminAliases(),
            'collections' => $content->collections(),
            'adminData' => $adminData->all(),
            'preferences' => $adminData->preferences((string) $request->session()->get('adxon_user.email', '')),
            'canAccess' => fn (string $permission): bool => $this->can($request, $permission),
            'adminStats' => [
                'revenue' => $adminData->invoiceRevenue(),
                'balance' => $adminData->invoiceBalance(),
            ],
        ] + $extra);
    }

    private function isLoggedIn(Request $request): bool
    {
        return $request->session()->get('adxon_admin') === true;
    }

    protected function requirePermission(Request $request, string $permission): ?RedirectResponse
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        if (! $this->can($request, $permission)) {
            return $this->accessDenied($request, $permission);
        }

        return null;
    }

    private function accessDenied(Request $request, string $permission): RedirectResponse
    {
        return redirect()
            ->route($this->fallbackRoute($request) ?? 'admin.dashboard')
            ->withErrors(['access' => 'Your role does not include '.$permission.' access.']);
    }

    private function fallbackRoute(Request $request): ?string
    {
        foreach ($this->moduleRoutes() as $permission => $route) {
            if ($this->can($request, $permission)) {
                return $route;
            }
        }

        return null;
    }

    private function can(Request $request, string $permission): bool
    {
        $permissions = (array) $request->session()->get('adxon_user.permissions', []);

        if (in_array('*', $permissions, true)) {
            return true;
        }

        foreach ($this->permissionAliases($permission) as $alias) {
            if (in_array($alias, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    private function permissionAliases(string $permission): array
    {
        return [
            'Campaigns' => ['Campaigns'],
            'Clients' => ['Clients'],
            'Dashboard' => ['Dashboard'],
            'Analytics' => ['Analytics', 'Website Analytics'],
            'CRM' => ['CRM', 'Lead Center'],
            'Lead View' => ['Lead View', 'CRM', 'Lead Center'],
            'Lead Edit' => ['Lead Edit', 'CRM', 'Lead Center'],
            'Lead Delete' => ['Lead Delete', 'CRM', 'Lead Center'],
            'Invoices' => ['Invoices', 'Invoice', 'Invoice Management'],
            'Payments' => ['Payments', 'Payment'],
            'Payment Analytics' => ['Payment Analytics'],
            'CMS' => ['CMS'],
            'Reports' => ['Reports'],
            'User Management' => ['User Management'],
            'Audit Logs' => ['Audit Logs'],
            'Global Search' => ['Global Search', 'Reports'],
        ][$permission] ?? [$permission];
    }

    private function moduleRoutes(): array
    {
        return [
            'Campaigns' => 'admin.platform.campaigns',
            'Clients' => 'admin.platform.clients',
            'Analytics' => 'admin.platform.analytics',
            'Dashboard' => 'admin.dashboard',
            'Lead View' => 'admin.leads',
            'Invoices' => 'admin.invoices',
            'User Management' => 'admin.users',
            'Reports' => 'admin.reports',
            'Audit Logs' => 'admin.reports.audit',
            'Global Search' => 'admin.search',
            'CMS' => 'admin.settings',
        ];
    }

    private function sendUserLoginEmail(array $user, string $password): void
    {
        Mail::send('mail.admin-user-created', [
            'user' => $user,
            'password' => $password,
            'loginUrl' => route('admin.login'),
        ], function ($message) use ($user): void {
            $message
                ->to((string) $user['email'], (string) $user['name'])
                ->subject('Your Adxon CMS access is ready');
        });
    }
}
