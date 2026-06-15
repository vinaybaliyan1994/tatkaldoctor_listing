# WHATSAPP SERVICE — PLATFORM-AGNOSTIC ARCHITECTURE AUDIT
**Date:** 2026-06-10  
**Service:** `c:\wamp64\www\tatkaldoctor.whatsapp.com`  
**Method:** Read-only static analysis of all 21 PHP files, 11 migrations, routes  
**Question:** Does the service follow a platform-agnostic architecture, or is it still coupled to TatkalDoctor's models?

---

## VERDICT SUMMARY

The WhatsApp service is **partially platform-agnostic**.

The Meta/WhatsApp protocol layer — webhook receiver, parser, message sender, conversation storage, booking attempt log — is structurally correct and reusable. The service does NOT own doctors, schedules, appointments, patients, clinics, or subscriptions. That boundary is respected.

However, **6 specific coupling points remain** that would break reuse with any other platform. They are all cosmetic-to-medium changes — no architecture redesign is required.

| Layer | Verdict |
|-------|---------|
| Meta API / webhook protocol | Platform-agnostic |
| Conversation state machine flow | Platform-agnostic (1 state name exception) |
| Message storage | Platform-agnostic |
| Booking attempt log | Platform-agnostic (column names are coupled) |
| External service clients | COUPLED — both class names and method names are TatkalDoctor-specific |
| Database column names (conversations + booking attempts) | COUPLED — use TatkalDoctor domain vocabulary |
| State machine text strings | COUPLED — hardcoded "Doctor", "Dr.", "book a doctor" |
| Config key names | COUPLED — `doctor_listing.*` and `solution.*` |

---

## SECTION 1 — WHAT THE SERVICE CORRECTLY OWNS

These tables and files are WhatsApp-native. They belong here regardless of which platform is connected.

| Owns | File / Table | Notes |
|------|-------------|-------|
| Webhook logs | `whatsapp_webhook_logs` | Raw Meta Cloud API payloads |
| Conversations | `whatsapp_conversations` | Per-user session state |
| Messages | `whatsapp_messages` | Inbound + outbound message history |
| Booking attempts | `whatsapp_booking_attempts` | Request/response log for every booking call |
| Meta message sender | `WhatsAppMessageSender.php` | Calls Meta Graph API, stores outgoing messages |
| Webhook parser | `WhatsAppWebhookParser.php` | Parses Meta payload format |
| Conversation service | `WhatsAppConversationService.php` | findOrCreate, storeIncomingMessage |
| State machine | `WhatsAppStateMachineService.php` | Booking flow logic |
| HMAC client credentials | `clients` table | For calling doctor_listing API |

The service does **NOT** have tables for: doctors, appointments, slots, patients, clinics, subscriptions, or any domain entity of the connected platform. This is correct.

---

## SECTION 2 — COUPLING ISSUES (6 found)

### COUPLING #1 — `DoctorListingApiService` is a named concrete class (HIGH)

**File:** `app/Services/DoctorListingApiService.php`  
**Problem:** The class name, method names, and config keys are all doctor_listing-specific.

```php
// Current — TatkalDoctor-specific vocabulary
class DoctorListingApiService {
    public function searchListings(array $filters): array   { ... }
    public function getListingByUuid(string $uuid): array   { ... }
    public function getListingBySlug(string $qrSlug): array { ... }
    public function getServices(): array                    { ... }
    public function getCities(string $countryCode): array   { ... }
    public function getLocations(int $cityId): array        { ... }
}

// Reads from config:
config('doctor_listing.base_url')
config('doctor_listing.api_key')
config('doctor_listing.api_secret')
```

**What this represents generically:** A **Catalog API** — any API that lets the bot search for providers (doctors, dentists, lawyers, etc.) and fetch their profiles.

**The class is injected by concrete type in the state machine:**
```php
// WhatsAppStateMachineService.php line 21-25
public function __construct(
    private readonly WhatsAppMessageSender    $sender,
    private readonly DoctorListingApiService  $doctorApi,    // ← concrete class
    private readonly SolutionApiService       $solutionApi,  // ← concrete class
) {}
```

There is no interface. Swapping platforms requires editing the constructor.

---

### COUPLING #2 — `SolutionApiService` is a named concrete class (HIGH)

**File:** `app/Services/SolutionApiService.php`  
**Problem:** "Solution" is a TatkalDoctor product name. The class also uses domain-specific identifier vocabulary.

```php
// Current — TatkalDoctor-specific vocabulary
class SolutionApiService {
    public function getPublicClinic(array $identifiers): array    { ... }
    //                              ^^^^^^^^^^^^^^^^^^^
    //  accepts: listing_uuid, qr_slug, clinic_uuid — all TatkalDoctor concepts

    public function getAvailableSlots(array $identifiers, string $date): array { ... }
    //                                ^^^^^^^^^^^^^^^^^^
    //  same identifiers

    public function bookAppointment(array $payload): array { ... }
    //  payload keys: listing_uuid, qr_slug, clinic_uuid, whatsapp_no, type='in_clinic'
    //                                                                       ^^^^^^^^^
    //                                                       hardcoded appointment type
}

// Reads from config:
config('solution.base_url')
config('solution.service_token')
```

**What this represents generically:** A **Booking API** — any API that exposes slot availability and allows appointment creation.

---

### COUPLING #3 — `whatsapp_conversations` column names use TatkalDoctor vocabulary (MEDIUM)

**Migration:** `2026_06_07_000004_create_whatsapp_conversations_table.php`

The conversation row is the bot's working memory for a user session. It stores identifiers that need to be passed back to the platform APIs when fetching slots and booking. Currently those identifiers are named after TatkalDoctor's model layer:

| Current Column | What It Actually Stores | Agnostic Name |
|----------------|------------------------|---------------|
| `listing_uuid` | The platform's provider entity ID | `provider_entity_id` |
| `qr_slug` | The provider's shortcode/slug | `provider_entity_slug` |
| `clinic_uuid` | The bookable resource ID (clinic/branch) | `provider_resource_id` |
| `doctor_name` | The selected provider's display name | `provider_entity_name` |
| `appointment_uuid` | The created booking's internal ID | `booking_id` |
| `appointment_no` | The booking reference shown to the user | `booking_ref` |

The `WhatsAppConversation` model exposes these as public properties used throughout the state machine:
```php
// WhatsAppStateMachineService.php — used 14+ times
$conversation->listing_uuid
$conversation->qr_slug
$conversation->clinic_uuid
$conversation->doctor_name
$conversation->appointment_uuid
$conversation->appointment_no
```

---

### COUPLING #4 — `whatsapp_booking_attempts` column names (MEDIUM)

**Migration:** `2026_06_07_000006_create_whatsapp_booking_attempts_table.php`

Same vocabulary issue in the attempt log:

| Current Column | Agnostic Name |
|----------------|---------------|
| `listing_uuid` | `provider_entity_id` |
| `qr_slug` | `provider_entity_slug` |
| `clinic_uuid` | `provider_resource_id` |
| `appointment_uuid` | `booking_id` |
| `appointment_no` | `booking_ref` |

```php
// WhatsAppBookingAttempt model — fillable array mirrors this
protected $fillable = [
    'listing_uuid', 'qr_slug', 'clinic_uuid',
    'appointment_uuid', 'appointment_no', ...
];
```

---

### COUPLING #5 — Hardcoded domain text in the state machine (MEDIUM)

**File:** `app/Services/WhatsAppStateMachineService.php`

The user-visible text is written for a doctor booking service and cannot be changed without editing the service code:

```php
// Line 94-100 — hardcoded "Book Appointment" + "book a doctor"
$rows = [
    ['id' => 'book_appointment', 'title' => 'Book Appointment',
     'description' => 'Search and book a doctor'],   // ← "a doctor"
    ...
];

// Line 128-131 — "Find a Doctor", "Search by doctor or hospital name"
'Find a Doctor', 'How would you like to search for a doctor?'
['id' => 'search_doctor_name', 'title' => 'Doctor Name',
 'description' => 'Search by doctor or hospital name'],

// Line 221-223 — "Dr." prefix hardcoded
$this->sender->sendText($conversation, "You selected Dr. $doctorName.");

// Line 249 — same
$this->sender->sendText($conversation, "Doctor profile found: Dr. $doctorName.");

// Lines 56-58 — QR shortcut prefix convention is TatkalDoctor-specific
if (str_starts_with($textLow, 'qr:') || str_starts_with($textLow, 'slug:')) {
    $this->handleQrEntry($conversation, $rawText);
```

---

### COUPLING #6 — One state name uses domain vocabulary (LOW)

**File:** `app/Enums/WhatsAppConversationState.php`

```php
case WAITING_FOR_DOCTOR_SELECTION = 'WAITING_FOR_DOCTOR_SELECTION';
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
// 14 other states are generic (WAITING_FOR_DATE, WAITING_FOR_SLOT_SELECTION, etc.)
// Only this one uses "DOCTOR" — stored as a string in the DB
```

All other states are generic: `START`, `MAIN_MENU_SENT`, `WAITING_FOR_SEARCH_TYPE`, `WAITING_FOR_SEARCH_QUERY`, `WAITING_FOR_DATE`, `WAITING_FOR_SLOT_SELECTION`, `WAITING_FOR_PATIENT_NAME`, `WAITING_FOR_PATIENT_PHONE`, `WAITING_FOR_CONFIRMATION`, `BOOKED`, `FAILED`, `HELP`. Only `WAITING_FOR_DOCTOR_SELECTION` is domain-specific.

Note: Changing this enum value requires a DB migration to rename existing rows.

---

### MINOR OBSERVATION — `routes/web.php` brand name

```php
// routes/web.php line 11
return 'TatkalDoctor WhatsApp Service Ready';   // ← hardcoded brand

// line 16
'service' => 'tatkaldoctor-whatsapp',           // ← hardcoded brand in health check
```

Not a structural coupling issue but worth noting for white-label reuse.

---

## SECTION 3 — WHAT THE PLATFORM MUST EXPOSE (API CONTRACT)

For the WhatsApp service to be platform-agnostic, the connected platform must implement two API contracts. These are derived directly from what the state machine currently calls.

---

### CONTRACT A — Catalog API

Used for: provider search, profile fetch by ID, profile fetch by slug.

The WhatsApp service will call these. The platform implements them.

```
AUTH: Any mechanism (HMAC, Bearer, API Key, etc.) — configured per deployment

GET  /providers/search
  Query: {
    q?:           string      // free-text keyword
    category_id?: string|int  // specialty / service filter
    location_id?: string|int  // sub-city / area filter
    city_id?:     string|int  // city filter
    country?:     string      // country code
    page?:        int
    per_page?:    int          // max 10 recommended for WhatsApp list messages
  }
  Response: {
    success: true,
    data: [
      {
        id:          string,   // opaque provider identifier — stored as provider_entity_id
        slug:        string,   // short slug for QR/link — stored as provider_entity_slug
        name:        string,   // display name shown in WhatsApp list
        description: string,   // shown as list row description (max 72 chars)
        location?:   string,   // city / area — optional, for richer list display
      }
    ]
  }
  Error: { success: false, message: string }


GET  /providers/{id}
  Response: {
    success: true,
    data: {
      id:          string,
      slug:        string,
      name:        string,
      description: string,
      location?:   string,
      resource_id: string,  // the bookable resource (clinic/branch) — stored as provider_resource_id
                            // may be same as id if the entity IS the bookable resource
    }
  }
  Error 404: { success: false, message: "Not found" }


GET  /providers/by-slug/{slug}
  Response: same shape as GET /providers/{id}
  Error 404: { success: false, message: "Not found" }


GET  /categories                (optional — for search type menu)
  Response: {
    success: true,
    data: [{ id: int|string, name: string }]
  }


GET  /locations                 (optional — for location filter)
  Query: { city_id?, country? }
  Response: {
    success: true,
    data: [{ id: int|string, name: string }]
  }
```

---

### CONTRACT B — Booking API

Used for: slot availability lookup, appointment creation.

```
AUTH: Service Account Token (X-Service-Token header) recommended
      or any shared secret mechanism

GET  /availability
  Query: {
    provider_resource_id: string,   // the resource to check (clinic UUID, etc.)
    date:                 string,   // YYYY-MM-DD
  }
  Response: {
    success: true,
    data: [
      {
        time:               string,    // HH:MM — used as slot identifier AND display
        end_time?:          string,    // HH:MM — optional
        available_capacity: int,       // shown to user; 0 means full
      }
    ]
  }
  Error 404: { success: false, message: "Provider not found" }
  Empty:     { success: true, data: [] }   // no slots for that date


POST /bookings
  Body: {
    provider_resource_id: string,   // resolved resource (clinic/branch/office)
    appointment_date:     string,   // YYYY-MM-DD
    appointment_time:     string,   // HH:MM (from /availability response)
    patient_name:         string,
    phone:                string,
    whatsapp_no?:         string,
    reason?:              string,
    type?:                string,   // "in_clinic", "online", etc. — platform decides default
  }
  Response 200/201: {
    success: true,
    data: {
      booking_id:  string,   // stored as booking_id in whatsapp_conversations
      booking_ref: string,   // human-readable reference shown to patient (e.g. "APT-2026-001")
    }
  }
  Error 409: { success: false, message: "Slot no longer available" }
             → bot resets to WAITING_FOR_DATE, offers another slot
  Error 422: { success: false, message: "...", errors: {...} }
             → bot shows error, goes to FAILED
```

---

### CONTRACT SUMMARY TABLE

| Call | Method | Path | Contract | When |
|------|--------|------|----------|------|
| Search providers | GET | `/providers/search?q=...` | Catalog | Patient types search term |
| Fetch by ID | GET | `/providers/{id}` | Catalog | Patient selects from list |
| Fetch by slug | GET | `/providers/by-slug/{slug}` | Catalog | Patient sends `ref:{slug}` |
| Get categories | GET | `/categories` | Catalog (optional) | Search type menu |
| Get slots | GET | `/availability?resource_id=...&date=...` | Booking | Patient enters date |
| Create booking | POST | `/bookings` | Booking | Patient confirms |

---

## SECTION 4 — PROPOSED CHANGES TO MAKE THE SERVICE PLATFORM-AGNOSTIC

These are the minimal changes needed. Listed from most to least impactful.

### Change 1 — Define two PHP interfaces (NEW FILES)

**`app/Contracts/CatalogApiContract.php`:**
```php
interface CatalogApiContract
{
    /** @return array{success: bool, data: array, message: string, status: int} */
    public function searchProviders(array $filters): array;

    /** @return array{success: bool, data: array, message: string, status: int} */
    public function getProviderById(string $id): array;

    /** @return array{success: bool, data: array, message: string, status: int} */
    public function getProviderBySlug(string $slug): array;
}
```

**`app/Contracts/BookingApiContract.php`:**
```php
interface BookingApiContract
{
    /** @return array{success: bool, data: array, message: string, status: int} */
    public function getAvailableSlots(string $resourceId, string $date): array;

    /** @return array{success: bool, data: array, message: string, status: int} */
    public function createBooking(array $payload): array;
}
```

---

### Change 2 — Rename service classes and implement interfaces

| Current Name | New Name | Implements |
|-------------|----------|-----------|
| `DoctorListingApiService` | `CatalogApiService` | `CatalogApiContract` |
| `SolutionApiService` | `BookingApiService` | `BookingApiContract` |

**Method renames in `CatalogApiService`:**

| Current | New |
|---------|-----|
| `searchListings(filters)` | `searchProviders(filters)` |
| `getListingByUuid(uuid)` | `getProviderById(id)` |
| `getListingBySlug(slug)` | `getProviderBySlug(slug)` |

**Method renames in `BookingApiService`:**

| Current | New |
|---------|-----|
| `getPublicClinic(identifiers)` | Remove — state machine should resolve resource_id from catalog response |
| `getAvailableSlots(identifiers, date)` | `getAvailableSlots(string $resourceId, string $date)` |
| `bookAppointment(payload)` | `createBooking(payload)` |

---

### Change 3 — Update `AppServiceProvider` to bind interfaces

```php
// app/Providers/AppServiceProvider.php
$this->app->bind(CatalogApiContract::class,  CatalogApiService::class);
$this->app->bind(BookingApiContract::class,   BookingApiService::class);
```

---

### Change 4 — Update state machine constructor to use interfaces

```php
// WhatsAppStateMachineService.php
public function __construct(
    private readonly WhatsAppMessageSender $sender,
    private readonly CatalogApiContract    $catalog,   // was: DoctorListingApiService $doctorApi
    private readonly BookingApiContract    $booking,   // was: SolutionApiService $solutionApi
) {}
```

Rename all internal references: `$this->doctorApi` → `$this->catalog`, `$this->solutionApi` → `$this->booking`.

---

### Change 5 — Rename database columns (2 migrations needed)

**Migration for `whatsapp_conversations`:**

| Rename From | Rename To |
|------------|-----------|
| `listing_uuid` | `provider_entity_id` |
| `qr_slug` | `provider_entity_slug` |
| `clinic_uuid` | `provider_resource_id` |
| `doctor_name` | `provider_entity_name` |
| `appointment_uuid` | `booking_id` |
| `appointment_no` | `booking_ref` |

**Migration for `whatsapp_booking_attempts`:**

| Rename From | Rename To |
|------------|-----------|
| `listing_uuid` | `provider_entity_id` |
| `qr_slug` | `provider_entity_slug` |
| `clinic_uuid` | `provider_resource_id` |
| `appointment_uuid` | `booking_id` |
| `appointment_no` | `booking_ref` |

Update `WhatsAppConversation::$fillable`, `WhatsAppBookingAttempt::$fillable`, and all `$conversation->listing_uuid` references in the state machine.

---

### Change 6 — Extract state machine text to config

```php
// config/whatsapp_flow.php (new file)
return [
    'provider_label'         => env('WA_FLOW_PROVIDER_LABEL', 'Provider'),
    'provider_prefix'        => env('WA_FLOW_PROVIDER_PREFIX', ''),       // "Dr." for doctors
    'search_action_label'    => env('WA_FLOW_SEARCH_LABEL',  'Book Appointment'),
    'search_action_desc'     => env('WA_FLOW_SEARCH_DESC',   'Search and book'),
    'slug_shortcut_prefix'   => env('WA_FLOW_SLUG_PREFIX',   'ref'),      // was: "qr" / "slug"
];
```

Replace hardcoded strings in the state machine:

```php
// Before
"Search and book a doctor"
"Find a Doctor"
"You selected Dr. $doctorName"

// After
config('whatsapp_flow.search_action_desc')
'Find a ' . config('whatsapp_flow.provider_label')
"You selected " . config('whatsapp_flow.provider_prefix') . $doctorName
```

---

### Change 7 — Rename one enum case (LOW priority)

```php
// Before
case WAITING_FOR_DOCTOR_SELECTION = 'WAITING_FOR_DOCTOR_SELECTION';

// After
case WAITING_FOR_PROVIDER_SELECTION = 'WAITING_FOR_PROVIDER_SELECTION';
```

Requires DB migration: `UPDATE whatsapp_conversations SET current_state = 'WAITING_FOR_PROVIDER_SELECTION' WHERE current_state = 'WAITING_FOR_DOCTOR_SELECTION'`

---

### Change 8 — Rename config groups

| Current Config Key | New Config Key |
|-------------------|----------------|
| `doctor_listing.base_url` | `platform.catalog_url` |
| `doctor_listing.api_key` | `platform.catalog_api_key` |
| `doctor_listing.api_secret` | `platform.catalog_api_secret` |
| `doctor_listing.timeout` | `platform.catalog_timeout` |
| `solution.base_url` | `platform.booking_url` |
| `solution.service_token` | `platform.booking_token` |
| `solution.timeout` | `platform.booking_timeout` |

---

## SECTION 5 — CHANGE IMPACT MATRIX

| Change | Files Affected | DB Migration? | Breaking? |
|--------|---------------|--------------|-----------|
| 1. Define interfaces | 2 new files | No | No |
| 2. Rename service classes | 2 files | No | No (old names can be aliased) |
| 3. AppServiceProvider bindings | 1 file | No | No |
| 4. State machine constructor | 1 file + references | No | No |
| 5. Rename DB columns | 2 migrations | YES | Yes — update model + all `$conversation->*` references |
| 6. Config-driven text | 1 new config file + 1 state machine file | No | No |
| 7. Rename enum case | 1 enum + 1 migration | YES | Yes — update all state comparisons |
| 8. Rename config groups | 2 config files + `.env` | No | No (update env vars) |

**Total files to change: ~10 files + 2 new migrations**  
**Risk: LOW** — most changes are renames with no logic change.

---

## SECTION 6 — ARCHITECTURE DIAGRAM AFTER CHANGES

```
┌────────────────────────────────────────────────────────┐
│              WhatsApp Service                          │
│                                                        │
│  Meta Cloud API ←──── WhatsAppMessageSender            │
│        │                                              │
│        ▼                                              │
│  WhatsAppWebhookController                             │
│        │                                              │
│        ▼                                              │
│  WhatsAppStateMachineService                           │
│    injects: CatalogApiContract  ───────────────────►  │──► Any catalog API (HMAC, OAuth, etc.)
│             BookingApiContract  ───────────────────►  │──► Any booking API (service token, etc.)
│                                                        │
│  Owns:                                                 │
│    whatsapp_conversations  (provider_entity_id, etc.)  │
│    whatsapp_messages                                   │
│    whatsapp_webhook_logs                               │
│    whatsapp_booking_attempts (booking_id, booking_ref) │
└────────────────────────────────────────────────────────┘

Platform A (TatkalDoctor):
  CatalogApiService  → doctor_listing API (HMAC)
  BookingApiService  → solution.tatkaldoctor.com API (Service Token)

Platform B (hypothetical dental chain):
  CatalogApiService  → dental_registry API (Bearer token)
  BookingApiService  → dental_scheduling API (API key)
```

---

## SECTION 7 — IMPLEMENTATION ORDER RECOMMENDATION

If you want to do this incrementally without breaking the running service:

**Step 1** (safe, no DB changes):  
Create interfaces → rename service classes → bind in AppServiceProvider → update state machine constructor. System still works, nothing changes for users.

**Step 2** (requires deploy coordination):  
Rename config groups. Update `.env` on the server before deploying.

**Step 3** (requires migration + code update together):  
Rename DB columns (5 + 6 columns in 2 tables) + update model fillables + update all `$conversation->listing_uuid` etc. references. This must be deployed atomically — migration + code together.

**Step 4** (optional, low priority):  
Extract text to config. Rename enum case (requires data migration).

---

## SECTION 8 — WHAT IS ALREADY CORRECT

Do not change these — they are correctly platform-agnostic:

| Component | Why It's Correct |
|-----------|-----------------|
| `WhatsAppWebhookParser` | Parses Meta Cloud API format — has nothing to do with the platform |
| `WhatsAppWebhookLogger` | Logs raw webhook payloads — platform-agnostic |
| `WhatsAppMessageSender` | Sends to Meta — platform-agnostic |
| `WhatsAppConversationService` | findOrCreate / storeIncomingMessage — generic |
| State machine FLOW (not text) | search → select → date → slot → name → phone → confirm → book — generic |
| `whatsapp_messages` table | Stores message direction, type, body, payload — fully generic |
| `whatsapp_webhook_logs` table | Stores raw Meta payloads — fully generic |
| Duplicate message guard | `wa_message_id` dedup is Meta-protocol-level |
| `Client` model + `clients` table | Stores API credentials — generic concept |
| Booking attempt logging | Pattern is correct — records request + response payload |
| Dashboard + admin views | Use model data only — no platform business logic |

---

*Audit complete: 2026-06-10*  
*21 PHP files analyzed. No code was modified.*
