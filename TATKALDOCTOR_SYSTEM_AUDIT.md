# TATKALDOCTOR — MASTER SYSTEM AUDIT
**Date:** 2026-06-10  
**Auditor:** Claude Code (read-only analysis)  
**Scope:** All 4 projects at `c:\wamp64\www\`  
**Status:** READ-ONLY — No code was changed during this audit

---

## TABLE OF CONTENTS

1. [Executive Summary](#1-executive-summary)
2. [System Architecture Overview](#2-system-architecture-overview)
3. [Project 1 — doctor_listing](#3-project-1--doctor_listing)
4. [Project 2 — solution.tatkaldoctor.com](#4-project-2--solutiontatkaldoctorcom)
5. [Project 3 — tatkaldoctor.whatsapp.com](#5-project-3--tatkaldoctorwhatsappcom)
6. [Project 4 — taktaldoctor.com (public website)](#6-project-4--taktaldoctorcom-public-website)
7. [Integration Map](#7-integration-map)
8. [Ownership Map](#8-ownership-map)
9. [API Map — All Endpoints](#9-api-map--all-endpoints)
10. [Security Review](#10-security-review)
11. [Data Flow Diagrams](#11-data-flow-diagrams)
12. [Recommended Development Order](#12-recommended-development-order)
13. [What to Build Next](#13-what-to-build-next)
14. [What NOT to Rebuild](#14-what-not-to-rebuild)
15. [Missing Modules Across All Projects](#15-missing-modules-across-all-projects)
16. [Technical Debt & Risks](#16-technical-debt--risks)

---

## 1. EXECUTIVE SUMMARY

TatkalDoctor is a 4-project ecosystem for doctor discovery, clinic management, and appointment booking. All projects run on **Laravel 12 / PHP 8.2**.

| Project | Role | Auth | DB |
|---------|------|------|----|
| `doctor_listing` | Master doctor registry & API | HMAC-SHA256 | Yes — listings, clients, geo, docs |
| `solution.tatkaldoctor.com` | SaaS clinic management platform | Sanctum + Service Account Token | Yes — clinics, appointments, billing |
| `tatkaldoctor.whatsapp.com` | WhatsApp booking bot | Meta Cloud API webhook | Yes — conversations, booking attempts |
| `taktaldoctor.com` | Public SEO website + booking UI | None (public) | No — pure API consumer |

**Current completion estimate:**
- `doctor_listing` — ~85% (profile photo migration pending run)
- `solution` — ~80% (Phase 5 billing exists; payment gateway not integrated; no mobile API)
- `whatsapp` — ~75% (booking works; reminders, cancel/reschedule missing)
- `taktaldoctor.com` — ~60% (search + booking works; no patient portal, reviews, blog)

---

## 2. SYSTEM ARCHITECTURE OVERVIEW

```
┌─────────────────────────────────────────────────────────────────────┐
│                        TATKALDOCTOR ECOSYSTEM                       │
├──────────────────┬──────────────────┬─────────────────┬────────────┤
│  doctor_listing  │    solution       │ whatsapp-service │  website   │
│  (Registry API)  │  (SaaS Platform)  │  (Bot / Bridge)  │  (Public)  │
│                  │                   │                  │            │
│  Master source   │  Doctor's portal  │  WhatsApp bot    │  SEO pages │
│  of truth for    │  Clinic mgmt      │  State machine   │  /doctors  │
│  all doctor      │  Appointments     │  booking flow    │  /book     │
│  profiles        │  Billing          │                  │            │
│                  │                   │                  │            │
│  HMAC-protected  │  Sanctum API +    │  Calls both      │  Calls     │
│  REST API        │  Service Tokens   │  doctor_listing  │  both APIs │
│                  │  for M2M          │  and solution    │  via HTTP  │
└──────────────────┴──────────────────┴──────────────────┴────────────┘
         ▲                   ▲                  ▲               ▲
         │  HMAC calls       │  Service Token   │               │
         └───────────────────┴──────────────────┴───────────────┘
                All consumers call doctor_listing via HMAC
                WhatsApp + website call solution via service account token
```

**Core design principle:**
- `doctor_listing` is the SINGLE SOURCE OF TRUTH for doctor profiles.
- `solution` stores a local `doctor_listing_cache` for fast lookup (synced via integration endpoint).
- `whatsapp` and `taktaldoctor.com` are stateless — they call APIs and do not store doctor data.

---

## 3. PROJECT 1 — doctor_listing

**Path:** `c:\wamp64\www\doctor_listing`  
**Purpose:** Master registry. Every doctor listing in the system originates here.  
**Framework:** Laravel 12, PHP 8.2  
**Auth:** HMAC-SHA256 for all API routes; session-based for admin panel

### 3.1 Database Tables (Confirmed)

| Table | Purpose |
|-------|---------|
| `listings` | Core doctor/clinic profiles |
| `listing_documents` | Uploaded certificates, degrees |
| `listing_audit_logs` | Full change history for every listing |
| `clients` | API consumer credentials (api_key, secret_key) |
| `master_countries` | Country master data |
| `master_cities` | City master data |
| `master_locations` | Sub-city location data |
| `master_services` | Medical services/specialties |
| `master_qualifications` | Medical qualifications |
| `users` | Admin panel users (super_admin role) |
| `cache` / `jobs` | Laravel standard tables |

**Pending migration (NOT yet run):**  
`2026_06_09_000001_add_profile_photo_to_listings_table.php` — adds `profile_photo_path` column to `listings`

### 3.2 Listing Model — Key Fields

```
uuid (primary public identifier)
qr_slug (format: {name-slug}-{first-8-uuid-chars}, e.g. dr-ravi-kumar-d60fea83)
name, hospital_name, phone, email
country_code, master_city_id, master_location_id
qualification_id, service_ids (JSON array)
verification_status (unverified / verified / rejected)
is_active (boolean)
profile_photo_path (NEW — migration pending)
```

### 3.3 HMAC Authentication

**Headers required on every API request:**

| Header | Format | Description |
|--------|--------|-------------|
| `X-Api-Key` | 32-char hex | Client identifier |
| `X-Timestamp` | Unix epoch (string) | Request time (±300 seconds tolerance) |
| `X-Nonce` | 32-char hex | One-time value (cached to prevent replay) |
| `X-Signature` | 64-char hex | HMAC-SHA256 signature |

**String to sign:**
```
METHOD\n
TIMESTAMP\n
NONCE\n
/api/v1/path\n
SHA256(request_body_or_empty_string)
```

**Signature:** `lowercase(hash_hmac('sha256', stringToSign, secret_key))`

**Client storage:**  
- `api_key` = 32-char hex (plain)  
- `secret_key` = 64-char hex, **stored encrypted** via Laravel `Crypt::encrypt()`  
- Secret shown ONCE after creation via flash session — never stored plain

### 3.4 API Endpoints (HMAC protected, prefix: `/api/v1`)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/listings/search` | Search with filters (country_code, city, location, service, q) |
| GET | `/listings/{uuid}` | Get listing by UUID |
| GET | `/listings/slug/{qrSlug}` | Get listing by QR slug |
| GET | `/listings/{uuid}/documents` | Get listing documents |
| POST | `/listings/{uuid}/documents` | Upload document (multipart) |
| POST | `/listings/{uuid}/profile-photo` | Upload profile photo (multipart) |
| GET | `/countries` | All countries |
| GET | `/cities/{countryCode}` | Cities by country |
| GET | `/locations/{cityId}` | Locations by city |
| GET | `/services` | All medical services |
| GET | `/qualifications` | All qualifications |

### 3.5 Admin Panel (Web, session auth, `super_admin` middleware)

| Route | Purpose |
|-------|---------|
| `/listings` | CRUD + verification |
| `/listings/{listing}/audit-logs` | Per-listing audit history |
| `/listing-audit-logs` | Global audit log view |
| `/clients` | API client management (create/revoke HMAC credentials) |
| `/countries`, `/cities`, `/locations` | Geographic master data |
| `/services`, `/qualifications` | Medical master data |

### 3.6 Existing Modules (COMPLETE)

- [x] HMAC middleware with replay protection
- [x] Listing CRUD with verification workflow
- [x] Multi-document upload
- [x] Audit trail (full JSON diff of changes)
- [x] Geographic master data (country/city/location)
- [x] Medical master data (services/qualifications)
- [x] API client credential management
- [x] OpenAPI/Swagger docs (`storage/api-docs/api-docs.json`)

### 3.7 Missing / Incomplete Modules

| Module | Priority | Notes |
|--------|----------|-------|
| Profile photo migration (run `php artisan migrate`) | CRITICAL | Migration file exists, not run |
| Bulk listing import (CSV/Excel) | HIGH | Manual entry only now |
| Listing expiry / renewal system | MEDIUM | No TTL on listings |
| Doctor self-registration flow | MEDIUM | Admin-only currently |
| Search ranking / relevance scoring | MEDIUM | Basic WHERE search only |
| Rate limiting per API client | MEDIUM | Global throttle only |
| Webhook push to solution on listing change | LOW | Currently solution polls |
| API versioning (v2) | LOW | All on v1 |

---

## 4. PROJECT 2 — solution.tatkaldoctor.com

**Path:** `c:\wamp64\www\solution.tatkaldoctor.com`  
**Purpose:** SaaS clinic management platform. The operational backbone — doctors manage their clinic here.  
**Framework:** Laravel 12, PHP 8.2  
**Dependencies:** l5-swagger, Sanctum, Guzzle, maatwebsite/excel  
**Auth:** Session (web UI) + Sanctum Bearer Token (REST API) + Service Account Token (M2M)

### 4.1 Database Tables (14 migrations confirmed)

| Table | Phase | Purpose |
|-------|-------|---------|
| `users` | 1 | Doctor owners and admin |
| `clinics` | 1 | Clinic records (linked to doctor_listing via `listing_uuid`) |
| `doctor_listing_cache` | 1 | Local cache of listing profile from doctor_listing API |
| `settings` | 1 | Per-clinic key-value settings |
| `personal_access_tokens` | 1 | Sanctum tokens |
| `service_accounts` | 1 | Machine-to-machine auth tokens (for WhatsApp, website) |
| `staff` | 2 | Clinic staff (receptionist, nurse, etc.) |
| `patients` | 3 | Patient profiles |
| `appointment_slots` | 3 | Doctor availability slots (day/time/capacity) |
| `appointments` | 3 | Appointment bookings |
| `clinic_holidays` | 3 | Dates when clinic is closed |
| `patient_records` | 4 | Medical notes, prescriptions |
| `activity_logs` | 4 | Doctor portal activity trail |
| `notifications` | 4 | In-app notifications |
| `subscription_plans` | 5 | Plan definitions (free/basic/pro) |
| `subscriptions` | 5 | Doctor subscription status |
| `orders` | 5 | Payment orders |
| `invoices` | 5 | Billing invoices |

### 4.2 Clinic Model — Key Fields

```
uuid
owner_user_id (→ users)
listing_uuid (→ doctor_listing.listings.uuid — the bridge)
name, phone, address, city, country_code
logo_path
status (active/inactive)
subscription_id (→ subscriptions — active plan)
```

### 4.3 Authentication Layers

**Layer 1 — Web session (doctor portal UI)**
- Login via `/login` with email/password
- Session cookie with `auth_type = 'user'`
- Middleware: `auth.user`

**Layer 2 — Sanctum Bearer Token (REST API v1)**
- POST `/api/v1/login` returns token
- All protected routes require `Authorization: Bearer {token}`
- Middleware chain: `auth:sanctum` → `api.clinic` → `throttle`
- `api.clinic` middleware resolves the clinic from the authenticated user

**Layer 3 — Service Account Token (M2M)**
- Stored in `service_accounts` table
- Used by WhatsApp bot and public website to call public booking endpoints
- Middleware: `service.token`
- Routes: `/api/v1/public/*`, `/api/v1/integration/*`

### 4.4 REST API v1 Endpoints (`/api/v1`)

**Auth (no token):**
| Method | Path | Description |
|--------|------|-------------|
| POST | `/login` | Get Sanctum token |

**Protected (Sanctum token + clinic scope):**
| Method | Path | Description |
|--------|------|-------------|
| POST | `/logout` | Revoke token |
| GET | `/me` | Current user info |
| GET | `/dashboard` | Clinic stats summary |
| GET/POST | `/patients` | List / create patients |
| GET | `/patients/search` | Search patients by name/phone |
| GET/PUT | `/patients/{patient}` | Get / update patient |
| GET/POST | `/slots` | List / create appointment slots |
| GET | `/slots/available` | Available slots for a date |
| PUT/DELETE | `/slots/{slot}` | Update / delete slot |
| GET/POST | `/appointments` | List / create appointments |
| GET/PUT | `/appointments/{uuid}` | Get / update appointment |
| POST | `/appointments/{uuid}/confirm` | Confirm appointment |
| POST | `/appointments/{uuid}/complete` | Mark complete |
| POST | `/appointments/{uuid}/cancel` | Cancel appointment |
| POST | `/appointments/{uuid}/no-show` | Mark no-show |

**Public / M2M (Service Account Token):**
| Method | Path | Description |
|--------|------|-------------|
| GET | `/public/clinic` | Get clinic by listing_uuid / qr_slug / clinic_uuid |
| GET | `/public/slots/available` | Available slots (for WhatsApp/website) |
| POST | `/public/appointments` | Book appointment (no doctor login required) |
| POST | `/integration/listing-cache-sync` | Sync listing cache from doctor_listing |

### 4.5 Doctor Listing Integration (DoctorListingService)

solution.tatkaldoctor.com calls doctor_listing API using:
- Library: Guzzle HTTP
- Auth: HMAC-SHA256 (same algorithm as middleware)
- Config: `config/doctor_listing.php` → env vars `DOCTOR_LISTING_BASE_URL`, `DOCTOR_LISTING_API_KEY`, `DOCTOR_LISTING_API_SECRET`
- Operations: getListingByUuid, getListingBySlug, getCountries, getCities, getLocations, getServices, getQualifications, getDocuments, uploadDocument, uploadProfilePhoto

### 4.6 Existing Modules (COMPLETE)

- [x] Phase 1: Doctor registration, clinic creation, listing cache sync
- [x] Phase 2: Staff management (multi-role)
- [x] Phase 3: Patient management, appointment slots, appointments, clinic holidays
- [x] Phase 4: Patient records (medical notes), follow-ups, activity logs, notifications
- [x] Phase 5: Subscription plans, subscriptions, orders, invoices (manual activation)
- [x] Phase 7: Full REST API v1 with Sanctum + service account auth
- [x] Web UI: complete portal with all above features
- [x] Excel exports for all major entities
- [x] Reports: appointments, patients, staff, revenue, subscriptions

### 4.7 Missing / Incomplete Modules

| Module | Priority | Notes |
|--------|----------|-------|
| Payment gateway (Razorpay/Stripe) | HIGH | Orders/invoices exist but no payment processing |
| SMS/WhatsApp appointment reminders (outbound) | HIGH | Notifications table exists, no sending |
| Doctor mobile app API | HIGH | REST API exists but no mobile-specific endpoints |
| Online payment for patients | HIGH | Website booking has no payment step |
| Prescription module | MEDIUM | patient_records exists but no PDF generation |
| Video consultation | MEDIUM | No telemedicine support |
| Referral system | LOW | No patient referral tracking |
| Multi-clinic support per doctor | LOW | One clinic per owner currently |
| Doctor availability calendar UI | MEDIUM | Slots exist but no visual calendar |

---

## 5. PROJECT 3 — tatkaldoctor.whatsapp.com

**Path:** `c:\wamp64\www\tatkaldoctor.whatsapp.com`  
**Purpose:** WhatsApp booking bot. Patients book appointments by chatting on WhatsApp.  
**Framework:** Laravel 12, PHP 8.2  
**External API:** Meta Cloud API (WhatsApp Business)

### 5.1 Database Tables (11 migrations confirmed)

| Table | Purpose |
|-------|---------|
| `logs` | General application logs |
| `whatsapp_webhook_logs` | Raw incoming webhook payloads |
| `whatsapp_conversations` | Per-user conversation state + stored booking data |
| `whatsapp_messages` | Individual message records (in/out) |
| `whatsapp_booking_attempts` | All booking attempts with request/response payload |
| `clients` | HMAC client credentials (for doctor_listing API) |

### 5.2 Conversation State Machine

The state machine is the core of this project. States in order:

```
START
  └─► MAIN_MENU_SENT          (welcome menu: "Book Appointment" / "Help")
       └─► WAITING_FOR_SEARCH_TYPE   (search by: Name / Location / Service)
            └─► WAITING_FOR_SEARCH_QUERY  (type search text)
                 └─► WAITING_FOR_DOCTOR_SELECTION  (pick from list of up to 10)
                      └─► DOCTOR_SELECTED
                           └─► WAITING_FOR_DATE   (enter YYYY-MM-DD)
                                └─► WAITING_FOR_SLOT_SELECTION  (pick time slot)
                                     └─► WAITING_FOR_PATIENT_NAME
                                          └─► WAITING_FOR_PATIENT_PHONE
                                               └─► WAITING_FOR_CONFIRMATION
                                                    ├─► BOOKED  (success)
                                                    └─► FAILED  (error)
```

**Special shortcuts (work from any state):**
- Any greeting ("hi", "hello", "hey", "start", "menu") → reset to welcome
- `qr:{slug}` or `slug:{slug}` → jump directly to DOCTOR_SELECTED state

**Error recovery:**
- 409 Conflict on booking → back to WAITING_FOR_DATE (slot taken, choose again)
- Any other booking error → FAILED state
- FAILED/HELP states → next message resets to welcome

### 5.3 Conversation Model — Stored Fields

```
wa_id (WhatsApp phone number — primary identifier)
current_state (enum value)
listing_uuid (selected doctor)
qr_slug (doctor's QR slug)
clinic_uuid (resolved from solution API)
doctor_name
selected_date
selected_slot_time
patient_name
patient_phone
appointment_uuid (after successful booking)
appointment_no (human-readable booking reference)
```

### 5.4 External API Calls

**To doctor_listing (HMAC):**
- `searchListings(filters)` — doctor search
- `getListingByUuid(uuid)` — fetch doctor profile after selection
- `getListingBySlug(slug)` — QR shortcut lookup

**To solution (Service Account Token):**
- `getAvailableSlots(identifiers, date)` → calls `/api/v1/public/slots/available`
- `bookAppointment(payload)` → calls `/api/v1/public/appointments`

### 5.5 Webhook Architecture

```
Meta Cloud API
    │
    ├─► GET /webhook?hub.challenge=... (verification)
    └─► POST /webhook (incoming message)
              │
              └─► WebhookController::receive()
                       │
                       ├─► Log raw payload to whatsapp_webhook_logs
                       ├─► Parse message type (text / interactive / status)
                       ├─► Find or create WhatsAppConversation
                       └─► WhatsAppStateMachineService::handle()
                                │
                                ├─► Calls DoctorListingApiService (HMAC)
                                ├─► Calls SolutionApiService (service token)
                                └─► Sends replies via WhatsAppMessageSender
```

### 5.6 Existing Modules (COMPLETE)

- [x] Meta Cloud API webhook receiver
- [x] Full booking state machine (9 states)
- [x] Doctor search via WhatsApp list messages
- [x] QR code shortcut entry (scan QR → WhatsApp → book)
- [x] Slot availability lookup from solution
- [x] Appointment booking via solution
- [x] Booking attempt logging with full request/response
- [x] Conversation persistence across multiple WhatsApp sessions
- [x] Admin dashboard for conversations/logs
- [x] HMAC client for doctor_listing
- [x] Service account client for solution

### 5.7 Missing / Incomplete Modules

| Module | Priority | Notes |
|--------|----------|-------|
| Appointment reminder (outbound message 24h before) | HIGH | No scheduled sending |
| Booking cancellation flow | HIGH | No cancel via WhatsApp |
| Reschedule flow | MEDIUM | No state machine path for reschedule |
| Patient booking history (show past bookings) | MEDIUM | Conversations reset after BOOKED |
| Language support (Hindi/regional) | MEDIUM | English only currently |
| Media message handling (images, documents) | LOW | Ignored currently |
| Doctor-facing bot (appointment confirmations) | LOW | Patient-only now |
| Rate limiting per wa_id | MEDIUM | Spam protection missing |
| Opt-out / block list management | MEDIUM | No STOP handling |

---

## 6. PROJECT 4 — taktaldoctor.com (public website)

**Path:** `c:\wamp64\www\taktaldoctor.com`  
**Purpose:** SEO-optimized public website. Patients search for doctors and book appointments.  
**Framework:** Laravel 12, PHP 8.2  
**Frontend:** Blade + Alpine.js  
**Auth:** None (fully public)  
**Database:** None (pure API consumer — no local tables)

### 6.1 Key Architecture Decisions

- **No local DB:** Doctor data comes 100% from doctor_listing API via HMAC.
- **Booking goes through solution:** `/book/{slug}` fetches slots from and posts booking to solution public API.
- **Alpine.js for reactivity:** City/location dropdowns, slot picker, booking form — all via internal `/_api/*` endpoints.
- **Caching:** Doctor search results NOT cached. Countries/services cached 3600s in Laravel's cache driver.

### 6.2 Routes

| Route | Controller | Description |
|-------|-----------|-------------|
| `/` | HomeController@index | Homepage |
| `/doctors` | DoctorController@index | Doctor search with filters |
| `/doctor/{slug}` | DoctorController@profile | Doctor profile page |
| `/book/{slug}` | BookingController@show | Booking page |
| `/book-appointment/{slug}` | BookingController@show | SEO alias for booking |
| `/doctors-in-{segment}` | DoctorController@seoSearch | City-based SEO page |
| `/{specialty}-in-{city}` | DoctorController@seoSearch | Specialty+city SEO page |
| `/_api/cities` | ApiController@cities | Alpine.js dropdown |
| `/_api/locations` | ApiController@locations | Alpine.js dropdown |
| `/_api/services` | ApiController@services | Alpine.js dropdown |
| `/_api/slots` | BookingController@slots | Available time slots |
| `/_api/book` | BookingController@book | Submit booking |
| `/about-us`, `/terms-and-conditions`, `/privacy-policy` | PageController | Static pages |
| `/contact-us` | ContactController | Contact form |

### 6.3 SEO URL Structure

**Pattern 1:** `/doctors-in-{location}`
- Examples: `/doctors-in-delhi`, `/doctors-in-shahdara-delhi`
- Parsed as: city=delhi OR location=shahdara, city=delhi

**Pattern 2:** `/{specialty}-in-{location}`
- Examples: `/ent-specialist-in-delhi`, `/cardiologist-in-south-delhi-delhi`
- Regex guard: must contain `-in-` (prevents matching `/about-us`)
- Specialty is resolved by fuzzy-matching service names from API

**SEO Meta Generation:**
- Dynamic title: `{Specialty} in {Location} | TatkalDoctor`
- Dynamic description with service + location
- Canonical URL set per page

### 6.4 Booking Flow (Website)

```
Patient visits /book/{listing_uuid} or /book/{qr_slug}
  │
  ├─► BookingController@show
  │     └─► Calls doctor_listing API → gets doctor profile
  │
  ├─► Patient selects date
  │     └─► Alpine.js calls /_api/slots?slug={uuid}&date={date}
  │               └─► BookingController@slots
  │                       └─► Calls solution /api/v1/public/slots/available
  │
  └─► Patient fills form + submits
        └─► Alpine.js POSTs to /_api/book
                  └─► BookingController@book
                          └─► Calls solution /api/v1/public/appointments
```

### 6.5 Existing Modules (COMPLETE)

- [x] Doctor search with multi-filter (country, city, location, service, keyword)
- [x] Doctor profile page
- [x] Online booking (date → slot → patient details → confirm)
- [x] SEO URL generation for city and specialty pages
- [x] Dynamic meta tags per page
- [x] Alpine.js reactive dropdowns
- [x] Contact form
- [x] Static pages (about, terms, privacy)

### 6.6 Missing / Incomplete Modules

| Module | Priority | Notes |
|--------|----------|-------|
| Patient login / registration | HIGH | No account — bookings are anonymous |
| Booking confirmation page / email | HIGH | No confirmation sent after booking |
| Booking history for returning patients | HIGH | No way to check past bookings |
| Doctor reviews and ratings | HIGH | No review system |
| Online payment before booking | MEDIUM | Booking is free — no payment |
| Blog / health articles (SEO content) | MEDIUM | Only doctor pages indexed |
| Doctor profile share (QR code display) | MEDIUM | QR code not shown on profile |
| Geo-location based search ("near me") | MEDIUM | Manual city selection only |
| Pagination on search results | MEDIUM | All results returned at once |
| Booking cancellation by patient | MEDIUM | No cancel option after booking |
| Push notifications (appointment reminder) | LOW | No notification system |
| Schema.org markup for doctors | LOW | No structured data for rich snippets |

---

## 7. INTEGRATION MAP

```
╔══════════════════╗
║  doctor_listing  ║
║  (Master API)    ║
╚══════════════════╝
        ▲ HMAC calls from all 3 consumers
        │
        ├──── solution.tatkaldoctor.com ─────────────────────────────────┐
        │     reads: getListingByUuid/Slug, getServices, getCities, etc. │
        │     writes: uploadDocument, uploadProfilePhoto                 │
        │                                                                │
        ├──── tatkaldoctor.whatsapp.com                                  │
        │     reads: searchListings, getListingByUuid, getListingBySlug  │
        │                                                                │
        └──── taktaldoctor.com                                           │
              reads: searchDoctors, getDoctorByUuid/Slug, getCities, etc │
                                                                         │
╔══════════════════════════════════╗                                     │
║  solution.tatkaldoctor.com       ◄─────────────────────────────────────┘
║  (SaaS Platform)                 ║
║  Service Account Token for M2M   ║
╚══════════════════════════════════╝
        ▲ Service Account Token calls from 2 consumers
        │
        ├──── tatkaldoctor.whatsapp.com
        │     reads: getAvailableSlots
        │     writes: bookAppointment
        │
        └──── taktaldoctor.com
              reads: getAvailableSlots
              writes: bookAppointment

╔══════════════════════════════════╗
║  Meta Cloud API (WhatsApp)       ║
╚══════════════════════════════════╝
        │ Webhooks (GET verify + POST receive)
        ▼
  tatkaldoctor.whatsapp.com
```

### Integration Points Summary

| From | To | Protocol | What |
|------|----|----------|------|
| solution | doctor_listing | HMAC HTTP (Guzzle) | Read profiles, upload docs/photos |
| whatsapp | doctor_listing | HMAC HTTP (Laravel Http) | Search, get profile by UUID/slug |
| taktaldoctor.com | doctor_listing | HMAC HTTP (Laravel Http) | Search, get profile by UUID/slug |
| whatsapp | solution | Service Account Bearer | Get slots, book appointment |
| taktaldoctor.com | solution | Service Account Bearer | Get slots, book appointment |
| Meta Cloud API | whatsapp | HTTPS Webhook | Incoming WhatsApp messages |
| whatsapp | Meta Cloud API | HTTPS REST | Send replies to patients |
| doctor_listing | solution | — (NOT IMPLEMENTED) | Listing cache sync (one-way) |

**Note:** The `integration/listing-cache-sync` endpoint exists in solution, but doctor_listing does NOT automatically push updates to solution. Solution refreshes cache manually via "Refresh Profile" button or the sync endpoint must be called externally.

---

## 8. OWNERSHIP MAP

### Who Owns What Data

| Data | Authoritative Source | Cached In | Read By |
|------|---------------------|-----------|---------|
| Doctor profile (name, specialty, etc.) | `doctor_listing.listings` | `solution.doctor_listing_cache` | All 4 projects |
| Doctor documents | `doctor_listing.listing_documents` | — | solution (upload + read) |
| Geographic data (countries/cities/locations) | `doctor_listing.master_*` | Laravel cache (all 3 consumers) | All consumers |
| Medical services / qualifications | `doctor_listing.master_services` | Laravel cache (consumers) | All consumers |
| Appointment slots | `solution.appointment_slots` | — | whatsapp, website |
| Appointments | `solution.appointments` | — | whatsapp (booking attempt log) |
| Clinic holidays | `solution.clinic_holidays` | — | solution internally |
| Patients | `solution.patients` | — | solution, whatsapp (stores phone only) |
| Patient medical records | `solution.patient_records` | — | solution only |
| WhatsApp conversations | `whatsapp.whatsapp_conversations` | — | whatsapp only |
| Subscription / billing | `solution.subscriptions` / `orders` / `invoices` | — | solution only |
| API client credentials | `doctor_listing.clients` | — | HMAC middleware |
| Service account tokens | `solution.service_accounts` | — | service.token middleware |

### Role Responsibilities

| Role | Project | Can Do |
|------|---------|--------|
| Super Admin | doctor_listing | Manage all listings, verify doctors, manage API clients, manage master data |
| Super Admin | solution | Manage doctor owners, assign subscriptions, view all clinics |
| Doctor Owner | solution | Manage own clinic, staff, patients, appointments, billing, documents |
| Staff | solution | View patients, manage appointments (restricted access) |
| Patient (website) | taktaldoctor.com | Search doctors, book appointments anonymously |
| Patient (WhatsApp) | whatsapp | Search doctors, book appointments via chat |

---

## 9. API MAP — ALL ENDPOINTS

### 9.1 doctor_listing API (HMAC required)

Base URL: `{DOCTOR_LISTING_BASE_URL}/api/v1`

```
GET  /listings/search              ?country_code, city_id, location_id, service, q, per_page, page
GET  /listings/{uuid}
GET  /listings/slug/{qrSlug}
GET  /listings/{uuid}/documents
POST /listings/{uuid}/documents    multipart: document_type, document (file)
POST /listings/{uuid}/profile-photo  multipart: photo (file)
GET  /countries
GET  /cities/{countryCode}
GET  /locations/{cityId}
GET  /services
GET  /qualifications
```

### 9.2 solution API v1 (Sanctum Bearer OR Service Account Token)

Base URL: `{SOLUTION_BASE_URL}/api/v1`

```
POST  /login                    body: {email, password}
POST  /logout                   [Sanctum]
GET   /me                       [Sanctum]
GET   /dashboard                [Sanctum]

GET   /patients                 [Sanctum] ?search, per_page
GET   /patients/search          [Sanctum] ?q
POST  /patients                 [Sanctum] body: {name, phone, email?, dob?, ...}
GET   /patients/{patient}       [Sanctum]
PUT   /patients/{patient}       [Sanctum]

GET   /slots                    [Sanctum] ?date, per_page
GET   /slots/available          [Sanctum] ?date
POST  /slots                    [Sanctum] body: {day_of_week, start_time, end_time, capacity}
PUT   /slots/{slot}             [Sanctum]
DELETE /slots/{slot}            [Sanctum]

GET   /appointments             [Sanctum] ?date, status, per_page
POST  /appointments             [Sanctum] body: {patient_id, slot_id, date, type, ...}
GET   /appointments/{uuid}      [Sanctum]
PUT   /appointments/{uuid}      [Sanctum]
POST  /appointments/{uuid}/confirm    [Sanctum]
POST  /appointments/{uuid}/complete   [Sanctum]
POST  /appointments/{uuid}/cancel     [Sanctum]
POST  /appointments/{uuid}/no-show    [Sanctum]

GET   /public/clinic            [Service Token] ?listing_uuid OR qr_slug OR clinic_uuid
GET   /public/slots/available   [Service Token] ?listing_uuid, date
POST  /public/appointments      [Service Token] body: {listing_uuid, patient_name, phone, date, time, type}
POST  /integration/listing-cache-sync  [Service Token]
```

### 9.3 taktaldoctor.com Internal API (Session / public)

```
GET  /_api/cities?country=IND
GET  /_api/locations?city_id={id}
GET  /_api/services
GET  /_api/slots?slug={uuid}&date={YYYY-MM-DD}
POST /_api/book     body: {listing_uuid, patient_name, phone, appointment_date, appointment_time, ...}
```

---

## 10. SECURITY REVIEW

### 10.1 Authentication Security

| Project | Mechanism | Assessment |
|---------|-----------|------------|
| doctor_listing API | HMAC-SHA256 | STRONG — timestamp tolerance, nonce dedup, encrypted secret storage |
| solution web UI | Session + CSRF | STANDARD — Laravel default, adequate |
| solution REST API | Sanctum Bearer | STANDARD — adequate for mobile/API clients |
| solution M2M | Service Account Token | ADEQUATE — plain token in DB, consider hashing |
| whatsapp webhook | Meta hub.verify_token | ADEQUATE — verify token present |
| taktaldoctor.com | No auth (public) | CORRECT — public site, no sensitive data |

### 10.2 Security Strengths

- **HMAC replay protection:** Nonce cached in Redis/file cache; timestamp ±300s window — prevents replay attacks.
- **Secret key encryption:** `doctor_listing.clients.secret_key` stored via `Crypt::encrypt()` — encrypted at rest.
- **Secret shown once:** Secret key revealed only once via flash session after creation — not retrievable again.
- **Sanctum token scope:** `api.clinic` middleware scopes all API calls to authenticated user's clinic.
- **Service token isolation:** Service account tokens only access public endpoints, not doctor management.
- **Input validation:** All booking endpoints have explicit `validate()` calls.
- **CSRF protection:** All web form submissions protected by Laravel CSRF tokens.

### 10.3 Security Concerns

| Concern | Severity | Location | Recommendation |
|---------|----------|----------|---------------|
| Service account tokens stored plain | MEDIUM | `solution.service_accounts` | Hash tokens using `Hash::make()`, verify with `Hash::check()` |
| No rate limiting per WhatsApp wa_id | MEDIUM | whatsapp webhook | A spammer can flood the state machine |
| No STOP/opt-out handling in WhatsApp | MEDIUM | WhatsApp bot | Meta requires opt-out compliance |
| `/_api/book` is publicly accessible with no CAPTCHA | MEDIUM | taktaldoctor.com | Rate limit + CAPTCHA for booking form |
| No IP allowlisting for Meta webhook | LOW | whatsapp webhook | Whitelist Meta's IP ranges |
| HMAC timestamp tolerance 300s (5 min) | LOW | doctor_listing middleware | 300s is standard; acceptable risk |
| Laravel debug mode in production | UNKNOWN | All 4 projects | Verify `APP_DEBUG=false` in .env on prod |
| No MFA for admin panel | LOW | doctor_listing admin | Consider TOTP for super_admin accounts |

### 10.4 OWASP Top 10 Checklist

| Risk | Status |
|------|--------|
| A01 Broken Access Control | MITIGATED — role middleware, clinic scoping |
| A02 Cryptographic Failures | MITIGATED — Crypt for secrets, HMAC for API |
| A03 Injection | MITIGATED — Eloquent ORM, no raw queries found |
| A04 Insecure Design | PARTIALLY — service token not hashed |
| A05 Security Misconfiguration | UNKNOWN — need to verify prod .env |
| A06 Vulnerable Components | UNKNOWN — run `composer audit` |
| A07 Auth Failures | MITIGATED — Sanctum + HMAC + session |
| A08 Software Integrity | UNKNOWN — no code signing / integrity checks |
| A09 Logging Failures | MITIGATED — audit logs, webhook logs, booking logs |
| A10 SSRF | LOW RISK — HTTP calls only to known internal services |

---

## 11. DATA FLOW DIAGRAMS

### 11.1 Patient Books via Website

```
Patient (browser)
    │
    ├─1─► GET /doctor/{slug}
    │         └─► taktaldoctor.com DoctorController
    │                   └─► HMAC ──► doctor_listing /listings/slug/{slug}
    │                               Returns doctor profile
    │
    ├─2─► GET /book/{uuid}
    │         └─► BookingController@show
    │                   └─► HMAC ──► doctor_listing /listings/{uuid}
    │                               Returns doctor profile + qr_slug
    │
    ├─3─► GET /_api/slots?slug={uuid}&date=2026-06-15
    │         └─► BookingController@slots
    │                   └─► Service Token ──► solution /public/slots/available
    │                                        Returns available time slots
    │
    └─4─► POST /_api/book {listing_uuid, patient_name, phone, date, time}
              └─► BookingController@book
                        └─► Service Token ──► solution /public/appointments
                                             Creates appointment
                                             Returns {appointment_uuid, appointment_no}
```

### 11.2 Patient Books via WhatsApp

```
Patient (WhatsApp)
    │
    ├─1─► "hi" → Meta Cloud API → POST /webhook
    │              └─► StateMachine: sendWelcomeMenu → MAIN_MENU_SENT
    │
    ├─2─► [tap "Book Appointment"] → sendSearchTypeMenu → WAITING_FOR_SEARCH_TYPE
    │
    ├─3─► [tap "Doctor Name"] → sendText "type name" → WAITING_FOR_SEARCH_QUERY
    │
    ├─4─► "Dr Kumar" → searchListings() ──► HMAC ──► doctor_listing /listings/search
    │                   Returns doctor list → sendList → WAITING_FOR_DOCTOR_SELECTION
    │
    ├─5─► [tap doctor] → getListingByUuid() ──► HMAC ──► doctor_listing /listings/{uuid}
    │                     Stores listing_uuid, doctor_name → WAITING_FOR_DATE
    │
    ├─6─► "2026-06-15" → getAvailableSlots() ──► Service Token ──► solution /public/slots/available
    │                     Returns slots → sendList → WAITING_FOR_SLOT_SELECTION
    │
    ├─7─► [tap "10:00 AM"] → stores slot_time → WAITING_FOR_PATIENT_NAME
    │
    ├─8─► "Ramesh Kumar" → stores patient_name → WAITING_FOR_PATIENT_PHONE
    │
    ├─9─► "9876543210" → stores phone → sendConfirmationSummary → WAITING_FOR_CONFIRMATION
    │
    └─10─► [tap "Confirm"] → bookAppointment() ──► Service Token ──► solution /public/appointments
                              Returns {appointment_uuid, appointment_no} → BOOKED
```

### 11.3 Doctor Registers a Clinic

```
Super Admin (solution admin panel)
    │
    ├─1─► POST /doctor-owners (create user)
    │         Creates user with role=doctor_owner
    │
    └─2─► Doctor Owner logs in → /dashboard
              │
              └─► /clinics/create → POST /clinics
                        body: {listing_uuid, name, phone, address}
                        │
                        └─► ClinicController@store
                                  │
                                  ├─► Creates clinic record
                                  └─► Calls DoctorListingService@getListingByUuid
                                            └─► HMAC ──► doctor_listing /listings/{uuid}
                                                         Stores response in doctor_listing_cache
```

---

## 12. RECOMMENDED DEVELOPMENT ORDER

Based on the current state of all 4 projects, here is the recommended order:

### Phase A — Critical Infrastructure (Do This Week)

1. **Run pending migration in doctor_listing**
   - `php artisan migrate` — adds `profile_photo_path` to listings
   - Blocks profile photo feature from working

2. **Hash service account tokens in solution**
   - Currently stored plain in `service_accounts`
   - Security risk if DB is compromised

3. **Verify `APP_DEBUG=false` in all production .env files**
   - Exposes stack traces and config if debug is on in production

### Phase B — High Value Features (Next 2-4 Weeks)

4. **Booking confirmation email/SMS from solution**
   - After appointment is created, send confirmation to patient phone/email
   - Already have `notifications` table — just need the mailer/SMS sender

5. **Appointment reminder from WhatsApp bot**
   - 24-hour before appointment: send WhatsApp message to patient
   - Needs scheduled job in whatsapp project

6. **Payment gateway integration in solution**
   - Razorpay or Stripe
   - `orders` and `invoices` tables already exist
   - Blocks subscription monetization

7. **Booking cancellation flow in WhatsApp bot**
   - Patient asks to cancel → bot looks up recent booking → calls solution cancel endpoint
   - Needs new states in state machine

### Phase C — Growth Features (1-2 Months)

8. **Patient login portal on taktaldoctor.com**
   - Allow patients to register, see booking history
   - Currently all bookings are anonymous

9. **Doctor reviews and ratings on taktaldoctor.com**
   - Post-appointment review link via WhatsApp/email
   - Display ratings on doctor profile

10. **Bulk listing import in doctor_listing**
    - CSV upload for batch doctor registration
    - Currently manual entry only

11. **Schema.org markup on taktaldoctor.com**
    - `MedicalBusiness`, `Physician` structured data
    - Boosts Google rich snippets significantly

12. **WhatsApp opt-out / STOP handling**
    - Meta compliance requirement
    - Block list management

### Phase D — Advanced Features (2-3 Months)

13. **Doctor mobile app**
    - Use existing solution REST API v1
    - Add push notification endpoints
    - React Native / Flutter recommended

14. **Prescription PDF generation**
    - `patient_records` table already exists
    - Add `patient_record_items` table for structured prescriptions

15. **Auto listing cache sync**
    - doctor_listing should call `solution/integration/listing-cache-sync` on listing update
    - Currently requires manual refresh

---

## 13. WHAT TO BUILD NEXT

Priority order with justification:

### MUST BUILD (blocks revenue or compliance)

| Item | Project | Why |
|------|---------|-----|
| Payment gateway | solution | Subscriptions have no payment processing — revenue is blocked |
| Booking confirmation SMS/email | solution + whatsapp | Patients have no receipt — trust issue |
| WhatsApp opt-out (STOP) | whatsapp | Meta Cloud API compliance requirement |
| Run profile photo migration | doctor_listing | Feature exists but migration not run |

### SHOULD BUILD (high patient value)

| Item | Project | Why |
|------|---------|-----|
| Appointment reminders (WhatsApp) | whatsapp | Reduces no-shows significantly |
| Patient portal + booking history | taktaldoctor.com | Returning patients have no account |
| Doctor reviews | taktaldoctor.com | Increases trust and conversion |
| Booking cancellation via WhatsApp | whatsapp | Most demanded post-booking feature |
| Schema.org markup | taktaldoctor.com | SEO rich snippet in Google |

### CAN BUILD (nice to have)

| Item | Project | Why |
|------|---------|-----|
| Blog/health articles | taktaldoctor.com | Long-tail SEO content |
| Doctor mobile app | new project | Convenient for doctors managing on mobile |
| Video consultation | solution | Expands beyond in-clinic |
| Multi-language support | whatsapp | Hindi-speaking market is large |

---

## 14. WHAT NOT TO REBUILD

These things are already built well. Do NOT rebuild them:

| Thing | Location | Why It's Good |
|-------|----------|---------------|
| HMAC authentication | doctor_listing middleware | Industry standard, replay protected, correctly implemented |
| WhatsApp state machine | whatsapp StateMachineService | Clean, well-structured, easy to extend |
| DoctorListingService (Guzzle) | solution | Correct HMAC signing, handles multipart, good error handling |
| DoctorListingApiService (Http facade) | whatsapp, taktaldoctor.com | Correct implementation, consistent |
| SEO URL routing | taktaldoctor.com routes | Smart regex guards, clean slug parsing |
| Booking state stored in conversation | whatsapp | Correct approach — survives reconnects |
| HMAC client credential system | doctor_listing clients + admin | Encrypted secret, show-once pattern is correct |
| Appointment status workflow | solution | 5 statuses (pending/confirmed/complete/cancel/no-show) is adequate |
| Listing audit trail | doctor_listing | JSON diff audit log is comprehensive |
| Excel export | solution | Already implemented across all major entities |
| Service account tokens for M2M | solution | Right separation from user-facing auth |

**Do NOT:**
- Replace HMAC with OAuth/JWT for server-to-server calls — HMAC is simpler and more secure for this use case
- Rebuild doctor_listing cache — the `doctor_listing_cache` table is the right approach
- Create a separate search service — doctor_listing already handles search
- Add a frontend framework (React/Vue) to solution portal — Blade is adequate for admin UI
- Build a separate notification microservice — Laravel notifications + queues is sufficient

---

## 15. MISSING MODULES ACROSS ALL PROJECTS

### Cross-Project Gaps

| Gap | Impact | Which Projects |
|-----|--------|---------------|
| No push notification system | HIGH | solution, whatsapp |
| No payment processing | HIGH | solution, taktaldoctor.com |
| No booking cancellation for patients | HIGH | whatsapp, taktaldoctor.com |
| No booking reminder system | HIGH | whatsapp (outbound), solution |
| No patient identity system | MEDIUM | taktaldoctor.com (anonymous bookings) |
| No listing update push to solution | MEDIUM | doctor_listing → solution |
| No doctor mobile app | MEDIUM | solution (API exists, no mobile app) |

### Per-Project Gap Summary

**doctor_listing:** Profile photo migration not run, no bulk import, no doctor self-registration, no push webhook to consumers

**solution:** No payment gateway, no outbound SMS/email, no prescription PDF, no mobile-specific API endpoints, no appointment reminder jobs

**whatsapp:** No reminders, no cancellation, no reschedule, no opt-out handling, English only

**taktaldoctor.com:** No patient login, no booking history, no reviews, no schema.org markup, no payment

---

## 16. TECHNICAL DEBT & RISKS

### Debt

| Item | Severity | Where |
|------|----------|-------|
| `DoctorListingApiService` duplicated in 3 projects (whatsapp, website, solution) | MEDIUM | All 3 consumers independently implement HMAC — could be shared package |
| `SolutionApiService` duplicated in 2 projects (whatsapp, website) | LOW | Similar service client code |
| No composer.json `scripts` for post-deploy tasks | LOW | Migrations, cache clear not automated |
| Doctor listing cache sync is manual | MEDIUM | Profile changes in doctor_listing not reflected in solution automatically |
| No integration tests across projects | HIGH | Each project tested in isolation — cross-system flows untested |
| WhatsApp conversation `wa_id` used as phone number identifier — no normalization | MEDIUM | `+919876543210` vs `919876543210` could create duplicate conversations |
| No soft deletes on appointments | LOW | Deleted appointments lose history |

### Risks

| Risk | Probability | Impact |
|------|-------------|--------|
| doctor_listing API downtime affects all 3 consumers simultaneously | MEDIUM | HIGH — entire platform stops working |
| Meta Cloud API webhook rate limiting during peak hours | LOW | HIGH — all WhatsApp bookings stop |
| Service account token compromise in solution | LOW | HIGH — all public bookings could be abused |
| Laravel cache driver flush loses nonce dedup cache | LOW | MEDIUM — brief window for HMAC replay attacks |
| Profile photo migration not run causing 500 errors | HIGH | MEDIUM — photo upload will fail |

---

## APPENDIX: ENVIRONMENT VARIABLES NEEDED

### doctor_listing `.env` additions
```
# (Standard Laravel - already exists)
APP_KEY=...
DB_CONNECTION=mysql
CACHE_DRIVER=redis  # Recommended for nonce storage
```

### solution `.env` additions
```
DOCTOR_LISTING_BASE_URL=http://localhost/doctor_listing/public
DOCTOR_LISTING_API_KEY={32-char hex from doctor_listing admin}
DOCTOR_LISTING_API_SECRET={64-char hex secret}
SANCTUM_STATEFUL_DOMAINS=solution.tatkaldoctor.local
```

### whatsapp `.env` additions
```
WHATSAPP_PHONE_NUMBER_ID=...
WHATSAPP_ACCESS_TOKEN=...
WHATSAPP_VERIFY_TOKEN=...
DOCTOR_LISTING_BASE_URL=...
DOCTOR_LISTING_API_KEY=...
DOCTOR_LISTING_API_SECRET=...
SOLUTION_BASE_URL=...
SOLUTION_SERVICE_TOKEN=...
```

### taktaldoctor.com `.env` additions
```
DOCTOR_LISTING_BASE_URL=...
DOCTOR_LISTING_API_KEY=...
DOCTOR_LISTING_API_SECRET=...
SOLUTION_BASE_URL=...
SOLUTION_SERVICE_TOKEN=...
```

---

*Audit completed: 2026-06-10*  
*All findings are based on static code analysis. No data was modified.*  
*Total files analyzed: ~40 files across 4 projects*
