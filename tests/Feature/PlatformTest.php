<?php

namespace Tests\Feature;

use App\Support\AdxonAdminData;
use Mockery;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    private function owner(): static
    {
        return $this->withSession(['adxon_admin' => true, 'adxon_user' => ['name' => 'Test Owner', 'permissions' => ['*']]]);
    }

    public function test_all_workspace_views_render(): void
    {
        foreach (['admin.dashboard', 'admin.leads', 'admin.leads.list', 'admin.invoices', 'admin.invoices.list', 'admin.users', 'admin.reports', 'admin.settings', 'admin.reports.performance', 'admin.platform.campaigns', 'admin.platform.clients', 'admin.platform.analytics', 'admin.platform.seo', 'admin.platform.social', 'admin.platform.ads', 'admin.platform.messages', 'admin.platform.preferences'] as $route) {
            $this->owner()->get(route($route))->assertOk();
        }
    }

    public function test_guest_cannot_access_campaigns_or_clients(): void
    {
        $this->get(route('admin.platform.campaigns'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.clients.save'), [])->assertRedirect(route('admin.login'));
    }

    public function test_populated_campaigns_clients_and_charts_render(): void
    {
        $data = (new AdxonAdminData)->all();
        $data['campaigns'] = [array_replace($this->campaign(), ['id' => 'campaign-test', 'client' => 'client-test', 'platform' => 'SEO', 'name' => "Client's <launch>"])];
        $data['clients'] = [['id' => 'client-test', 'name' => 'Jane', 'company' => 'Acme', 'email' => 'jane@example.test', 'phone' => '', 'industry' => '', 'team' => '', 'notes' => '', 'status' => 'Active', 'created_at' => '2026-09-14']];
        $data['marketing_metrics']['seo'] = ['2026-09-14' => ['date' => '2026-09-14', 'organic_traffic' => 100, 'ranked_keywords' => 20, 'backlinks' => 30, 'domain_authority' => 40, 'search_visibility' => 50, 'keywords_improved' => 5]];
        $service = Mockery::mock(AdxonAdminData::class)->makePartial();
        $service->shouldReceive('all')->andReturn($data);
        $this->app->instance(AdxonAdminData::class, $service);
        foreach (['admin.platform.campaigns', 'admin.platform.clients', 'admin.platform.seo'] as $route) {
            $this->owner()->get(route($route))->assertOk();
        }
    }

    public function test_client_can_be_saved(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->andReturn(['clients' => [], 'audit_logs' => [], 'notifications' => []]);
        $service->shouldReceive('save')->once()->withArgs(fn ($data) => $data['clients'][0]['company'] === 'Acme' && array_key_exists('notes', $data['clients'][0]));
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->post(route('admin.clients.save'), ['name' => 'Jane', 'company' => 'Acme', 'email' => 'jane@example.test', 'status' => 'Active'])->assertSessionHasNoErrors()->assertRedirect(route('admin.platform.clients'));
    }

    public function test_invalid_bulk_selection_does_not_save(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->andReturn(['leads' => [], 'lead_stages' => ['Qualified']]);
        $service->shouldNotReceive('save');
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->post(route('admin.leads.bulk'), ['ids' => ['missing'], 'stage' => 'Qualified'])->assertSessionHasErrors('ids.0');
    }

    public function test_campaign_permission_is_required(): void
    {
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['Dashboard']]])
            ->post(route('admin.campaigns.save'), [])->assertSessionHasErrors('access');
    }

    public function test_campaign_is_saved_with_activity_and_notification(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->once()->andReturn(['campaigns' => [], 'audit_logs' => [], 'notifications' => []]);
        $service->shouldReceive('save')->once()->withArgs(fn ($data) => count($data['campaigns']) === 1 && $data['campaigns'][0]['name'] === 'Launch' && count($data['audit_logs']) === 1 && count($data['notifications']) === 1);
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->post(route('admin.campaigns.save'), $this->campaign())->assertRedirect(route('admin.platform.campaigns'))->assertSessionHasNoErrors();
    }

    public function test_invalid_campaign_metrics_are_rejected(): void
    {
        $this->mock(AdxonAdminData::class)->shouldNotReceive('save');
        $this->owner()->post(route('admin.campaigns.save'), array_replace($this->campaign(), ['spend' => -1, 'clicks' => 300]))->assertSessionHasErrors(['spend', 'clicks']);
    }

    public function test_unknown_campaign_cannot_be_updated(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->once()->andReturn(['campaigns' => []]);
        $service->shouldNotReceive('save');
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->patch(route('admin.campaigns.status', 'missing'), ['status' => 'Paused'])->assertNotFound();
    }

    public function test_report_csv_contains_recorded_totals(): void
    {
        $response = $this->owner()->get(route('admin.reports.performance', ['format' => 'csv']));
        $response->assertOk()->assertDownload('adxon-performance-report.csv');
        $this->assertStringContainsString('Campaigns', $response->streamedContent());
    }

    private function campaign(): array
    {
        return ['name' => 'Launch', 'platform' => 'Meta Ads', 'type' => 'Lead Generation', 'status' => 'Draft', 'start_date' => '2026-09-14', 'budget' => 2000, 'spend' => 100, 'impressions' => 100, 'clicks' => 10, 'conversions' => 2, 'revenue' => 500];
    }

    public function test_report_accepts_an_end_date_without_a_start_date(): void
    {
        $this->owner()->get(route('admin.reports.performance', ['to' => '2026-09-14']))->assertOk();
    }

    public function test_account_preferences_are_saved_for_the_signed_in_user(): void
    {
        $this->mock(AdxonAdminData::class)->shouldReceive('savePreferences')->once()->with('owner@example.test', ['display_name' => 'New display name', 'notifications' => false]);
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['email' => 'owner@example.test', 'permissions' => ['Dashboard']]])
            ->post(route('admin.preferences.save'), ['display_name' => 'New display name', 'notifications' => '0'])
            ->assertSessionHasNoErrors()->assertSessionHas('adxon_user.name', 'New display name');
    }

    public function test_report_includes_only_snapshots_in_the_selected_period(): void
    {
        $data = (new AdxonAdminData)->all();
        $data['marketing_metrics']['social'] = [
            '2026-08-01' => ['date' => '2026-08-01', 'followers' => 10, 'reach' => 20, 'engagements' => 5, 'impressions' => 30, 'clicks' => 4, 'posts' => 2],
            '2026-09-01' => ['date' => '2026-09-01', 'followers' => 50, 'reach' => 100, 'engagements' => 20, 'impressions' => 150, 'clicks' => 12, 'posts' => 8],
        ];
        $service = Mockery::mock(AdxonAdminData::class)->makePartial();
        $service->shouldReceive('all')->andReturn($data);
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->get(route('admin.reports.performance', ['from' => '2026-09-01', 'to' => '2026-09-30']))
            ->assertOk()->assertSee('Social performance snapshots')->assertSee('2026-09-01')->assertDontSee('2026-08-01');
    }

    public function test_performance_snapshot_is_saved(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->andReturn(['marketing_metrics' => [], 'audit_logs' => [], 'notifications' => []]);
        $service->shouldReceive('save')->once()->withArgs(fn ($data) => $data['marketing_metrics']['social']['2026-09-14']['followers'] === 500);
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->post(route('admin.metrics.save', 'social'), ['date' => '2026-09-14', 'followers' => 500, 'reach' => 1000, 'engagements' => 100, 'impressions' => 1200, 'clicks' => 20, 'posts' => 5])->assertSessionHasNoErrors();
    }

    public function test_campaign_can_be_paused(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->andReturn(['campaigns' => [array_replace($this->campaign(), ['id' => 'test', 'status' => 'Active'])], 'audit_logs' => [], 'notifications' => []]);
        $service->shouldReceive('save')->once()->withArgs(fn ($data) => $data['campaigns'][0]['status'] === 'Paused');
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->patch(route('admin.campaigns.status', 'test'), ['status' => 'Paused'])->assertSessionHasNoErrors();
    }

    public function test_bulk_update_changes_only_selected_leads(): void
    {
        $service = Mockery::mock(AdxonAdminData::class);
        $service->shouldReceive('all')->andReturn(['leads' => [['id' => 'a', 'stage' => 'New Lead', 'activity' => []], ['id' => 'b', 'stage' => 'New Lead', 'activity' => []]], 'lead_stages' => ['New Lead', 'Qualified'], 'audit_logs' => [], 'notifications' => []]);
        $service->shouldReceive('save')->once()->withArgs(fn ($data) => $data['leads'][0]['stage'] === 'Qualified' && $data['leads'][1]['stage'] === 'New Lead');
        $this->app->instance(AdxonAdminData::class, $service);
        $this->owner()->post(route('admin.leads.bulk'), ['ids' => ['a'], 'stage' => 'Qualified'])->assertSessionHasNoErrors();
    }

    public function test_invoice_preview_renders(): void
    {
        $invoice = (new AdxonAdminData)->all()['invoices'][0] ?? null;
        if (! $invoice) {
            $this->markTestSkipped('No invoice fixture available.');
        }
        $this->owner()->get(route('admin.invoices.preview', $invoice['id']))->assertOk()->assertSee($invoice['invoice_no']);
    }
}
