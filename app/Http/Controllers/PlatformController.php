<?php

namespace App\Http\Controllers;

use App\Support\AdxonAdminData;
use App\Support\AdxonContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformController extends AdminController
{
    public const METRICS = [
        'seo' => ['organic_traffic' => 'Organic traffic', 'ranked_keywords' => 'Ranked keywords', 'backlinks' => 'Backlinks', 'domain_authority' => 'Domain authority', 'search_visibility' => 'Search visibility (%)', 'keywords_improved' => 'Keywords improved'],
        'social' => ['followers' => 'Followers', 'reach' => 'Reach', 'engagements' => 'Engagements', 'impressions' => 'Impressions', 'clicks' => 'Clicks', 'posts' => 'Posts'],
    ];

    private const MODULES = [
        'campaigns' => 'Campaigns', 'clients' => 'Clients', 'analytics' => 'Analytics',
        'seo' => 'Analytics', 'social' => 'Analytics', 'ads' => 'Analytics',
        'messages' => 'CMS', 'preferences' => 'Dashboard',
    ];

    public function index(Request $request, AdxonContent $content, AdxonAdminData $adminData, string $module)
    {
        abort_unless(isset(self::MODULES[$module]), 404);
        if ($redirect = $this->requirePermission($request, self::MODULES[$module])) {
            return $redirect;
        }

        return $this->view($module, $content, $adminData);
    }

    public function saveCampaign(Request $request, AdxonAdminData $adminData)
    {
        if ($redirect = $this->requirePermission($request, 'Campaigns')) {
            return $redirect;
        }
        $payload = $request->validate([
            'id' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:160'],
            'client' => ['nullable', 'string', 'max:160'],
            'platform' => ['required', Rule::in(['Google Ads', 'Meta Ads', 'SEO', 'Social Media', 'Email', 'Other'])],
            'type' => ['required', Rule::in(['Awareness', 'Traffic', 'Lead Generation', 'Sales'])],
            'status' => ['required', Rule::in(['Draft', 'Active', 'Paused', 'Completed'])],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'budget' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'spend' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'impressions' => ['required', 'integer', 'min:0', 'max:999999999'],
            'clicks' => ['required', 'integer', 'min:0', 'lte:impressions'],
            'conversions' => ['required', 'integer', 'min:0', 'lte:clicks'],
            'revenue' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);
        $data = $adminData->all();
        $index = ! empty($payload['id']) ? array_search($payload['id'], array_column($data['campaigns'], 'id'), true) : false;
        abort_if(! empty($payload['id']) && $index === false, 404);
        $payload['id'] = $payload['id'] ?? (string) Str::uuid();
        $payload['client'] = $payload['client'] ?? '';
        if ($index === false) {
            $data['campaigns'][] = $payload;
        } else {
            $data['campaigns'][$index] = $payload;
        }
        $this->recordActivity($data, 'Campaign saved', $payload['name']);
        $adminData->save($data);

        return redirect()->route('admin.platform.campaigns')->with('status', 'Campaign saved.');
    }

    public function campaignStatus(Request $request, AdxonAdminData $adminData, string $campaign)
    {
        if ($redirect = $this->requirePermission($request, 'Campaigns')) {
            return $redirect;
        }
        $validated = $request->validate(['status' => ['required', Rule::in(['Active', 'Paused'])]]);
        $data = $adminData->all();
        $index = array_search($campaign, array_column($data['campaigns'], 'id'), true);
        abort_if($index === false, 404);
        $data['campaigns'][$index]['status'] = $validated['status'];
        $this->recordActivity($data, 'Campaign '.$validated['status'], $data['campaigns'][$index]['name']);
        $adminData->save($data);

        return back()->with('status', 'Campaign status updated.');
    }

    public function saveClient(Request $request, AdxonAdminData $adminData)
    {
        if ($redirect = $this->requirePermission($request, 'Clients')) {
            return $redirect;
        }
        $payload = $request->validate([
            'id' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:160'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'industry' => ['nullable', 'string', 'max:120'],
            'team' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['Active', 'Onboarding', 'Inactive'])],
        ]);
        $data = $adminData->all();
        $payload = array_replace(['phone' => '', 'industry' => '', 'team' => '', 'notes' => ''], $payload);
        $index = ! empty($payload['id']) ? array_search($payload['id'], array_column($data['clients'], 'id'), true) : false;
        abort_if(! empty($payload['id']) && $index === false, 404);
        $payload['id'] = $payload['id'] ?? (string) Str::uuid();
        $payload['created_at'] = $index === false ? now()->toDateString() : $data['clients'][$index]['created_at'];
        if ($index === false) {
            $data['clients'][] = $payload;
        } else {
            $data['clients'][$index] = $payload;
        }
        $this->recordActivity($data, 'Client saved', $payload['company']);
        $adminData->save($data);

        return redirect()->route('admin.platform.clients')->with('status', 'Client saved.');
    }

    public function bulkLeads(Request $request, AdxonAdminData $adminData)
    {
        if ($redirect = $this->requirePermission($request, 'Lead Edit')) {
            return $redirect;
        }
        $data = $adminData->all();
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', Rule::in(array_column($data['leads'], 'id'))],
            'stage' => ['required', Rule::in($data['lead_stages'])],
        ]);
        foreach ($data['leads'] as &$lead) {
            if (in_array($lead['id'], $payload['ids'], true)) {
                $lead['stage'] = $payload['stage'];
                $lead['activity'][] = 'Stage changed to '.$payload['stage'].' on '.now()->toDateTimeString();
            }
        }
        unset($lead);
        $this->recordActivity($data, 'Lead stages updated', count($payload['ids']).' leads');
        $adminData->save($data);

        return back()->with('status', 'Selected leads updated.');
    }

    public function saveMetrics(Request $request, AdxonAdminData $adminData, string $channel)
    {
        abort_unless(isset(self::METRICS[$channel]), 404);
        if ($redirect = $this->requirePermission($request, 'Analytics')) {
            return $redirect;
        }
        $rules = ['date' => ['required', 'date_format:Y-m-d']];
        foreach (self::METRICS[$channel] as $key => $label) {
            $rules[$key] = ['required', 'numeric', 'min:0', in_array($key, ['domain_authority', 'search_visibility']) ? 'max:100' : 'max:999999999'];
        }
        $payload = $request->validate($rules);
        $data = $adminData->all();
        $data['marketing_metrics'][$channel][$payload['date']] = $payload;
        ksort($data['marketing_metrics'][$channel]);
        $this->recordActivity($data, ucfirst($channel).' metrics recorded', $payload['date']);
        $adminData->save($data);

        return back()->with('status', 'Performance snapshot saved.');
    }

    public function reportPage(Request $request, AdxonContent $content, AdxonAdminData $adminData)
    {
        if ($redirect = $this->requirePermission($request, 'Reports')) {
            return $redirect;
        }
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])]]);
        $data = $adminData->all();
        foreach (['campaigns' => 'start_date', 'leads' => 'date', 'invoices' => 'invoice_date'] as $key => $date) {
            $data[$key] = array_values(array_filter($data[$key], fn ($row) => (! $request->filled('from') || ($row[$date] ?? '') >= $request->input('from'))
                && (! $request->filled('to') || ($row[$date] ?? '') <= $request->input('to'))
            ));
        }
        foreach ($data['marketing_metrics'] as $channel => $snapshots) {
            $data['marketing_metrics'][$channel] = array_filter($snapshots, fn ($snapshot) => (! $request->filled('from') || $snapshot['date'] >= $request->input('from'))
                && (! $request->filled('to') || $snapshot['date'] <= $request->input('to'))
            );
        }
        if ($request->query('format') === 'csv') {
            return response()->streamDownload(function () use ($data) {
                $stream = fopen('php://output', 'w');
                fputcsv($stream, ['Metric', 'Value']);
                foreach (['Campaigns' => count($data['campaigns']), 'Leads' => count($data['leads']), 'Ad spend' => array_sum(array_column($data['campaigns'], 'spend')), 'Campaign revenue' => array_sum(array_column($data['campaigns'], 'revenue')), 'Invoice payments' => array_sum(array_column($data['invoices'], 'paid'))] as $metric => $value) {
                    fputcsv($stream, [$metric, $value]);
                }
                foreach ($data['marketing_metrics'] as $channel => $snapshots) {
                    foreach ($snapshots as $snapshot) {
                        foreach (self::METRICS[$channel] ?? [] as $key => $label) {
                            fputcsv($stream, [ucfirst($channel).' / '.$snapshot['date'].' / '.$label, $snapshot[$key]]);
                        }
                    }
                }
                fclose($stream);
            }, 'adxon-performance-report.csv', ['Content-Type' => 'text/csv']);
        }

        return view('admin.performance-report', ['data' => $data, 'settings' => $content->all()['settings']]);
    }

    private function recordActivity(array &$data, string $action, string $subject): void
    {
        $data['audit_logs'][] = ['date' => now()->format('d M Y h:i A'), 'user' => session('adxon_user.name', 'Admin'), 'action' => $action, 'subject' => $subject];
        $data['notifications'][] = ['date' => now()->format('d M Y h:i A'), 'message' => $action.': '.$subject];
    }

    public function invoicePreview(Request $request, AdxonContent $content, AdxonAdminData $adminData, string $invoice)
    {
        if ($redirect = $this->requirePermission($request, 'Invoices')) {
            return $redirect;
        }
        $record = collect($adminData->all()['invoices'])->firstWhere('id', $invoice);
        abort_unless($record, 404);

        return view('admin.invoice-preview', ['invoice' => $record, 'settings' => $content->all()['settings']]);
    }

    public function savePreferences(Request $request, AdxonAdminData $adminData)
    {
        if ($redirect = $this->requirePermission($request, 'Dashboard')) {
            return $redirect;
        }
        $payload = $request->validate(['display_name' => ['required', 'string', 'max:120'], 'notifications' => ['required', 'boolean']]);
        $payload['notifications'] = $request->boolean('notifications');
        $adminData->savePreferences((string) $request->session()->get('adxon_user.email', ''), $payload);
        $request->session()->put('adxon_user.name', $payload['display_name']);

        return back()->with('status', 'Account preferences saved.');
    }
}
