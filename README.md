# Adxon Laravel Website and CMS

Adxon is now a Laravel project with a premium agency website, CMS, CRM-style lead center, invoice management, users/roles, and reports.

## Local URLs

With XAMPP Apache:

- Website: `http://localhost/Adxon/public/`
- CMS: `http://localhost/Adxon/public/admin`

With Laravel's local server:

```bash
php artisan serve
```

- Website: `http://127.0.0.1:8000/`
- CMS: `http://127.0.0.1:8000/admin`

## CMS Login

- Username: `admin`
- Password: `adxon@123`

You can override these with environment variables:

```env
ADXON_ADMIN_USER=admin
ADXON_ADMIN_PASSWORD=change-this-password
```

## Create User Login

1. Login to `admin`.
2. Open `Users and Roles`.
3. Fill `Name`, `Email`, `Password`, `Role`, `Status`, and permissions.
4. Click `Save User`.
5. The new user can login from the same CMS login page using their email and password.

User passwords are stored as encrypted hashes in `storage/app/adxon/admin.json`.

## Admin Sections

- Dashboard
- Lead Center
- Invoice Management
- Users and Roles
- Reports
- Services
- Package - Content Production
- Package - Social Media
- Package - Paid Ads
- Package - Combinations
- Portfolio
- Testimonials
- Blogs
- FAQ
- Enquiries
- Settings and SEO

## Content Storage

CMS content is stored here:

```text
storage/app/adxon/content.json
```

Admin panel data for analytics, leads, invoices, users, notifications, and audit logs is stored here:

```text
storage/app/adxon/admin.json
```

Enterprise admin architecture, user flows, schema, ER diagram, API documentation, and component notes are documented here:

```text
docs/admin-enterprise-blueprint.md
```

The previous plain PHP version was preserved here:

```text
legacy-plain-php/
```
