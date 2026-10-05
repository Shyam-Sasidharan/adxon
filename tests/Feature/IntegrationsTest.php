<?php

namespace Tests\Feature;

use App\Http\Middleware\InjectTracking;
use App\Models\Integration;
use App\Support\TrackingSnippets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IntegrationsTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): static
    {
        return $this->withSession(['adxon_admin' => true, 'adxon_user' => ['email' => 'owner@test.test', 'permissions' => ['*']]]);
    }

    private function payload(string $type, string $id): array
    {
        $codes = app(TrackingSnippets::class)->codes($type, $id);

        return ['head_code' => $codes['head'].($type === 'meta_pixel' ? $codes['body'] : ''), 'body_code' => $type === 'gtm' ? $codes['body'] : '', 'enabled' => '1'];
    }

    public function test_all_providers_render_once_at_document_openings_and_admin_is_excluded(): void
    {
        foreach (['ga4' => 'G-ABC1234567', 'gtm' => 'GTM-ABC1234', 'meta_pixel' => '123456789012345'] as $type => $id) {
            $this->owner()->put(route('admin.integrations.update', $type), $this->payload($type, $id))->assertSessionHasNoErrors()->assertRedirect(route('admin.integrations'));
        }
        foreach (['/', '/insights/blogs/0'] as $url) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();
            $this->assertMatchesRegularExpression('/<head>\s*<!-- adxon-tracking:ga4 -->/', $html);
            $this->assertMatchesRegularExpression('/<body[^>]*>\s*<!-- adxon-tracking:gtm -->\s*<noscript>/', $html);
            foreach (['gtag/js?id=', 'gtm.js?id=', 'fbevents.js', 'ns.html?id='] as $needle) {
                $this->assertSame(1, substr_count($html, $needle));
            }
            $this->assertLessThan(strpos($html, '<meta'), strpos($html, 'fbevents.js'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->owner()->get(route('admin.integrations'))->assertOk()->assertSee('Connected')->assertDontSee('<!-- adxon-tracking:', false);
        $this->get(route('admin.login'))->assertDontSee('<!-- adxon-tracking:', false);
    }

    public function test_updates_disable_enable_and_removal_take_effect_immediately(): void
    {
        foreach (['ga4' => ['G-ABC1234567', 'G-XYZ1234567'], 'gtm' => ['GTM-ABC1234', 'GTM-XYZ1234'], 'meta_pixel' => ['123456789012345', '987654321098765']] as $type => [$old, $new]) {
            $this->owner()->put(route('admin.integrations.update', $type), $this->payload($type, $old))->assertSessionHasNoErrors();
            $this->get('/')->assertSee($old, false);
            $this->owner()->put(route('admin.integrations.update', $type), $this->payload($type, $new))->assertSessionHasNoErrors();
            $this->get('/')->assertSee($new, false)->assertDontSee($old, false);
            $this->owner()->patch(route('admin.integrations.status', $type), ['enabled' => 0])->assertSessionHasNoErrors();
            $this->get('/')->assertDontSee($new, false);
            $this->assertNotEmpty(Integration::where('integration_type', $type)->first()->head_code);
            $this->owner()->patch(route('admin.integrations.status', $type), ['enabled' => 1])->assertSessionHasNoErrors();
            $this->get('/')->assertSee($new, false);
            $this->owner()->delete(route('admin.integrations.destroy', $type))->assertRedirect();
            $this->get('/')->assertDontSee($new, false);
            $this->assertDatabaseMissing('integrations', ['integration_type' => $type]);
        }
        $this->assertDatabaseCount('integration_events', 15);
    }

    public function test_codes_are_encrypted_and_audit_contains_no_snippets(): void
    {
        $payload = $this->payload('ga4', 'G-ABC1234567');
        $this->owner()->put(route('admin.integrations.update', 'ga4'), $payload)->assertSessionHasNoErrors();
        $stored = DB::table('integrations')->first();
        $this->assertStringNotContainsString('G-ABC1234567', $stored->head_code);
        $this->assertSame($payload['head_code'], Integration::first()->head_code);
        $this->assertArrayNotHasKey('head_code', Integration::first()->toArray());
        $this->assertDatabaseHas('integration_events', ['actor' => 'owner@test.test', 'action' => 'saved']);
    }

    public function test_unauthorized_users_cannot_read_or_mutate(): void
    {
        foreach ([[], ['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS', 'Dashboard', 'Analytics', 'User Management']]]] as $session) {
            $this->withSession($session)->get(route('admin.integrations'))->assertForbidden();
            $this->put(route('admin.integrations.update', 'ga4'), $this->payload('ga4', 'G-ABC1234567'))->assertForbidden();
            $this->patch(route('admin.integrations.status', 'ga4'), ['enabled' => 1])->assertForbidden();
            $this->delete(route('admin.integrations.destroy', 'ga4'))->assertForbidden();
        }
    }

    public function test_mismatched_and_malicious_snippets_are_rejected(): void
    {
        $payload = $this->payload('gtm', 'GTM-ABC1234');
        $payload['body_code'] = str_replace('GTM-ABC1234', 'GTM-XYZ1234', $payload['body_code']);
        $this->owner()->put(route('admin.integrations.update', 'gtm'), $payload)->assertSessionHasErrors('body_code');
        foreach (['<script>alert(1)</script>', '<img src=x onerror=alert(1)>'] as $extra) {
            $payload = $this->payload('ga4', 'G-ABC1234567');
            $payload['head_code'] .= $extra;
            $this->owner()->put(route('admin.integrations.update', 'ga4'), $payload)->assertSessionHasErrors('head_code');
        }
        $this->assertDatabaseCount('integrations', 0);
    }

    public function test_account_scope_is_server_controlled_and_isolated(): void
    {
        config(['integrations.account_id' => 'site-a']);
        $this->owner()->put(route('admin.integrations.update', 'ga4'), $this->payload('ga4', 'G-AAAA123456') + ['account_id' => 'site-b'])->assertSessionHasNoErrors();
        config(['integrations.account_id' => 'site-b']);
        $this->get('/')->assertDontSee('G-AAAA123456', false);
        $this->owner()->get(route('admin.integrations'))->assertDontSee('G-AAAA123456', false);
        $this->owner()->patch(route('admin.integrations.status', 'ga4'), ['enabled' => 1])->assertNotFound();
        $this->owner()->put(route('admin.integrations.update', 'ga4'), $this->payload('ga4', 'G-BBBB123456'))->assertSessionHasNoErrors();
        $this->owner()->delete(route('admin.integrations.destroy', 'ga4'));
        config(['integrations.account_id' => 'site-a']);
        $this->get('/')->assertSee('G-AAAA123456', false)->assertDontSee('G-BBBB123456', false);
    }

    public function test_new_dynamic_routes_and_repeated_middleware_do_not_duplicate_tags(): void
    {
        $this->owner()->put(route('admin.integrations.update', 'ga4'), $this->payload('ga4', 'G-ABC1234567'));
        Route::middleware('web')->get('/dynamic-test', fn () => response('<!doctype html><html><head data-test="yes"></head><body class="example">Dynamic</body></html>'));
        $response = $this->get('/dynamic-test')->assertOk();
        $second = app(InjectTracking::class)->handle(Request::create('/dynamic-test'), fn () => $response->baseResponse);
        $this->assertSame(1, substr_count($second->getContent(), 'gtag/js?id='));
    }
}
