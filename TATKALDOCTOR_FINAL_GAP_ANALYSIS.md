# TATKALDOCTOR — FINAL GAP ANALYSIS
**Date:** 2026-06-10  
**Scope:** doctor_listing · solution.tatkaldoctor.com · tatkaldoctor.whatsapp.com  
**Method:** Complete static analysis of all three codebases  
**Decision:** WhatsApp is a premium TatkalDoctor feature, not a generic SaaS  
**Output:** What exists, what is missing, what must be built next, in what order

---

## TABLE OF CONTENTS

1. [Current Architecture State](#1-current-architecture-state)
2. [What Is Fully Built](#2-what-is-fully-built)
3. [Critical Gaps — Blocks Revenue or Business Flow](#3-critical-gaps--blocks-revenue-or-business-flow)
4. [High Gaps — Important Features Missing](#4-high-gaps--important-features-missing)
5. [Medium Gaps — Significant but Not Blocking](#5-medium-gaps--significant-but-not-blocking)
6. [Low Gaps — Polish and Compliance](#6-low-gaps--polish-and-compliance)
7. [Subscription Enforcement Map](#7-subscription-enforcement-map)
8. [Integration Gaps](#8-integration-gaps)
9. [Onboarding Flow Gap Analysis](#9-onboarding-flow-gap-analysis)
10. [Implementation Order](#10-implementation-order)
11. [Dependencies](#11-dependencies)
12. [Risks](#12-risks)

---

## 1. CURRENT ARCHITECTURE STATE

### Three codebases, three roles

```
doctor_listing              solution                    whatsapp-service
─────────────────          ──────────────────────       ─────────────────────
Master doctor registry     SaaS clinic management       WhatsApp booking bot
HMAC-protected API         Sanctum + ServiceToken       Meta Cloud API webhook
Listings, QR, documents    Clinic, patients, appts      Conversations, messages
Admin-panel managed        Doctor portal + REST API     State machine flow
```

### The intended business flow (as stated, vs as coded)

```
STATED FLOW                          CODED REALITY
─────────────────────────────────    ────────────────────────────────────────
Doctor visits taktaldoctor.com       No "Register as Doctor" link exists on
  → clicks "Solutions"               taktaldoctor.com. No route. No redirect.
  → redirects to solution            [GAP]

Doctor registers on solution         No public /register route in solution.
                                     DoctorOwnerController.store() is
                                     SUPER-ADMIN ONLY. [CRITICAL GAP]

Super Admin reviews listing          Admin reviews listing in doctor_listing.
  → approves                         Approval works. [BUILT]
  → listing goes live                Listing visible on taktaldoctor.com. [BUILT]

Doctor starts on Free Plan           No auto-assignment of Free Plan on
                                     clinic creation. [GAP]

Doctor upgrades to paid plan         No self-service upgrade. Super-admin
                                     manually assigns. No payment gateway.
                                     [CRITICAL GAP]

Professional plan unlocks WhatsApp   whatsapp_enabled flag exists in
  → doctor gets QR code              SubscriptionPlan but is NEVER CHECKED.
  → booking link                     Any clinic gets WhatsApp. [CRITICAL GAP]
  → conversation logs
```

---

## 2. WHAT IS FULLY BUILT

### doctor_listing — ~85% complete

| Module | Status | Notes |
|--------|--------|-------|
| HMAC-SHA256 API authentication | COMPLETE | Replay protection, encrypted secrets |
| Listing CRUD + verification workflow | COMPLETE | pending → approved → rejected |
| QR slug generation | COMPLETE | `{name-slug}-{first-8-uuid}` format |
| Document upload (multipart) | COMPLETE | PDF/image certificates |
| Profile photo upload | BUILT (migration not run) | `add_profile_photo_to_listings_table` pending |
| Listing audit trail (full JSON diff) | COMPLETE | Every field change logged |
| Geographic master data | COMPLETE | Countries, cities, locations |
| Medical master data | COMPLETE | Services, qualifications |
| API client credential management | COMPLETE | Admin creates HMAC clients |
| OpenAPI / Swagger docs | COMPLETE | `storage/api-docs/api-docs.json` |
| Listing search with filters | COMPLETE | country, city, location, service, text |

### solution.tatkaldoctor.com — ~75% complete

| Module | Status | Notes |
|--------|--------|-------|
| Doctor owner account management (admin) | COMPLETE | Super-admin creates accounts |
| Clinic creation + listing cache link | COMPLETE | Links to doctor_listing via listing_uuid |
| Doctor listing profile cache | COMPLETE | Synced from doctor_listing, manual refresh |
| Staff management | COMPLETE | Add/edit staff per clinic |
| Patient management | COMPLETE | CRUD, clinic-scoped |
| Appointment slots (recurring schedule) | COMPLETE | Day/time/capacity/duration |
| Appointments (book/confirm/complete/cancel/no-show) | COMPLETE | Full status machine |
| Clinic holidays | COMPLETE | Blocks slots on holiday dates |
| Patient records (medical notes) | COMPLETE | Per-appointment |
| Follow-up tracking | COMPLETE | Follow-up list view |
| Activity log | COMPLETE | All actions audited |
| In-app notifications | COMPLETE | Subscription events, reminders |
| Subscription plans CRUD | COMPLETE | Admin defines plan tiers |
| Subscription assignment (admin only) | COMPLETE | Manual, no payment |
| Orders + invoices | COMPLETE | Records exist, no payment processing |
| Reports (appointments, patients, revenue) | COMPLETE | UI + Excel exports |
| REST API v1 (Sanctum) | COMPLETE | Full CRUD for mobile/integration |
| Service account tokens (M2M) | COMPLETE | SHA-256 hashed, for WhatsApp/website |
| Public booking API | COMPLETE | WhatsApp + website can book |
| Listing cache sync endpoint | COMPLETE | But never called by doctor_listing |
| PlanLimitService (patients, appointments, records) | COMPLETE | Enforced on public booking |

### tatkaldoctor.whatsapp.com — ~70% complete

| Module | Status | Notes |
|--------|--------|-------|
| Meta Cloud API webhook (verify + receive) | COMPLETE | |
| Webhook parser (text, interactive, status) | COMPLETE | |
| Duplicate message guard | COMPLETE | wa_message_id dedup |
| Conversation state machine (9 states) | COMPLETE | Full booking flow |
| QR / slug shortcut entry (`qr:{slug}`) | COMPLETE | Bypasses search |
| Doctor search via WhatsApp list messages | COMPLETE | Up to 10 results |
| Slot availability lookup | COMPLETE | From solution public API |
| Appointment booking | COMPLETE | Via solution public API |
| Booking attempt logging | COMPLETE | Full request/response stored |
| 409 conflict handling (slot taken) | COMPLETE | Resets to date selection |
| Admin dashboard | COMPLETE | But NO AUTH PROTECTION |
| Conversation admin view | COMPLETE | But NO AUTH PROTECTION |

---

## 3. CRITICAL GAPS — BLOCKS REVENUE OR BUSINESS FLOW

### GAP-C1 — No doctor self-registration flow

**Severity: CRITICAL**  
**Affects:** Business onboarding, revenue  
**Where found:** `solution.tatkaldoctor.com/app/Http/Controllers/DoctorOwnerController.php`

`DoctorOwnerController.store()` is guarded by `Route::middleware('superadmin')`. There is no public `/register` route anywhere in solution.

```php
// routes/web.php — registration is superadmin-only
Route::middleware('superadmin')->group(function () {
    Route::get('/doctor-owners/create', ...)
    Route::post('/doctor-owners', ...)          // only superadmin can create doctors
```

Additionally, `taktaldoctor.com/routes/web.php` has NO route for "Solutions" or "register as doctor". The public website cannot send doctors to solution.

**What must be built:**
- Public `/register` route in solution (or a separate onboarding URL)
- Email verification after registration
- "Register Your Clinic" link/button on taktaldoctor.com

---

### GAP-C2 — `whatsapp_enabled` plan flag is never checked

**Severity: CRITICAL**  
**Affects:** Subscription revenue — WhatsApp is supposed to be a paid feature  
**Where found:** `SubscriptionPlan.php`, `PlanLimitService.php`, `WhatsAppStateMachineService.php`

`SubscriptionPlan` has `whatsapp_enabled` (boolean). But:

1. `PlanLimitService` has NO `canUseWhatsApp()` method:
```php
// PlanLimitService.php — missing method
// canCreateAppointment ✓
// canCreatePatient     ✓
// canCreateStaff       ✓ (though count is hardcoded 0)
// canUseWhatsApp       ✗ MISSING
```

2. `PublicClinicApiController.bookAppointment()` calls `planLimitService->canCreateAppointment()` but not a WhatsApp gate:
```php
// Line 174 — only checks appointment limit, not WhatsApp access
$limit = $this->planLimitService->canCreateAppointment($clinic->id);
```

3. The WhatsApp state machine never checks the clinic's plan before proceeding.

**Result:** Every clinic can receive WhatsApp bookings regardless of plan. A Free plan clinic gets WhatsApp.

**What must be built:**
- `PlanLimitService::canUseWhatsApp(int $clinicId): array`
- Gate check in `PublicClinicApiController.bookAppointment()` — if booked `via:whatsapp-service`, verify WhatsApp enabled
- Or: gate check in the WhatsApp service itself when it calls `getAvailableSlots` and `bookAppointment`

---

### GAP-C3 — Payment gateway not integrated

**Severity: CRITICAL**  
**Affects:** Revenue collection  
**Where found:** `SubscriptionController.php`, `OrderService.php`, `orders` and `invoices` tables

Subscriptions can only be activated by super-admin manually. There is no:
- Online payment for plan upgrades
- Self-service upgrade portal for doctors
- Razorpay / Stripe / PayU integration

The data models are fully ready (`orders`, `invoices`, `subscriptions` tables all exist). The infrastructure is built. Only the payment gateway integration is missing.

**What must be built:**
- Payment gateway integration (Razorpay recommended for India)
- Self-service plan selection UI for doctor portal
- Payment confirmation webhook → auto-activate subscription
- Invoice PDF generation

---

### GAP-C4 — Profile photo migration not run

**Severity: CRITICAL**  
**Affects:** Profile photo upload will throw SQL errors  
**Where found:** `doctor_listing/database/migrations/2026_06_09_000001_add_profile_photo_to_listings_table.php`

The migration file exists but has not been run. The `Listing` model already has `profile_photo_path` in `$fillable`. The `ListingDetailResource` already includes `profile_photo_url` in its response. Any attempt to upload or display a profile photo will fail.

**Action required (today):**
```bash
cd c:\wamp64\www\doctor_listing
php artisan migrate
```

---

### GAP-C5 — No automatic listing cache sync

**Severity: CRITICAL**  
**Affects:** Data integrity between doctor_listing and solution  
**Where found:** `solution.tatkaldoctor.com/app/Http/Controllers/Api/V1/PublicClinicApiController.php::syncListingCache()`

The `POST /api/v1/integration/listing-cache-sync` endpoint exists in solution and works correctly. But `doctor_listing` **never calls it**.

When a listing is approved, edited, or rejected in doctor_listing:
- solution's `doctor_listing_cache` is NOT updated
- Clinic dashboard shows stale doctor name, services, location
- Public clinic API returns stale data

Currently, the only way to sync is the doctor manually clicking "Refresh Profile" in the solution portal.

**What must be built:**
- In `doctor_listing`: an `AfterListingUpdated` observer or event listener
- After listing `verification_status` changes to `approved`, or any field updates:
  - Call solution's `integration/listing-cache-sync` endpoint via HMAC-signed HTTP call
  - Or queue a `SyncListingCacheJob`

---

## 4. HIGH GAPS — IMPORTANT FEATURES MISSING

### GAP-H1 — Free plan does not gate scheduling/appointments

**Severity: HIGH**  
**Where found:** `PlanLimitService.php`, `PublicClinicApiController.php`

The stated business rule is: Free plan = listing + search visibility only, no appointments, no slots. But:

```php
// PlanLimitService.php line 89-99
public function canCreateAppointment(int $clinicId): array
{
    $plan = $this->getActivePlan($clinicId);
    if (!$plan || $plan->max_appointments_per_month === null) {
        return ['allowed' => true, ...];  // ← NULL PLAN = ALLOWED
    }
```

A clinic with NO active subscription returns `allowed = true` for appointment creation. Free plan clinics (or clinics with no plan) can still receive appointments via the public API.

**What must be built:**
- `SubscriptionPlan` needs a `scheduling_enabled` boolean field (or use `max_appointments_per_month = 0` for Free plan)
- `PlanLimitService.canCreateAppointment()` must block clinics with no active plan or Free plan
- Slot availability endpoint must also check plan gating

---

### GAP-H2 — No booking confirmation to patient

**Severity: HIGH**  
**Affects:** Patient trust, operational reliability  

After a booking is created (via website or WhatsApp), the patient receives:
- **Website:** No confirmation page, no email, no SMS
- **WhatsApp:** A text message with the appointment number (this works)

The WhatsApp confirmation exists. The website confirmation is completely missing.

**What must be built:**
- Booking confirmation email from solution after `POST /public/appointments`
- Or: confirmation SMS via provider (Twilio, MSG91)
- Website should show a proper "Booking confirmed" page after `/_api/book` returns success

---

### GAP-H3 — WhatsApp appointment reminders not implemented

**Severity: HIGH**  
**Affects:** No-show reduction, patient experience  

`SubscriptionPlan` has `whatsapp_enabled` which implies reminders are a paid feature. But no reminder system exists anywhere.

**What must be built:**
- Laravel scheduled command (runs hourly/daily)
- Finds appointments 24h from now where patient has `whatsapp_no`
- Calls `WhatsAppMessageSender::sendText()` with reminder message
- Tracks sent reminders to avoid duplicates (new table or flag on appointment)
- Only runs for clinics whose plan has `whatsapp_enabled = true`

---

### GAP-H4 — WhatsApp cancellation flow missing

**Severity: HIGH**  
**Affects:** Patient experience post-booking  

Once a patient reaches `BOOKED` state, there is no way to cancel via WhatsApp. The `handlePostBooked()` method only tells them they already have a booking.

```php
// WhatsAppStateMachineService.php line 582-589
private function handlePostBooked(WhatsAppConversation $conversation): void
{
    $this->sender->sendText(
        $conversation,
        ($apptNo ? "Your appointment ($apptNo) is already confirmed. " : 'You already have a confirmed booking. ')
        . 'Type hi to book another appointment.',
    );
}
// ↑ No cancel option offered
```

**What must be built:**
- New state: `WAITING_FOR_CANCEL_CONFIRM`
- In `handlePostBooked()`: offer "Cancel Appointment" option if `appointment_uuid` is set
- Call `solution.tatkaldoctor.com/api/v1/public/appointments/{uuid}/cancel` (this endpoint doesn't exist yet on the public/service-token route — only Sanctum route)
- Add cancel endpoint to solution public API for service accounts

---

### GAP-H5 — Staff limit count is hardcoded to 0

**Severity: HIGH**  
**Where found:** `PlanLimitService.php` line 63

```php
public function canCreateStaff(int $clinicId): array
{
    $plan = $this->getActivePlan($clinicId);
    if (!$plan || $plan->max_staff === null) {
        return ['allowed' => true, 'current' => 0, 'max' => null];
    }
    $current = 0; // Staff table is Phase 2 — not yet created
```

The comment says "not yet created" but the staff table and Staff model **do exist**. This is a stale comment. The staff count never increments, so the plan limit for staff is never actually enforced.

**What must be built:**
- Replace hardcoded `0` with `Staff::where('clinic_id', $clinicId)->where('is_active', true)->count()`
- Call `canCreateStaff()` in `StaffController::store()`

---

### GAP-H6 — Subscription expiry not automatic

**Severity: HIGH**  
**Affects:** Subscription management accuracy  

`SubscriptionService::markExpired()` and `notifyExpiringSubscriptions()` exist but are never called. There is no scheduled artisan command.

**What must be built:**
- `app/Console/Commands/ExpireSubscriptions.php`
- Schedule in `routes/console.php` or `Kernel.php`: daily at midnight
- `notifyExpiringSubscriptions()` — 7 days before expiry
- `markExpired()` — on the day of expiry

---

### GAP-H7 — `reports_enabled` plan flag never checked

**Severity: HIGH**  
**Where found:** `SubscriptionPlan.php`, `routes/web.php`

`SubscriptionPlan` has `reports_enabled` boolean. The reports routes in solution (`/reports/*`) have NO subscription gate — any doctor can access reports regardless of plan.

**What must be built:**
- Middleware or gate check in ReportController that verifies `plan->reports_enabled`
- Or: add reports_enabled check in `CheckPermission` middleware

---

## 5. MEDIUM GAPS — SIGNIFICANT BUT NOT BLOCKING

### GAP-M1 — No doctor self-service onboarding wizard

After a doctor account is created (currently admin-only), there is no step-by-step onboarding:
1. Enter your doctor_listing UUID to link your profile
2. Verify clinic details
3. Set your availability (appointment slots)
4. Review subscription options

Currently a doctor logs in and sees a blank dashboard with no guidance.

---

### GAP-M2 — WhatsApp admin routes have no authentication

**Severity: HIGH-MEDIUM**  
**Where found:** `tatkaldoctor.whatsapp.com/routes/web.php`

```php
Route::get('/whatsapp/dashboard', ...)      // ← no auth middleware
Route::get('/whatsapp/conversations', ...)  // ← exposes all patient conversations
Route::get('/whatsapp/logs', ...)           // ← exposes all webhook payloads
```

Anyone who knows the URL can view all patient WhatsApp conversation history. This is a privacy risk.

**What must be built:**
- Basic auth middleware (or IP allowlist) on all admin routes
- At minimum: HTTP Basic Auth or a simple session-based login for the admin panel

---

### GAP-M3 — No "Solutions" / "Register Doctor" link on taktaldoctor.com

The business flow says doctors should discover solution.tatkaldoctor.com through the public website. But `taktaldoctor.com/routes/web.php` has no route for this. No footer link, no "Are you a doctor?" CTA.

**What must be built:**
- Link or button on taktaldoctor.com pointing to solution.tatkaldoctor.com/register
- Consider a dedicated landing page: `/for-doctors`

---

### GAP-M4 — No patient login on taktaldoctor.com

All bookings on the public website are anonymous. Patients cannot:
- See their booking history
- Cancel a booking
- Reschedule

**What must be built:**
- Patient authentication (phone OTP recommended)
- Booking history page
- Booking cancellation

---

### GAP-M5 — QR code generation not tied to subscription or explicit trigger

The Listing model has `qr_slug`, `qr_code_path`, `qr_generated_at` fields. But:
- There is no explicit QR code generation trigger visible in the codebase
- It is unclear when `qr_code_path` gets populated
- There is no subscription gate on QR access (Professional plan should enable QR)
- The public website does not display the QR code on doctor profile pages

**What must be built (clarification needed):**
- Confirm: is QR generated on listing approval, or on Professional plan activation?
- Add `canUseQRCode()` to PlanLimitService
- Display QR on doctor profile page on taktaldoctor.com

---

### GAP-M6 — `ai_enabled` plan flag has no implementation

`SubscriptionPlan.ai_enabled` exists. No AI feature is built anywhere. Placeholder for future work.

---

### GAP-M7 — Doctor cannot submit their own listing

Currently:
- Listings are created by super-admin in doctor_listing admin panel
- Doctors cannot create or edit their own listing from solution portal

**What must be built:**
- Doctor self-submission flow in solution portal
- Integrates with doctor_listing API to submit listing data
- Or: a form on doctor_listing that mirrors to solution account

---

### GAP-M8 — No prescription / medical record PDF export

`patient_records` table exists. Records can be created. But there is no PDF generation for prescriptions.

---

## 6. LOW GAPS — POLISH AND COMPLIANCE

| Gap | Priority | Description |
|-----|----------|-------------|
| WhatsApp STOP/opt-out handling | LOW | Meta Cloud API compliance — unsubscribe handling |
| Schema.org markup on taktaldoctor.com | LOW | `MedicalBusiness`/`Physician` for Google rich snippets |
| Doctor profile QR code display on website | LOW | Show QR image on `/doctor/{slug}` |
| Booking pagination on taktaldoctor.com search | LOW | All results returned at once |
| Multi-clinic support in solution | LOW | One clinic per doctor owner currently |
| `taktaldoctor.com` contact form sends email? | LOW | ContactController exists but email sending not verified |
| Invoice PDF generation | LOW | Invoice records exist, no PDF download |
| Health check `/health` endpoint on solution | LOW | Only whatsapp-service has health check |

---

## 7. SUBSCRIPTION ENFORCEMENT MAP

This table shows which subscription features are enforced in code vs stated in the plan.

| Plan Feature | Flag in DB | Gate in Code | Status |
|-------------|-----------|-------------|--------|
| Public Listing | (implicit - verified listing) | `scopePublic()` in Listing model | ENFORCED |
| Search Visibility | (implicit) | Same scope | ENFORCED |
| Scheduling (appointments) | `max_appointments_per_month` | `canCreateAppointment()` — BUT null plan = allowed | PARTIALLY |
| Max Patients | `max_patients` | `canCreatePatient()` | ENFORCED |
| Max Staff | `max_staff` | `canCreateStaff()` — hardcoded 0 | BROKEN |
| Max Medical Records | `max_medical_records_per_month` | `canCreateMedicalRecord()` | ENFORCED |
| WhatsApp Booking | `whatsapp_enabled` | **NOT CHECKED ANYWHERE** | MISSING |
| QR Code | (implicit - whatsapp_enabled?) | **NOT CHECKED ANYWHERE** | MISSING |
| Reports | `reports_enabled` | **NOT CHECKED ANYWHERE** | MISSING |
| AI Features | `ai_enabled` | No AI built | N/A |
| Auto-expire subscriptions | `end_date` | No scheduled job | MISSING |

---

## 8. INTEGRATION GAPS

### Gap I-1 — doctor_listing never pushes to solution

The `integration/listing-cache-sync` endpoint exists and works. But doctor_listing has no observer or webhook to call it.

**Trigger points that should sync:**
- Listing approved (`verification_status` → `approved`)
- Listing details updated (name, services, location, photo)
- Listing deactivated (`status` → false)
- Listing rejected (should mark clinic cache as stale)

### Gap I-2 — solution cannot notify doctor_listing of clinic activity

If a clinic is deactivated in solution, doctor_listing is not informed. The listing stays public.

**Missing:** Bidirectional sync signal between solution and doctor_listing.

### Gap I-3 — WhatsApp service doesn't check clinic subscription before booking

The WhatsApp flow calls `solutionApi.getAvailableSlots()` → `bookAppointment()` without knowing if the clinic's plan allows WhatsApp bookings.

**Missing:** Either:
- WhatsApp service calls solution to check `whatsapp_enabled` before showing slots
- Or: solution `GET /public/slots/available` returns 403 if plan doesn't have WhatsApp enabled

### Gap I-4 — No push after appointment creation (webhooks or queues)

When an appointment is booked (from website or WhatsApp):
- Doctor is not notified (no email, no SMS, no push)
- Patient is not notified on website (WhatsApp gets a confirmation message — this works)

---

## 9. ONBOARDING FLOW GAP ANALYSIS

### Current flow (reality)

```
1. Super Admin manually creates doctor_owner account in solution
2. Super Admin tells doctor their credentials out-of-band
3. Doctor logs in — sees empty dashboard
4. Doctor manually creates Clinic with their listing_uuid
5. Doctor must know their listing_uuid (not obvious how to find it)
6. Clinic created — no subscription assigned
7. Listing cache not synced until doctor clicks "Refresh Profile"
8. Super Admin manually assigns subscription plan
9. No payment collected
10. Doctor can now use paid features
```

### Ideal flow (what needs to be built)

```
1. Doctor visits taktaldoctor.com → clicks "Register Your Clinic" [MISSING]
2. Doctor fills registration form on solution → account created [MISSING]
3. Email verification link sent [MISSING]
4. After verification → onboarding wizard starts [MISSING]
   Step 1: Enter your doctor_listing UUID (or link auto-detected via email match)
   Step 2: Profile preview — confirm this is your listing
   Step 3: Clinic details (address, phone, logo)
   Step 4: Set your availability (appointment slots)
5. Account created, on Free Plan automatically [MISSING - no auto Free Plan]
6. Doctor sees dashboard with feature availability card:
   "Upgrade to Professional to unlock WhatsApp Booking"
7. Doctor clicks upgrade → payment gateway → plan activated [MISSING]
8. WhatsApp enabled → QR code generated → doctor can share it [MISSING gate]
```

**Gap summary:**
- Steps 1-5 entirely missing
- Step 6 partially (no plan-based feature visibility UI)
- Step 7 entirely missing (payment gateway)
- Step 8 partially (WhatsApp works but subscription gate missing)

---

## 10. IMPLEMENTATION ORDER

### Sprint 1 — Fix Critical Blockers (This Week)

| # | Task | Project | Complexity |
|---|------|---------|-----------|
| 1.1 | Run `php artisan migrate` for profile photo | doctor_listing | 1 minute |
| 1.2 | Fix staff count in PlanLimitService (replace `0` with real count) | solution | 30 minutes |
| 1.3 | Add auth middleware to WhatsApp admin routes | whatsapp | 1 hour |
| 1.4 | Add `canUseWhatsApp()` to PlanLimitService | solution | 2 hours |
| 1.5 | Gate WhatsApp bookings: check `whatsapp_enabled` in public booking endpoint | solution | 2 hours |

### Sprint 2 — Close Subscription Gaps (Week 2)

| # | Task | Project | Complexity |
|---|------|---------|-----------|
| 2.1 | Create subscription expiry scheduled command | solution | 3 hours |
| 2.2 | Free plan gate: `max_appointments_per_month = 0` blocks scheduling | solution | 2 hours |
| 2.3 | `reports_enabled` gate in ReportController | solution | 2 hours |
| 2.4 | Auto listing cache sync: observer in doctor_listing calls solution on approval/update | doctor_listing | 4 hours |
| 2.5 | WhatsApp appointment reminders: scheduled command + outbound message | whatsapp | 6 hours |

### Sprint 3 — Onboarding + Registration (Week 3-4)

| # | Task | Project | Complexity |
|---|------|---------|-----------|
| 3.1 | Public `/register` route in solution | solution | 1 day |
| 3.2 | Email verification after registration | solution | 4 hours |
| 3.3 | Free Plan auto-assignment on clinic creation | solution | 2 hours |
| 3.4 | "Register Your Clinic" link on taktaldoctor.com | taktaldoctor.com | 2 hours |
| 3.5 | Onboarding wizard (3 steps: link listing, confirm details, set slots) | solution | 3 days |

### Sprint 4 — Patient Experience (Week 5-6)

| # | Task | Project | Complexity |
|---|------|---------|-----------|
| 4.1 | Booking confirmation email after `POST /public/appointments` | solution | 4 hours |
| 4.2 | Booking confirmation page on taktaldoctor.com | taktaldoctor.com | 4 hours |
| 4.3 | WhatsApp cancellation flow (new state + solution cancel endpoint for service tokens) | whatsapp + solution | 1 day |
| 4.4 | Patient OTP login on taktaldoctor.com | taktaldoctor.com | 2 days |

### Sprint 5 — Payment Gateway (Week 7-8)

| # | Task | Project | Complexity |
|---|------|---------|-----------|
| 5.1 | Razorpay integration in solution | solution | 3 days |
| 5.2 | Self-service plan upgrade UI in doctor portal | solution | 2 days |
| 5.3 | Payment webhook → auto-activate subscription | solution | 1 day |
| 5.4 | Invoice PDF generation | solution | 1 day |

### Sprint 6 — Growth Features (Month 2)

| # | Task | Project | Complexity |
|---|------|---------|-----------|
| 6.1 | Doctor reviews and ratings on taktaldoctor.com | taktaldoctor.com | 3 days |
| 6.2 | Schema.org markup for doctor profiles | taktaldoctor.com | 1 day |
| 6.3 | Patient booking history (requires patient login) | taktaldoctor.com | 2 days |
| 6.4 | QR code display on doctor profile page | taktaldoctor.com | 4 hours |
| 6.5 | Prescription PDF generation | solution | 2 days |

---

## 11. DEPENDENCIES

```
Profile photo works
  └── requires: Run php artisan migrate [1.1]

WhatsApp subscription gate works
  └── requires: canUseWhatsApp() in PlanLimitService [1.4]
  └── requires: Gate check in public booking endpoint [1.5]

Free plan auto-assignment works
  └── requires: Public registration route [3.1]
  └── requires: Free Plan exists in subscription_plans table

Automatic cache sync works
  └── requires: doctor_listing observer [2.4]
  └── requires: solution HMAC client credentials for this call

WhatsApp reminders work
  └── requires: Scheduled command [2.5]
  └── requires: whatsapp_enabled gate [1.4 + 1.5]
  └── requires: Appointments table has patient whatsapp_no

Subscription expiry works
  └── requires: Scheduled command [2.1]
  └── requires: No payment gateway needed (can expire manually-assigned plans)

Self-service payment works
  └── requires: Payment gateway [Sprint 5]
  └── requires: Plan selection UI [Sprint 5]
  └── requires: Subscription activation on payment success

Patient booking history works
  └── requires: Patient OTP login [4.4]
  └── requires: Bookings linked to patient account

WhatsApp cancellation works
  └── requires: New public cancel endpoint in solution [4.3]
  └── requires: New state in WhatsApp state machine [4.3]
```

---

## 12. RISKS

| Risk | Severity | Probability | Mitigation |
|------|----------|-------------|-----------|
| Profile photo upload throws SQL error | HIGH | CERTAIN | Run migration immediately |
| WhatsApp bookings bypass subscription — revenue leak | HIGH | CERTAIN (already happening) | Sprint 1.4 + 1.5 |
| Patient data exposed via WhatsApp admin dashboard (no auth) | HIGH | HIGH | Sprint 1.3 |
| doctor_listing and solution show different doctor info indefinitely | MEDIUM | HIGH | Sprint 2.4 auto-sync |
| Subscriptions never expire — metrics inaccurate | MEDIUM | HIGH | Sprint 2.1 |
| Doctor has no way to register without calling super-admin | HIGH | CERTAIN | Sprint 3.1 |
| Free plan clinics can schedule appointments | MEDIUM | CERTAIN | Sprint 2.2 |
| Razorpay integration delayed — manual-only for too long | MEDIUM | MEDIUM | Accept manual for now; prioritize Sprint 5 |
| Meta Cloud API STOP non-compliance (WhatsApp opt-out) | LOW | LOW | Post Sprint 6 |

---

## APPENDIX — FILES ANALYZED

**doctor_listing (c:\wamp64\www\doctor_listing)**
- `app/Models/Listing.php`
- `app/Http/Controllers/Api/ListingController.php`
- `app/Http/Middleware/HmacAuthentication.php`
- `app/Http/Resources/ListingDetailResource.php`
- `app/Http/Resources/ListingSearchResource.php`
- `routes/api.php`, `routes/web.php`

**solution.tatkaldoctor.com (c:\wamp64\www\solution.tatkaldoctor.com)**
- `app/Models/{Clinic, Subscription, SubscriptionPlan, Appointment, DoctorListingCache}.php`
- `app/Services/{PlanLimitService, SubscriptionService, DoctorListingService}.php`
- `app/Http/Controllers/{DoctorOwnerController, SubscriptionController}.php`
- `app/Http/Controllers/Api/V1/PublicClinicApiController.php`
- `app/Http/Middleware/{EnsureApiClinicScope, ServiceTokenAuth}.php`
- `routes/api.php`, `routes/web.php`

**tatkaldoctor.whatsapp.com (c:\wamp64\www\tatkaldoctor.whatsapp.com)**
- `app/Services/{WhatsAppStateMachineService, SolutionApiService, DoctorListingApiService}.php`
- `app/Services/{WhatsAppMessageSender, WhatsAppConversationService, WhatsAppWebhookParser, WhatsAppWebhookLogger}.php`
- `app/Models/{WhatsAppConversation, WhatsAppBookingAttempt, WhatsAppMessage, WhatsAppWebhookLog}.php`
- `app/Http/Controllers/Webhook/WhatsAppWebhookController.php`
- `app/Enums/WhatsAppConversationState.php`
- All 11 migrations
- `routes/web.php`

---

*Gap analysis complete: 2026-06-10*  
*No code was modified during this analysis.*  
*Total: ~35 files analyzed across 3 codebases*
