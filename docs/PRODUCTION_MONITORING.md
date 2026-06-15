# TatkalDoctor — Production Monitoring Guide

## Service Map

| Service | Port | URL |
|---------|------|-----|
| doctor-listing | 8000 | `http://your-server:8000` |
| solution | 8001 | `http://your-server:8001` |
| whatsapp | 8002 | `http://your-server:8002` |
| public-website | 8003 | `http://your-server:8003` |

---

## Health Check Endpoints

Test all 4 services are running:

```
GET /health
```

Expected response:
```json
{ "success": true, "service": "doctor-listing", "status": "running", "time": "...", "version": "production" }
```

Quick check all at once:
```powershell
foreach ($port in @(8000, 8001, 8002, 8003)) {
    try { $r = Invoke-RestMethod "http://127.0.0.1:$port/health" -TimeoutSec 3; "$port OK: $($r.service)" }
    catch { "$port FAIL" }
}
```

---

## Log File Locations

| Service | Log Path |
|---------|----------|
| doctor-listing | `C:\wamp64\www\doctor_listing\storage\logs\laravel.log` |
| solution | `C:\wamp64\www\solution.tatkaldoctor.com\storage\logs\laravel.log` |
| whatsapp | `C:\wamp64\www\tatkaldoctor.whatsapp.com\storage\logs\laravel.log` |
| public-website | `C:\wamp64\www\taktaldoctor.com\storage\logs\laravel.log` |

### View last 50 lines of any log

```powershell
Get-Content "C:\wamp64\www\doctor_listing\storage\logs\laravel.log" -Tail 50
```

### Search for errors

```powershell
Select-String -Path "C:\wamp64\www\*\storage\logs\laravel.log" -Pattern "ERROR|CRITICAL|Exception"
```

---

## Log Rotation

Laravel uses daily log rotation by default (`config/logging.php` — channel `daily`).

- Files named: `laravel-YYYY-MM-DD.log`
- Retention: controlled by `'days' => 14` in logging config
- Manual cleanup (keep last 30 days):

```powershell
Get-ChildItem "C:\wamp64\www\*\storage\logs\laravel-*.log" |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } |
    Remove-Item -Force
```

---

## Backup Schedule

Run `scripts\backup-all.ps1` daily. Recommended: Windows Task Scheduler at 02:00.

**Setup Task Scheduler (one-time):**

```powershell
$Action  = New-ScheduledTaskAction -Execute "powershell.exe" `
    -Argument "-NonInteractive -ExecutionPolicy Bypass -File C:\wamp64\www\doctor_listing\scripts\backup-all.ps1"
$Trigger = New-ScheduledTaskTrigger -Daily -At "02:00"
$Settings = New-ScheduledTaskSettingsSet -RunOnlyIfNetworkAvailable:$false
Register-ScheduledTask -TaskName "TatkalDoctor-DailyBackup" `
    -Action $Action -Trigger $Trigger -Settings $Settings -RunLevel Highest
```

**Backup retention** — keep last 14 days:

```powershell
Get-ChildItem "C:\tatkaldoctor_backups" -Directory |
    Where-Object { $_.Name -lt (Get-Date).AddDays(-14).ToString("yyyy-MM-dd") } |
    Remove-Item -Recurse -Force
```

---

## Queue Workers

The `solution` service uses a queue worker for appointment reminders.

**Start worker (development):**
```powershell
cd "C:\wamp64\www\solution.tatkaldoctor.com"
php artisan queue:work --sleep=3 --tries=3 --timeout=60
```

**Production (NSSM or Task Scheduler):**
```
Program: C:\wamp64\bin\php\php8.2.28\php.exe
Arguments: artisan queue:work --sleep=3 --tries=3 --timeout=60 --daemon
Working dir: C:\wamp64\www\solution.tatkaldoctor.com
```

**Check failed jobs:**
```powershell
cd "C:\wamp64\www\solution.tatkaldoctor.com"
php artisan queue:failed
```

---

## Scheduler (Cron)

The solution scheduler runs appointment reminders.

**Windows Task Scheduler entry (run every minute):**
```
Program: C:\wamp64\bin\php\php8.2.28\php.exe
Arguments: artisan schedule:run
Working dir: C:\wamp64\www\solution.tatkaldoctor.com
Trigger: Every 1 minute, indefinitely
```

**Manual run:**
```powershell
cd "C:\wamp64\www\solution.tatkaldoctor.com"
php artisan schedule:run
```

---

## Production Settings Checklist

Before go-live, verify each project:

| Setting | Required Value | Check Command |
|---------|----------------|---------------|
| `APP_ENV` | `production` | `php artisan env` |
| `APP_DEBUG` | `false` | `php artisan env` |
| Storage writable | Yes | `php artisan system:check` |
| Config cached | Yes | `php artisan config:cache` |
| Route cached | Yes | `php artisan route:cache` |
| View cached | Yes | `php artisan view:cache` |
| Queue running | Yes | see Queue Workers above |
| Scheduler running | Yes | see Scheduler above |
| DB migrations current | Yes | `php artisan migrate:status` |
| Storage link exists | Yes | `php artisan storage:link` |

**Apply all caches at once (run in each project dir):**
```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Clear all caches (use when deploying new code):**
```powershell
php artisan optimize:clear
```

---

## Key Config Values to Verify (production)

**doctor-listing `.env`:**
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.tatkaldoctor.com
WHATSAPP_BUSINESS_PHONE=<real number>
```

**solution `.env`:**
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://solution.tatkaldoctor.com
RAZORPAY_KEY=rzp_live_...
RAZORPAY_SECRET=...
OTP_PROVIDER=n8n
N8N_WEBHOOK_URL=https://n8n.srv948607.hstgr.cloud/webhook/registration/send-otp
WHATSAPP_DRY_RUN=false
```

**whatsapp `.env`:**
```
APP_ENV=production
APP_DEBUG=false
WHATSAPP_ACCESS_TOKEN=<live token>
WHATSAPP_PHONE_NUMBER_ID=<live phone id>
```

---

## Alerts / Error Monitoring

- Check logs daily for `ERROR` and `CRITICAL` entries
- Set up email alerts via Laravel `LOG_SLACK_WEBHOOK_URL` or a log aggregator
- Monitor disk usage: backups in `C:\tatkaldoctor_backups\` — clean up entries older than 14 days weekly
