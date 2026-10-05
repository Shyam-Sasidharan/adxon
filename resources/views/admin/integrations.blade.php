<div class="workspace-heading"><div><a class="text-link" href="{{ route('admin.platform.preferences') }}"><i data-lucide="arrow-left"></i>Account & Settings</a><h2>Integrations & API</h2><p>Connected means a valid configuration is saved and enabled. Provider receipt is not verified.</p></div></div>
@foreach (\App\Support\TrackingSnippets::TYPES as $type => $name)
    @php($integration = $integrations->get($type))
    <section class="data-section integration-section">
        <div class="panel-title"><h2>{{ $name }}</h2><span class="badge">{{ $integration?->status === 'active' ? 'Connected' : 'Not Connected' }}</span></div>
        <form method="post" action="{{ route('admin.integrations.update', $type) }}" class="form-grid">
            @csrf @method('put')
            <input type="hidden" name="editing_type" value="{{ $type }}">
            <label class="wide">{{ ['ga4' => 'GA4 Tracking Code', 'gtm' => 'GTM <head> Code', 'meta_pixel' => 'Meta Pixel Code'][$type] }}
                <textarea class="tracking-code" name="head_code" rows="9" required maxlength="20000" spellcheck="false" autocapitalize="off">{{ old('editing_type') === $type ? old('head_code') : $integration?->head_code }}</textarea>
            </label>
            @if ($type === 'gtm')<label class="wide">GTM &lt;body&gt; Code<textarea class="tracking-code" name="body_code" rows="5" required maxlength="10000" spellcheck="false">{{ old('editing_type') === $type ? old('body_code') : $integration?->body_code }}</textarea></label>@endif
            <input type="hidden" name="enabled" value="0">
            <label class="checkbox-label"><input type="checkbox" role="switch" name="enabled" value="1" @checked(old('editing_type') === $type ? old('enabled') : $integration?->status === 'active')>Enabled</label>
            <button class="button button-primary" type="submit"><i data-lucide="save"></i>Save</button>
        </form>
        @if ($integration)
            <div class="toolbar integration-actions">
                <form method="post" action="{{ route('admin.integrations.status', $type) }}">@csrf @method('patch')<input type="hidden" name="enabled" value="{{ $integration->status === 'active' ? '0' : '1' }}"><button class="button button-ghost" type="submit"><i data-lucide="power"></i>{{ $integration->status === 'active' ? 'Disable' : 'Enable' }}</button></form>
                <form method="post" action="{{ route('admin.integrations.destroy', $type) }}" data-confirm="Remove {{ $name }} and clear its saved code?">@csrf @method('delete')<button class="button button-ghost" type="submit"><i data-lucide="trash-2"></i>Remove / Clear</button></form>
            </div>
        @endif
    </section>
@endforeach
<section class="data-section"><h2>Integration activity</h2><div class="responsive-table"><table><thead><tr><th>Integration</th><th>Action</th><th>Administrator</th><th>Date</th></tr></thead><tbody>@forelse($integrationEvents as $event)<tr><td>{{ \App\Support\TrackingSnippets::TYPES[$event->integration_type] }}</td><td>{{ ucfirst($event->action) }}</td><td>{{ $event->actor }}</td><td>{{ $event->created_at }}</td></tr>@empty<tr><td colspan="4">No integration changes yet.</td></tr>@endforelse</tbody></table></div></section>
