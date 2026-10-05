<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Support\AdxonAdminData;
use App\Support\AdxonContent;
use App\Support\TrackingSnippets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntegrationController extends AdminController
{
    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->session()->get('adxon_admin') === true && in_array('*', (array) $request->session()->get('adxon_user.permissions', []), true), 403);
    }

    public function index(Request $request, AdxonContent $content, AdxonAdminData $adminData)
    {
        $this->authorizeOwner($request);

        return $this->view('integrations', $content, $adminData, [
            'integrations' => Integration::where('account_id', config('integrations.account_id'))->get()->keyBy('integration_type'),
            'integrationEvents' => DB::table('integration_events')->where('account_id', config('integrations.account_id'))->latest('id')->limit(20)->get(),
        ]);
    }

    public function update(Request $request, string $type, TrackingSnippets $snippets)
    {
        $this->authorizeOwner($request);
        abort_unless(isset(TrackingSnippets::TYPES[$type]), 404);
        $payload = $request->validate([
            'head_code' => ['required', 'string', 'max:20000'],
            'body_code' => [$type === 'gtm' ? 'required' : 'nullable', 'string', 'max:10000'],
            'enabled' => ['required', 'boolean'],
        ]);
        $snippets->validate($type, $payload['head_code'], $payload['body_code'] ?? '');
        $this->mutate($request, $type, 'saved', function () use ($type, $payload) {
            $record = Integration::firstOrNew(['account_id' => config('integrations.account_id'), 'integration_type' => $type]);
            $record->created_by ??= (string) session('adxon_user.email', 'Owner');
            $record->fill(['head_code' => $payload['head_code'], 'body_code' => $payload['body_code'] ?? null, 'status' => $payload['enabled'] ? 'active' : 'inactive', 'updated_by' => (string) session('adxon_user.email', 'Owner')])->save();
        });

        return redirect()->route('admin.integrations')->with('status', TrackingSnippets::TYPES[$type].' saved.');
    }

    public function status(Request $request, string $type, TrackingSnippets $snippets)
    {
        $this->authorizeOwner($request);
        $payload = $request->validate(['enabled' => ['required', 'boolean']]);
        $this->mutate($request, $type, $payload['enabled'] ? 'enabled' : 'disabled', function () use ($type, $payload, $snippets) {
            $record = Integration::where('account_id', config('integrations.account_id'))->where('integration_type', $type)->lockForUpdate()->firstOrFail();
            if ($payload['enabled']) {
                $snippets->validate($type, $record->head_code, $record->body_code ?? '');
            }
            $record->update(['status' => $payload['enabled'] ? 'active' : 'inactive', 'updated_by' => (string) session('adxon_user.email', 'Owner')]);
        });

        return back()->with('status', 'Integration status updated.');
    }

    public function destroy(Request $request, string $type)
    {
        $this->authorizeOwner($request);
        abort_unless(isset(TrackingSnippets::TYPES[$type]), 404);
        $this->mutate($request, $type, 'removed', fn () => Integration::where('account_id', config('integrations.account_id'))->where('integration_type', $type)->delete());

        return back()->with('status', 'Integration removed.');
    }

    private function mutate(Request $request, string $type, string $action, callable $change): void
    {
        DB::transaction(function () use ($request, $type, $action, $change) {
            $change();
            DB::table('integration_events')->insert(['account_id' => config('integrations.account_id'), 'integration_type' => $type, 'action' => $action, 'actor' => (string) $request->session()->get('adxon_user.email', 'Owner'), 'created_at' => now()]);
        });
    }
}
