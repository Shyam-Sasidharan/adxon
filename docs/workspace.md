# Adxon Workspace

The Laravel admin interface uses the existing JSON content and admin data stores. No database migration is required.

## Modules

- Overview: recorded leads, conversion, invoice payments, and active campaigns; existing sample website charts are explicitly labeled.
- Campaigns: create and edit campaigns, record metrics, change local status, and filter by platform, status, type, and date. Campaign status does not control a remote advertising account.
- Clients: contact details, team assignment, notes, linked campaigns, and matching invoice balances.
- Leads: search, source and status filters, date range, sorting, individual status changes, and confirmed bulk status updates.
- Analytics: existing sample website analytics plus manual SEO and social snapshots. Snapshots for the same channel and date replace that date's prior values.
- Reports: date-filtered report view, CSV export, and print styles for the browser's Save as PDF facility.
- Invoices: printable preview and an email compose link. The compose link does not send email automatically.
- Content, settings, team permissions, enquiries, and existing invoice creation remain available.
- Account preferences save a display name and notification visibility for the signed-in user.

Campaigns and Clients are independent permissions on the team creation form. Owners signed in through the configured owner credentials have access through their existing wildcard permission. Analytics, Reports, CMS, and invoice permissions continue to control their respective modules.

## Data Conventions

All campaign currency values use INR. CTR is clicks / impressions, CPC is spend / clicks, CPA is spend / conversions, and ROAS is attributed revenue / spend. Undefined ratios display a dash.

Campaign report date filters select campaign start dates and show those campaigns' recorded lifetime totals. Lead and invoice records are selected by their record dates. Reports exclude sample website analytics.

Live marketing-provider synchronization, API credentials, and automated ad delivery are not configured. The workspace makes this distinction visible instead of presenting manually recorded or sample metrics as live data.

## Assets and Verification

Chart.js 4.4.8 and Lucide 0.468.0 are served locally from `public/assets/vendor`, with their license files. `package-lock.json` pins the dependency tree.

Run `php artisan test` for rendering, permissions, campaign and client operations, snapshots, bulk lead validation, and report coverage. Browser visual verification must be performed separately when a browser connection is available.

## Public Website

The public website uses the same color and component system as the workspace. Its CMS content, consultation submission, package groups, and existing statistics are retained. Case-study and resource detail pages are available through `/insights/portfolio/{index}` and `/insights/blogs/{index}`.

Website settings include font family, a 12-24 px base size, normal/italic style, social links, and optional URLs for approved privacy and terms policies. Invalid fonts, sizes, and link schemes are rejected. Saving only typography preserves the other settings.

Additional CMS fields support service benefits, case-study challenges and strategies, article bodies, client logos, testimonial photos and ratings, and performance results. Logos, ratings, social/policy links, and results are shown when their content is supplied; no new client endorsements or performance claims are invented.

The legacy four-step process is expanded to six steps while retaining its existing descriptions. Custom process rows remain editable.

The bundled studio and project photos are illustrative Unsplash imagery, not verified photographs of the agency or its client work. Replace project images with actual work using the case-study `image_url` fields.

Photo sources:
- Studio: https://images.unsplash.com/photo-1497366811353-6870744d04b2
- Interiors: https://images.unsplash.com/photo-1600210492486-724fe5c67fb0
- Architecture: https://images.unsplash.com/photo-1600585154340-be6161a56a0c
- Food: https://images.unsplash.com/photo-1546069901-ba9599a7e63c
