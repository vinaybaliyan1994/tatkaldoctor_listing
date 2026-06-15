# TatkalDoctor — Final UAT Checklist

Test environment: all 4 services running locally on ports 8000–8003.

---

## Pre-Flight

- [ ] All 4 services started (WampServer running, PHP servers on 8000/8001/8002/8003)
- [ ] `php artisan system:check` passes in all 4 projects
- [ ] `scripts\backup-all.ps1` completed successfully today
- [ ] n8n webhook reachable: `https://n8n.srv948607.hstgr.cloud/webhook/registration/send-otp`

---

## Step 1 — Doctor Registration (doctor-listing, port 8000)

1. Open `http://127.0.0.1:8000/admin/doctors/create`
2. Fill in: name, phone (valid Indian mobile), city, specialization
3. Submit form
4. **Expected:** OTP sent via n8n webhook (check n8n execution log — not Laravel log file)
5. Enter OTP to verify phone
6. **Expected:** Doctor record created with `verification_status = pending`

---

## Step 2 — Admin: Approve Doctor (doctor-listing)

1. Login as super_admin: `http://127.0.0.1:8000/admin/login`
2. Navigate to doctor list
3. Find the newly registered doctor
4. Click "Approve"
5. **Expected:** `verification_status` changes to `approved`
6. Doctor now appears in public listing

---

## Step 3 — QR Code Generation (doctor-listing)

1. Open doctor detail page: `http://127.0.0.1:8000/admin/doctors/{id}`
2. Click "Regenerate QR Code"
3. **Expected:** SVG QR code appears on page (no Imagick error, no broken image)
4. QR code file saved to `storage/app/public/qr-codes/`

---

## Step 4 — Document Upload (doctor-listing)

1. On doctor detail page, upload a document (PDF or image)
2. **Expected:** File saved to `storage/app/public/` and shown in document list
3. Admin can view/download the document

---

## Step 5 — Subscription Plans (solution, port 8001)

1. Login as super_admin: `http://127.0.0.1:8001/admin/login`
2. Navigate to Subscription Plans
3. **Expected:** At least 4 active plans visible
4. Verify plan names, prices, and features are correct

---

## Step 6 — Doctor Subscription (solution)

1. As a clinic/doctor, navigate to subscription page
2. Select a plan (Basic / Standard / Premium)
3. Click "Pay with Razorpay"
4. **Expected:** Razorpay checkout modal opens (test mode key `rzp_test_...`)
5. Use test card: `4111 1111 1111 1111`, CVV `123`, expiry any future date
6. Complete payment
7. **Expected:** Subscription activated, invoice generated

---

## Step 7 — Invoice PDF (solution)

1. After successful payment, navigate to invoice
2. Click "Download PDF"
3. **Expected:** PDF downloads with correct doctor name, plan, amount, GST breakdown
4. Invoice number is sequential

---

## Step 8 — Analytics Dashboard (solution)

1. Login as super_admin
2. Navigate to `http://127.0.0.1:8001/admin/dashboard`
3. **Expected:**
   - 14 KPI cards visible with real counts
   - 5 Chart.js charts render (no JS errors in browser console)
   - Filter bar works (select date range, plan, click Apply)
   - Revenue breakdown table shows plan-wise data
4. Click "Export Revenue CSV"
5. **Expected:** CSV file downloads with correct headers and data

---

## Step 9 — WhatsApp Booking Flow (whatsapp, port 8002)

Simulate a patient booking via WhatsApp:

1. Open `http://127.0.0.1:8002/whatsapp/conversations`
2. **Expected:** Existing conversation list visible
3. Trigger a new booking message (via `php artisan tinker` or webhook test):
   ```php
   // In tinker:
   app(\App\Services\WhatsAppStateMachineService::class)
       ->handle('919876543210', 'Hi');
   ```
4. **Expected:** State machine sends welcome + doctor list
5. Continue with "1" to select doctor
6. **Expected:** Available slots shown
7. Select slot, confirm booking
8. **Expected:** Appointment created in solution DB, confirmation sent

---

## Step 10 — Appointment Reminder (solution)

1. Create a test appointment scheduled 24 hours from now
2. Run scheduler manually:
   ```powershell
   cd "C:\wamp64\www\solution.tatkaldoctor.com"
   php artisan schedule:run
   ```
3. Or run command directly:
   ```powershell
   php artisan appointments:send-reminders
   ```
4. **Expected:** Reminder dispatched (check logs or n8n/WhatsApp execution)

---

## Step 11 — Public Website (port 8003)

1. Open `http://127.0.0.1:8003/`
2. **Expected:**
   - Home page renders correctly — NO raw Blade syntax visible (`@foreach`, `{{--`, etc.)
   - SEO meta tags present in page source (`<title>`, `<meta name="description">`)
   - Logo visible, navigation works
3. Click "Find Doctors" or search
4. **Expected:** Doctors fetched from doctor-listing API and displayed

---

## Step 12 — Public Website Sitemap

1. Open `http://127.0.0.1:8003/sitemap.xml`
2. **Expected:** Valid XML sitemap with URLs for home, doctor profiles

---

## Step 13 — HMAC API Authentication (doctor-listing)

1. Run the test script:
   ```powershell
   & "C:\wamp64\www\doctor_listing\test-hmac-apis.ps1"
   ```
2. **Expected:** All HMAC-signed API calls return 200, unauthenticated calls return 401/403

---

## Step 14 — Admin Audit Logs (doctor-listing)

1. Make a change to a doctor record (edit phone or name)
2. Navigate to `http://127.0.0.1:8000/admin/audit-logs`
3. **Expected:** Change recorded with actor, field, old value, new value, timestamp

---

## Step 15 — All system:check Commands Pass

```powershell
cd "C:\wamp64\www\doctor_listing" ; php artisan system:check
cd "C:\wamp64\www\solution.tatkaldoctor.com" ; php artisan system:check
cd "C:\wamp64\www\tatkaldoctor.whatsapp.com" ; php artisan system:check
cd "C:\wamp64\www\taktaldoctor.com" ; php artisan system:check
```

**Expected:** All output `All checks passed`.

---

## Sign-Off

| Tester | Date | All steps passed? | Notes |
|--------|------|-------------------|-------|
| | | | |
