# Adxon Laravel Website and CMS

Adxon is now a Laravel project with a premium agency website and a lightweight CMS.

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

## CMS Sections

- Dashboard
- Services
- Packages
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

The previous plain PHP version was preserved here:

```text
legacy-plain-php/
```
