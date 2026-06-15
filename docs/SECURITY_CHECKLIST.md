# TatkalDoctor — Security Checklist

Run this checklist before go-live and after any significant code change.

---

## 1. Environment & Secrets

- [ ] `APP_DEBUG=false` in all 4 `.env` files (production)
- [ ] `APP_ENV=production` in all 4 `.env` files
- [ ] `.env` files are NOT committed to git (verify: `git ls-files | grep "\.env$"` returns nothing)
- [ ] `.env.example` committed with placeholder values (no real keys)
- [ ] `APP_KEY` is unique and at least 32 characters in each project
- [ ] Razorpay key is `rzp_live_*` (not test) on production
- [ ] WhatsApp token is the production token (not test)
- [ ] n8n webhook URL uses HTTPS
- [ ] `N8N_VERIFY_SSL=true` on production (change from `false` used in dev)
- [ ] No hardcoded credentials in any PHP/JS source file

---

## 2. Authentication & Authorization

- [ ] Super admin login uses a strong password (not `admin`, `password`, `tatkaldoctor`)
- [ ] Session lifetime is set appropriately (`SESSION_LIFETIME` in `.env`)
- [ ] Admin routes require `session('role') === 'super_admin'` — verify no bypass exists
- [ ] WhatsApp admin routes protected by `admin.auth` middleware
- [ ] HMAC API clients table: each client has a unique, secret `api_secret`
- [ ] HMAC signatures validated with `hash_equals()` (timing-safe) — already implemented
- [ ] OTP codes expire after a short window (check `OtpSenderService` config)
- [ ] OTP codes are single-use (invalidated after verification)

---

## 3. Input Validation

- [ ] All form inputs validated with Laravel `$request->validate([...])` before use
- [ ] File uploads: type, size, and extension validated (no arbitrary file upload)
- [ ] Profile photos and documents only accept image/PDF MIME types
- [ ] Phone numbers validated as valid Indian mobile numbers before OTP send
- [ ] SQL queries use Eloquent / query builder parameter binding (no raw string concatenation)

---

## 4. CSRF Protection

- [ ] All POST/PUT/DELETE web forms include `@csrf` Blade directive
- [ ] API routes use HMAC authentication, not CSRF (correct — API routes are stateless)
- [ ] WhatsApp webhook endpoint excluded from CSRF only for Meta's POST calls (correct)

---

## 5. XSS Prevention

- [ ] All user-generated content rendered with `{{ }}` (escaped) not `{!! !!}` (raw)
- [ ] Admin-only fields using `{!! !!}` reviewed for necessity
- [ ] Content-Security-Policy header set (configure in `app/Http/Middleware/`)
- [ ] `X-Content-Type-Options: nosniff` header set

---

## 6. Storage & File Security

- [ ] `storage/app/private/` not publicly accessible (no symlink to it)
- [ ] Only `storage/app/public/` symlinked to `public/storage/`
- [ ] Uploaded files stored with randomized names (not original filename)
- [ ] `.env` not in `public/` or `storage/app/public/`
- [ ] `vendor/` not directly accessible (Laravel default `public/` root handles this)

---

## 7. HTTPS & Transport

- [ ] All 4 services served over HTTPS in production
- [ ] `APP_URL` uses `https://` in production `.env`
- [ ] `FORCE_HTTPS=true` or redirect configured at web server level
- [ ] `SESSION_SECURE_COOKIE=true` in production `.env`
- [ ] HSTS header set at web server (nginx/Apache) level

---

## 8. Rate Limiting

- [ ] Doctor registration endpoint rate-limited (prevent OTP spam)
- [ ] Admin login endpoint rate-limited (Laravel's `throttle` middleware)
- [ ] WhatsApp webhook not rate-limited too aggressively (Meta sends batches)
- [ ] API endpoints use HMAC (not rate-limited by IP alone)

---

## 9. Dependency Security

- [ ] Run `composer audit` in each project — no known vulnerabilities

  ```powershell
  foreach ($dir in @("doctor_listing", "solution.tatkaldoctor.com", "tatkaldoctor.whatsapp.com", "taktaldoctor.com")) {
      Write-Host "--- $dir ---"
      cd "C:\wamp64\www\$dir"
      composer audit
  }
  ```

- [ ] PHP version is 8.2+ (actively supported)
- [ ] Laravel version is 11+ or 12 (security fixes applied)
- [ ] No `composer.lock` conflicts or packages with known CVEs

---

## 10. Logging & Monitoring

- [ ] Error logs do NOT include sensitive data (passwords, tokens, OTPs)
- [ ] `APP_DEBUG=false` so stack traces not exposed in HTTP responses
- [ ] Log files not accessible via web (stored in `storage/logs/`, not `public/`)
- [ ] Audit log table records admin actions on doctor records
- [ ] Failed payment attempts logged (not card details — Razorpay handles that)

---

## Quick Security Scan Commands

```powershell
# Check for hardcoded secrets in PHP files
Select-String -Path "C:\wamp64\www\doctor_listing\app\**\*.php" -Pattern "password|secret|token|api_key" -CaseSensitive:$false

# Check .env not tracked in git
cd "C:\wamp64\www\doctor_listing"
git ls-files | Select-String "\.env$"

# Run composer audit (all projects)
foreach ($dir in @("doctor_listing","solution.tatkaldoctor.com","tatkaldoctor.whatsapp.com","taktaldoctor.com")) {
    cd "C:\wamp64\www\$dir"; composer audit 2>&1 | Select-String "Found|No security"
}
```
