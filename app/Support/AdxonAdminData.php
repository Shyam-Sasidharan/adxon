<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdxonAdminData
{
    public function all(): array
    {
        $this->ensureStorage();

        $data = json_decode((string) File::get($this->path()), true);

        if (! is_array($data)) {
            return $this->defaults();
        }

        $merged = array_replace($this->defaults(), $data);
        $merged['analytics'] = array_replace($this->defaults()['analytics'], $data['analytics'] ?? []);
        $merged['lead_stages'] = array_values(array_unique(array_merge($merged['lead_stages'], ['Contacted', 'Proposal', 'Won'])));

        return $merged;
    }

    public function save(array $data): void
    {
        File::ensureDirectoryExists(dirname($this->path()));
        File::put($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function addLead(array $payload): void
    {
        $data = $this->all();
        $data['leads'][] = [
            'id' => (string) Str::uuid(),
            'date' => now()->toDateString(),
            'name' => trim((string) ($payload['name'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'business' => trim((string) ($payload['business'] ?? '')),
            'location' => trim((string) ($payload['location'] ?? '')),
            'service' => trim((string) ($payload['service'] ?? '')),
            'package' => trim((string) ($payload['package'] ?? '')),
            'source' => trim((string) ($payload['source'] ?? 'Website')),
            'campaign_id' => trim((string) ($payload['campaign_id'] ?? '')),
            'score' => isset($payload['score']) ? (int) $payload['score'] : null,
            'stage' => trim((string) ($payload['stage'] ?? 'New Lead')),
            'assigned_to' => trim((string) ($payload['assigned_to'] ?? '')),
            'notes' => trim((string) ($payload['notes'] ?? '')),
            'reminder' => trim((string) ($payload['reminder'] ?? '')),
            'activity' => ['Lead created on '.now()->format('d M Y h:i A')],
        ];
        $data['audit_logs'][] = $this->log('Lead created', $payload['name'] ?? 'New lead');
        $data['notifications'][] = ['date' => now()->format('d M Y h:i A'), 'message' => 'New lead: '.($payload['name'] ?? 'New lead')];
        $this->save($data);
    }

    public function updateLeadStage(string $id, string $stage): void
    {
        $data = $this->all();
        foreach ($data['leads'] as &$lead) {
            if (($lead['id'] ?? '') === $id) {
                $lead['stage'] = $stage;
                $lead['activity'][] = 'Stage changed to '.$stage.' on '.now()->format('d M Y h:i A');
                $data['audit_logs'][] = $this->log('Lead stage updated', $lead['name'] ?? $id);
                break;
            }
        }
        $this->save($data);
    }

    public function addInvoice(array $payload): void
    {
        $data = $this->all();
        $total = (float) ($payload['total'] ?? 0);
        $paid = (float) ($payload['paid'] ?? 0);
        $data['invoices'][] = [
            'id' => (string) Str::uuid(),
            'invoice_no' => 'ADX-'.now()->format('Ymd').'-'.str_pad((string) (count($data['invoices']) + 1), 3, '0', STR_PAD_LEFT),
            'customer' => trim((string) ($payload['customer'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'billing_address' => trim((string) ($payload['billing_address'] ?? '')),
            'business' => trim((string) ($payload['business'] ?? '')),
            'gst' => trim((string) ($payload['gst'] ?? '')),
            'invoice_date' => trim((string) ($payload['invoice_date'] ?? now()->toDateString())),
            'due_date' => trim((string) ($payload['due_date'] ?? now()->addDays(15)->toDateString())),
            'service' => trim((string) ($payload['service'] ?? '')),
            'billing_type' => trim((string) ($payload['billing_type'] ?? 'One-Time Payment')),
            'status' => trim((string) ($payload['status'] ?? 'Pending')),
            'total' => $total,
            'paid' => $paid,
            'balance' => max(0, $total - $paid),
            'payment_history' => $paid > 0 ? [['date' => now()->toDateString(), 'amount' => $paid, 'method' => 'Manual', 'notes' => 'Initial payment']] : [],
        ];
        $data['audit_logs'][] = $this->log('Invoice created', $payload['customer'] ?? 'Customer');
        $data['notifications'][] = ['date' => now()->format('d M Y h:i A'), 'message' => 'Invoice created for '.($payload['customer'] ?? 'Customer')];
        $this->save($data);
    }

    public function addUser(array $payload): array
    {
        $data = $this->all();
        $user = [
            'id' => (string) Str::uuid(),
            'name' => trim((string) ($payload['name'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'password_hash' => Hash::make((string) ($payload['password'] ?? Str::password(12))),
            'role' => trim((string) ($payload['role'] ?? 'Sales Team')),
            'permissions' => array_values((array) ($payload['permissions'] ?? [])),
            'status' => trim((string) ($payload['status'] ?? 'Active')),
            'created_at' => now()->toDateString(),
        ];

        $data['users'][] = $user;
        $data['audit_logs'][] = $this->log('User created', $payload['name'] ?? 'User');
        $this->save($data);

        return $user;
    }

    public function report(string $type): array
    {
        $data = $this->all();
        $leads = $data['leads'];
        $invoices = $data['invoices'];

        return match ($type) {
            'leads' => [
                ['Metric', 'Value'],
                ['Total Leads', count($leads)],
                ['Qualified Leads', $this->countBy($leads, 'stage', 'Qualified')],
                ['Lost Leads', $this->countBy($leads, 'stage', 'Lost')],
                ['Converted Leads', $this->countBy($leads, 'stage', 'Converted')],
            ],
            'sales' => [
                ['Metric', 'Value'],
                ['Total Invoices', count($invoices)],
                ['Paid Invoices', $this->countBy($invoices, 'status', 'Paid')],
                ['Pending Invoices', $this->countBy($invoices, 'status', 'Pending')],
                ['Outstanding Payments', $this->invoiceBalance()],
            ],
            default => [
                ['Metric', 'Value'],
                ['Total Visitors', $data['analytics']['total_visitors']],
                ['Unique Visitors', $data['analytics']['unique_visitors']],
                ['Returning Visitors', $data['analytics']['returning_visitors']],
                ['Bounce Rate', $data['analytics']['bounce_rate']],
            ],
        };
    }

    public function search(string $query): array
    {
        $query = strtolower(trim($query));
        $data = $this->all();

        if ($query === '') {
            return [];
        }

        $matches = [];
        foreach ($data['leads'] as $lead) {
            $haystack = strtolower(implode(' ', array_map('strval', array_filter($lead, fn ($value) => ! is_array($value)))));
            if (str_contains($haystack, $query)) {
                $matches[] = ['module' => 'Lead', 'title' => $lead['name'] ?? 'Lead', 'meta' => ($lead['email'] ?? '').' / '.($lead['stage'] ?? '')];
            }
        }

        foreach ($data['invoices'] as $invoice) {
            $haystack = strtolower(implode(' ', array_map('strval', array_filter($invoice, fn ($value) => ! is_array($value)))));
            if (str_contains($haystack, $query)) {
                $matches[] = ['module' => 'Invoice', 'title' => $invoice['invoice_no'] ?? 'Invoice', 'meta' => ($invoice['customer'] ?? '').' / '.($invoice['status'] ?? '')];
            }
        }

        foreach ($data['users'] as $user) {
            $haystack = strtolower(implode(' ', array_map('strval', array_filter($user, fn ($value) => ! is_array($value)))));
            if (str_contains($haystack, $query)) {
                $matches[] = ['module' => 'User', 'title' => $user['name'] ?? 'User', 'meta' => ($user['email'] ?? '').' / '.($user['role'] ?? '')];
            }
        }

        return $matches;
    }

    public function findActiveUserByEmail(string $email): ?array
    {
        foreach ($this->all()['users'] as $user) {
            if (
                strtolower((string) ($user['email'] ?? '')) === strtolower($email)
                && ($user['status'] ?? '') === 'Active'
                && isset($user['password_hash'])
            ) {
                return $user;
            }
        }

        return null;
    }

    public function invoiceRevenue(): float
    {
        return array_sum(array_map(fn ($invoice) => (float) ($invoice['paid'] ?? 0), $this->all()['invoices']));
    }

    public function invoiceBalance(): float
    {
        return array_sum(array_map(fn ($invoice) => (float) ($invoice['balance'] ?? 0), $this->all()['invoices']));
    }

    public function preferences(string $email): array
    {
        return array_replace(['display_name' => '', 'notifications' => true], $this->all()['preferences'][hash('sha256', strtolower($email))] ?? []);
    }

    public function savePreferences(string $email, array $payload): void
    {
        $data = $this->all();
        $data['preferences'][hash('sha256', strtolower($email))] = $payload;
        $this->save($data);
    }

    private function countBy(array $rows, string $key, string $value): int
    {
        return count(array_filter($rows, fn ($row) => ($row[$key] ?? '') === $value));
    }

    private function log(string $action, string $subject): array
    {
        return [
            'date' => now()->format('d M Y h:i A'),
            'user' => 'Admin',
            'action' => $action,
            'subject' => $subject,
        ];
    }

    private function ensureStorage(): void
    {
        if (! File::exists($this->path())) {
            $this->save($this->defaults());
        }
    }

    private function path(): string
    {
        return storage_path('app/adxon/admin.json');
    }

    private function defaults(): array
    {
        return [
            'campaigns' => [],
            'clients' => [],
            'marketing_metrics' => [],
            'preferences' => [],
            'analytics' => [
                'total_visitors' => 12840,
                'unique_visitors' => 9240,
                'returning_visitors' => 2180,
                'avg_session' => '02m 48s',
                'avg_page_time' => '01m 12s',
                'bounce_rate' => '38%',
                'active_users' => 18,
                'top_pages' => ['Home', 'Packages', 'Portfolio', 'Contact'],
                'traffic_trend' => [420, 520, 610, 690, 760, 840, 910],
                'visitor_growth' => [210, 260, 310, 455, 510, 690, 760],
                'revenue_trend' => [18000, 32000, 28000, 50000, 62000, 74000, 86000],
                'devices' => ['Mobile' => 58, 'Desktop' => 34, 'Tablet' => 8],
                'browsers' => ['Chrome' => 61, 'Safari' => 18, 'Edge' => 11, 'Firefox' => 7, 'Other' => 3],
                'funnel' => ['Visitors' => 12840, 'Leads' => 420, 'Qualified' => 188, 'Converted' => 62],
                'payment_status' => ['Paid' => 52, 'Partially Paid' => 18, 'Pending' => 22, 'Overdue' => 8],
                'sources' => [
                    'Meta Ads' => 34,
                    'Google Search' => 22,
                    'Direct Traffic' => 14,
                    'Organic Search' => 12,
                    'Referral Websites' => 8,
                    'Social Media' => 7,
                    'Other Campaign Sources' => 3,
                ],
                'locations' => ['India' => 68, 'UAE' => 12, 'USA' => 8, 'UK' => 6, 'Other' => 6],
            ],
            'lead_stages' => ['New Lead', 'Qualified', 'Not Qualified', 'Lost', 'Converted'],
            'leads' => [
                [
                    'id' => 'lead-1',
                    'date' => now()->subDays(2)->toDateString(),
                    'name' => 'Arun Prakash',
                    'email' => 'arun@example.com',
                    'phone' => '+91 98765 43210',
                    'business' => 'BuildPro',
                    'location' => 'Palakkad',
                    'service' => 'Content Production',
                    'package' => 'Active Production',
                    'source' => 'Website',
                    'stage' => 'Qualified',
                    'assigned_to' => 'Sales Team',
                    'notes' => 'Interested in monthly content and social media management.',
                    'reminder' => now()->addDays(2)->toDateString(),
                    'activity' => ['Lead created', 'Qualified after call'],
                ],
            ],
            'invoices' => [
                [
                    'id' => 'invoice-1',
                    'invoice_no' => 'ADX-20260723-001',
                    'customer' => 'BuildPro',
                    'email' => 'accounts@buildpro.example',
                    'phone' => '+91 98765 43210',
                    'billing_address' => 'Palakkad, Kerala',
                    'business' => 'BuildPro',
                    'gst' => '',
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(15)->toDateString(),
                    'service' => 'Content + Management 2',
                    'billing_type' => 'Monthly Subscription',
                    'status' => 'Partially Paid',
                    'total' => 50000,
                    'paid' => 25000,
                    'balance' => 25000,
                    'payment_history' => [['date' => now()->toDateString(), 'amount' => 25000, 'method' => 'UPI', 'notes' => 'Advance paid']],
                ],
            ],
            'users' => [
                ['id' => 'owner-1', 'name' => 'Owner', 'email' => 'owner@adxonagency.com', 'role' => 'Owner', 'permissions' => ['Dashboard', 'Analytics', 'CRM', 'Lead View', 'Lead Edit', 'Lead Delete', 'Invoices', 'Payments', 'Payment Analytics', 'CMS', 'Reports', 'User Management', 'Audit Logs', 'Global Search'], 'status' => 'Active', 'created_at' => now()->toDateString()],
            ],
            'roles' => [
                'Owner' => ['Full access to all modules'],
                'Sales Team' => ['Dashboard', 'CRM', 'Lead View', 'Lead Edit', 'Invoices', 'Payments', 'Reports', 'Global Search'],
                'Marketing Team' => ['Dashboard', 'Analytics', 'CMS', 'Reports', 'Global Search'],
            ],
            'notifications' => [
                ['date' => now()->format('d M Y h:i A'), 'message' => 'New lead received from website'],
                ['date' => now()->format('d M Y h:i A'), 'message' => 'Invoice payment reminder due'],
            ],
            'audit_logs' => [
                ['date' => now()->format('d M Y h:i A'), 'user' => 'System', 'action' => 'Admin panel initialized', 'subject' => 'Requirements scaffold'],
            ],
        ];
    }
}
