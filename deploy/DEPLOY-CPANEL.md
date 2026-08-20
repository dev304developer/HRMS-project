# Deploying HRMS to cPanel Shared Hosting

A step-by-step guide to take this Laravel 12 app from local to your live cPanel server.

---

## 0. Prerequisites (check in cPanel first)

- **PHP 8.2 or higher** — set it in cPanel → **MultiPHP Manager** (select your domain → PHP 8.2/8.3).
- **Required PHP extensions** (cPanel → *Select PHP Version* → Extensions): `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `xml`, `zip`.
- **MySQL** database (cPanel → *MySQL Databases*).
- Ideally **SSH / Terminal access** + **Composer** (makes life much easier). If you don't have SSH, use the "No-SSH" notes below.

---

## 1. Prepare the upload (on your PC)

Your assets are already built (`public/build/`). Create a ZIP of the project **excluding** these (don't upload them):

```
/node_modules        (not needed in production)
/.git                (if present)
/.env                (never upload your local .env)
/deploy              (this folder — local-only helpers)
/storage/*.key, logs (logs will be regenerated)
```

**Do include `/vendor`** if your host has NO Composer (so you don't need to run `composer install`).
If your host HAS Composer/SSH, you can skip `/vendor` and run `composer install` on the server instead.

> Tip: easiest is to ZIP the whole `d:\HRMS_system` folder, then delete `node_modules` and `.env` from the ZIP, or zip everything except those.

---

## 2. Choose where Laravel's `public/` is served from

cPanel serves files from `public_html`, but Laravel's web root must be the **`public`** folder. Pick ONE:

### Option A — Subdomain/Addon domain (RECOMMENDED, cleanest)
1. Upload/extract the whole project to a folder like `/home/CPANELUSER/hrms`.
2. In cPanel → **Domains** (or *Subdomains*), point the domain's **Document Root** to `/home/CPANELUSER/hrms/public`.
3. Done — nothing else to move.

### Option B — Main domain, can't change document root (the "split" method)
1. Upload the project to `/home/CPANELUSER/hrms` (OUTSIDE public_html).
2. Move the **contents** of `hrms/public/*` into `public_html/`.
3. Edit `public_html/index.php` and fix the two require paths:
   ```php
   require __DIR__.'/../hrms/vendor/autoload.php';
   $app = require_once __DIR__.'/../hrms/bootstrap/app.php';
   ```
4. Make sure `public_html/.htaccess` (from Laravel's public folder) is in place.

---

## 3. Create the database (cPanel → MySQL Databases)

1. **Create a database** — note the full name (e.g. `cpaneluser_hrms`).
2. **Create a user** + password, then **Add user to database** with **ALL PRIVILEGES**.
3. Import the schema:
   - cPanel → **phpMyAdmin** → select the new database → **Import** → upload `deploy/hrms_schema.sql` → Go.
   - This creates all 14 tables. (It also marks migrations as run, so you don't need to migrate again.)

> **Alternative (if you have SSH):** skip the import and run `php artisan migrate --force` in step 5 instead — it builds the tables from scratch.

---

## 4. Configure `.env` on the server

1. Copy `deploy/.env.production` to the project root and rename it to **`.env`**.
2. Fill in the `>>> CHANGE <<<` values: `APP_URL`, and the `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` from step 3.
3. Generate the app key:
   - **With SSH:** `php artisan key:generate`
   - **No SSH:** run `php artisan key:generate --show` locally and paste the `base64:...` value into `APP_KEY=` (use a *fresh* key, not your local one).

---

## 5. Finalize on the server

**With SSH (preferred)** — from the project root:
```bash
composer install --no-dev --optimize-autoloader     # skip if you uploaded /vendor
php artisan migrate --force                          # skip if you imported the SQL in step 3
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**No SSH:**
- You uploaded `/vendor`, so Composer isn't needed.
- You imported the SQL, so migrations aren't needed.
- Skip the `config:cache`/`route:cache` commands (or run them later via a Terminal app). The app still works without them — caching just makes it faster.

---

## 6. Permissions

These two folders must be writable by the web server:
```
storage/            (and all subfolders)
bootstrap/cache/
```
In cPanel File Manager, set them to **755** (or 775 if 755 fails). Files inside `storage` can be 644.

---

## 7. Go live & verify

1. Visit `https://your-domain.com` → you should see the login page.
2. Enable **HTTPS**: cPanel → *SSL/TLS Status* → run **AutoSSL** (free Let's Encrypt), then force https (Laravel uses `APP_URL`).
3. Register/log in and click through the tabs.

---

## 8. IMPORTANT — production security cleanup

- **Remove the demo accounts** (`admin@hrms.test`, `hr@hrms.test`, `employee@hrms.test`) — they have the password `password`. Create your real admin instead (via the **Admin** page or Tinker), then delete the demos.
- Confirm `APP_DEBUG=false` (it is, in the template).
- The `deploy/` folder and `hrms_schema.sql` are local helpers — don't leave them publicly accessible on the server.
- Set a real `MAIL_*` SMTP later if you want password-reset / notification emails to actually send.

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| **500 error, blank page** | Check `storage/logs/laravel.log`. Usually permissions (step 6) or missing `APP_KEY` (step 4). |
| **"No application encryption key"** | `APP_KEY` is empty — set it (step 4). |
| **DB connection refused** | Wrong `DB_*` values, or `DB_HOST` should be `localhost` instead of `127.0.0.1` (try both). |
| **CSS/JS missing (unstyled)** | `public/build/` didn't upload, or `APP_URL` is wrong. Re-upload `public/build` and clear caches. |
| **Routes 404 except homepage** | `.htaccess` missing in the public root, or `mod_rewrite` off. Re-upload Laravel's `public/.htaccess`. |
| **Changed `.env` but no effect** | Run `php artisan config:clear` (cached config overrides `.env`). |
| **Timezone wrong** | `APP_TIMEZONE=Asia/Kolkata` is set; run `php artisan config:clear`. |

---

### Quick file checklist to upload
```
app/  bootstrap/  config/  database/  public/ (incl. build/)
resources/  routes/  storage/ (keep folder structure)  vendor/ (if no Composer)
artisan  composer.json  composer.lock  .env (your edited one)
```
Do NOT upload: `node_modules/`, your local `.env`, `deploy/`, `.git/`.
