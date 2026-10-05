# Tracking integrations

Owners (the existing wildcard permission) manage GA4, GTM and Meta Pixel in Account & Settings → Integrations & API. Other workspace roles cannot read or change these configurations. Public tracking IDs necessarily appear in public page source once activated; stored snippets are encrypted with Laravel's application key and omitted from model serialization.

Run `php artisan migrate` on deployment. Back up and retain `APP_KEY`, since encrypted snippets depend on it. One row per account/provider is enforced in the database. Changes and their actors are recorded transactionally in `integration_events`, displayed on the integrations page, without copying scripts into the audit trail.

This application currently has a single site, not a tenant resolver. `config/integrations.php` uses the server-controlled `ADXON_SITE_ACCOUNT_ID` (default `default`). Never derive this scope from an incoming account_id parameter or a public visitor's login session. A future multi-tenant installation must bind that config to its authenticated/domain-resolved tenant for each request; the storage queries are already account scoped.

Paste standard provider installation snippets, including optional HTML comments. Whitespace, HTML attribute order and JavaScript quote style are normalized for validation. Extra JavaScript, custom event calls, custom data layers, consent customizations, and arbitrary attributes are deliberately rejected. Validated provider templates are rendered instead of executing arbitrary administrator-supplied JavaScript. GA4 loader/config IDs must match; GTM head/body IDs must match. Meta's standard script appears in the head; its noscript image is placed in the body for valid HTML.

One web middleware injects the active integrations as the first block inside head and body on every successful public HTML document, including future dynamic web routes. Admin, JSON, redirects, errors, streams, and downloads are excluded. Multiple head integrations share the opening block in stable database-ID order; they cannot literally all be the first script simultaneously. Per-provider markers make the injection idempotent. Do not install the same GA4 or Meta tag again inside the remote GTM container: remote container contents cannot be deduplicated by this application.

There is no HTML/config cache in this implementation. Public HTML uses `Cache-Control: private, no-store`, and each response queries current account-scoped rows, so changes take effect on the next request. No broad cache flush is needed. Any external CDN configured to ignore origin cache headers must be purged/configured by its operator. Already-open documents cannot unload tracking scripts that ran before an administrator disabled them.

Connected means saved, validated and enabled, not externally verified. Confirm delivery with the providers' debugging tools after entering real IDs. No API tokens or marketing dashboard synchronization are added by these browser tracking integrations.

Provider references:
- https://developers.google.com/tag-platform/gtagjs
- https://developers.google.com/tag-platform/tag-manager/web
- https://developers.facebook.com/docs/meta-pixel/get-started/

Tests: `php artisan test --filter=IntegrationsTest` covers placements, repeated injection, lifecycle changes, encrypted storage, authorization, invalid snippets, dynamic routes and account scope.
