#Requires -Version 5.1
<#
.SYNOPSIS
    HMAC API Test Suite - TatkalDoctor Doctor Listing
.DESCRIPTION
    Tests every HMAC-protected GET endpoint.
    Credentials and dynamic test data are fetched from the database
    via php artisan tinker before tests begin.

    Signature note:
      The middleware signs with $request->getPathInfo() which is the
      URL path WITHOUT the query string.  The full URL (with query
      string) is used only for the actual HTTP request.
#>

$ErrorActionPreference = "Continue"
Set-Location "C:\wamp64\www\doctor_listing"

$BASE_URL = "http://127.0.0.1:8000"

# ------------------------------------------------------------------------------
# Helper - run a php artisan tinker --execute snippet and return the last
# non-empty output line.  Tinker may prefix output with its banner; we take
# the last non-empty line which is always our echo'd JSON.
# ------------------------------------------------------------------------------
function Invoke-Tinker {
    param([string]$PhpCode)

    $raw   = php artisan tinker --execute=$PhpCode 2>$null
    $lines = @($raw | Where-Object { ($_ -ne $null) -and ($_.Trim() -ne '') })
    if ($lines.Count -eq 0) { return '' }
    return $lines[-1].Trim()
}

# ------------------------------------------------------------------------------
# Step 1 - Active client credentials
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "  FETCHING CLIENT CREDENTIALS" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray

$clientCode = "`$c=App\Models\Client::where('status','active')->first();echo json_encode(`$c?['api_key'=>`$c->api_key,'secret'=>`$c->getDecryptedSecretKey()]:['error'=>'No active client']);"
$clientJson = Invoke-Tinker $clientCode

try {
    $clientData = $clientJson | ConvertFrom-Json
} catch {
    Write-Host "FATAL: Could not parse tinker output for client." -ForegroundColor Red
    Write-Host "Raw output: $clientJson"
    exit 1
}

if ($clientData.error) {
    Write-Host "FATAL: $($clientData.error)" -ForegroundColor Red
    exit 1
}

$API_KEY    = $clientData.api_key
$SECRET_KEY = $clientData.secret
Write-Host "  api_key : $API_KEY" -ForegroundColor Green
Write-Host "  secret  : [retrieved, $($SECRET_KEY.Length) chars]" -ForegroundColor Green

# ------------------------------------------------------------------------------
# Step 2 - Dynamic test data (country / city / listing)
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "  FETCHING TEST DATA FROM DATABASE" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray

$dataCode = "`$co=App\Models\MasterCountry::first();`$ci=App\Models\MasterCity::where('status',true)->first();`$li=App\Models\Listing::where('status',true)->where('verification_status','approved')->first();echo json_encode(['country_code'=>`$co?`$co->code:null,'city_id'=>`$ci?`$ci->id:null,'listing_uuid'=>`$li?`$li->uuid:null,'listing_qr_slug'=>`$li?`$li->qr_slug:null]);"
$dataJson = Invoke-Tinker $dataCode

try {
    $testData = $dataJson | ConvertFrom-Json
} catch {
    Write-Host "WARNING: Could not parse test data JSON - dynamic endpoints will be skipped." -ForegroundColor Yellow
    Write-Host "Raw output: $dataJson"
    $testData = [PSCustomObject]@{ country_code = $null; city_id = $null; listing_uuid = $null; listing_qr_slug = $null }
}

$COUNTRY_CODE    = $testData.country_code
$CITY_ID         = $testData.city_id
$LISTING_UUID    = $testData.listing_uuid
$LISTING_QR_SLUG = $testData.listing_qr_slug

Write-Host "  country_code    : $(if ($COUNTRY_CODE)    { $COUNTRY_CODE    } else { '(none)' })"
Write-Host "  city_id         : $(if ($CITY_ID)         { $CITY_ID         } else { '(none)' })"
Write-Host "  listing_uuid    : $(if ($LISTING_UUID)    { $LISTING_UUID    } else { '(none)' })"
Write-Host "  listing_qr_slug : $(if ($LISTING_QR_SLUG) { $LISTING_QR_SLUG } else { '(none - generate QR first)' })"

# ------------------------------------------------------------------------------
# HMAC header builder
#
# String-to-sign format (matches HmacAuthentication middleware exactly):
#
#   METHOD\n
#   timestamp\n
#   nonce\n
#   pathInfo      <- $request->getPathInfo()  PATH ONLY, no query string
#   bodyHash      <- SHA-256 of raw request body (empty string for GET)
#
# ------------------------------------------------------------------------------
function New-HmacHeaders {
    param (
        [string]$Method     = "GET",
        [string]$SignPath,          # URL path only - NO query string
        [string]$ApiKey,
        [string]$SecretKey,
        [string]$Body       = ""
    )

    $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $nonce     = [System.Guid]::NewGuid().ToString("N")   # 32-char hex, satisfies >= 8

    $sha      = [System.Security.Cryptography.SHA256]::Create()
    $bodyHash = [System.BitConverter]::ToString(
        $sha.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($Body))
    ).Replace("-", "").ToLower()

    # Exact same implode("\n", [...]) as the middleware
    $stringToSign = "$Method`n$timestamp`n$nonce`n$SignPath`n$bodyHash"

    $hmac     = New-Object System.Security.Cryptography.HMACSHA256
    $hmac.Key = [System.Text.Encoding]::UTF8.GetBytes($SecretKey)
    $sig      = [System.BitConverter]::ToString(
        $hmac.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($stringToSign))
    ).Replace("-", "").ToLower()

    return @{
        "X-Api-Key"   = $ApiKey
        "X-Timestamp" = "$timestamp"
        "X-Nonce"     = $nonce
        "X-Signature" = $sig
    }
}

# ------------------------------------------------------------------------------
# Test runner - runs a single endpoint and accumulates result
# ------------------------------------------------------------------------------
$script:results = [System.Collections.Generic.List[hashtable]]::new()

function Invoke-EndpointTest {
    param (
        [string]$Name,
        [string]$SignPath,          # path only (no query string) - used for signing
        [string]$QueryString = ""   # e.g. "?per_page=20" - appended to URL only
    )

    $url     = $BASE_URL + $SignPath + $QueryString
    $headers = New-HmacHeaders -Method "GET" -SignPath $SignPath `
                               -ApiKey $API_KEY -SecretKey $SECRET_KEY

    Write-Host ""
    Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
    Write-Host ("  [{0:D2}] {1}" -f ($script:results.Count + 1), $Name) -ForegroundColor Cyan
    Write-Host "  URL : $url"

    try {
        $resp = Invoke-RestMethod -Method GET -Uri $url -Headers $headers `
                                  -ErrorAction Stop

        $script:results.Add(@{ Name = $Name; Status = "PASS" })
        Write-Host "  STATUS  : PASS" -ForegroundColor Green
        Write-Host "  success : $($resp.success)"

        if ($resp.PSObject.Properties["message"] -and $resp.message) {
            Write-Host "  message : $($resp.message)"
        }

        if ($null -ne $resp.data) {
            if ($resp.data -is [System.Array] -or $resp.data -is [System.Collections.IEnumerable]) {
                $arr = @($resp.data)
                Write-Host "  data.count : $($arr.Count)"
                if ($arr.Count -gt 0) {
                    $preview = $arr[0] | ConvertTo-Json -Depth 2 -Compress
                    if ($preview.Length -gt 280) { $preview = $preview.Substring(0, 280) + "..." }
                    Write-Host "  data[0]    : $preview"
                }
            } else {
                $preview = $resp.data | ConvertTo-Json -Depth 2 -Compress
                if ($preview.Length -gt 280) { $preview = $preview.Substring(0, 280) + "..." }
                Write-Host "  data : $preview"
            }
        }

    } catch {
        $script:results.Add(@{ Name = $Name; Status = "FAIL" })
        Write-Host "  STATUS : FAIL" -ForegroundColor Red

        $errDetail = ""
        try {
            if ($_.Exception.Response) {
                $stream    = $_.Exception.Response.GetResponseStream()
                $reader    = New-Object System.IO.StreamReader($stream)
                $errDetail = $reader.ReadToEnd()
            } else {
                $errDetail = $_.Exception.Message
            }
        } catch {
            $errDetail = $_.Exception.Message
        }
        Write-Host "  Error  : $errDetail" -ForegroundColor Red
    }
}

function Skip-EndpointTest {
    param ([string]$Name, [string]$Reason)
    Write-Host ""
    Write-Host ("  [SKIP] {0} - {1}" -f $Name, $Reason) -ForegroundColor Yellow
    $script:results.Add(@{ Name = $Name; Status = "SKIP" })
}

function Invoke-PostTest {
    param (
        [string]$Name,
        [string]$SignPath,
        [hashtable]$BodyData,
        [int]$ExpectStatus = 201
    )

    $bodyJson = $BodyData | ConvertTo-Json -Compress
    $headers  = New-HmacHeaders -Method "POST" -SignPath $SignPath `
                                -ApiKey $API_KEY -SecretKey $SECRET_KEY -Body $bodyJson
    $headers["Content-Type"] = "application/json"

    $url = $BASE_URL + $SignPath

    Write-Host ""
    Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
    Write-Host ("  [{0:D2}] {1}" -f ($script:results.Count + 1), $Name) -ForegroundColor Cyan
    Write-Host "  URL  : $url"
    Write-Host "  BODY : $bodyJson"

    try {
        $resp = Invoke-RestMethod -Method POST -Uri $url -Headers $headers `
                                  -Body $bodyJson -ErrorAction Stop

        $script:results.Add(@{ Name = $Name; Status = "PASS" })
        Write-Host "  STATUS  : PASS" -ForegroundColor Green
        Write-Host "  success : $($resp.success)"
        Write-Host "  message : $($resp.message)"
        if ($resp.data) {
            Write-Host "  data    : $($resp.data | ConvertTo-Json -Compress)"
        }
    } catch {
        $script:results.Add(@{ Name = $Name; Status = "FAIL" })
        Write-Host "  STATUS : FAIL" -ForegroundColor Red

        $errDetail = ""
        try {
            if ($_.Exception.Response) {
                $stream    = $_.Exception.Response.GetResponseStream()
                $reader    = New-Object System.IO.StreamReader($stream)
                $errDetail = $reader.ReadToEnd()
            } else {
                $errDetail = $_.Exception.Message
            }
        } catch { $errDetail = $_.Exception.Message }
        Write-Host "  Error  : $errDetail" -ForegroundColor Red
    }
}

# ------------------------------------------------------------------------------
# Test Suite
# ------------------------------------------------------------------------------
Write-Host ""
Write-Host "============================================================" -ForegroundColor White
Write-Host "  TATKAL DOCTOR - HMAC API TEST SUITE" -ForegroundColor White
Write-Host "  Base URL : $BASE_URL" -ForegroundColor White
Write-Host "============================================================" -ForegroundColor White

# 1. Countries
Invoke-EndpointTest `
    -Name        "GET /api/v1/countries" `
    -SignPath     "/api/v1/countries"

# 2. Cities by country code
if ($COUNTRY_CODE) {
    Invoke-EndpointTest `
        -Name     "GET /api/v1/cities/{countryCode} [$COUNTRY_CODE]" `
        -SignPath  "/api/v1/cities/$COUNTRY_CODE"
} else {
    Skip-EndpointTest -Name "GET /api/v1/cities/{countryCode}" -Reason "no country in DB"
}

# 3. Locations by city id
if ($CITY_ID) {
    Invoke-EndpointTest `
        -Name     "GET /api/v1/locations/{cityId} [id=$CITY_ID]" `
        -SignPath  "/api/v1/locations/$CITY_ID"
} else {
    Skip-EndpointTest -Name "GET /api/v1/locations/{cityId}" -Reason "no active city in DB"
}

# 4. Services
Invoke-EndpointTest `
    -Name        "GET /api/v1/services" `
    -SignPath     "/api/v1/services"

# 5. Qualifications
Invoke-EndpointTest `
    -Name        "GET /api/v1/qualifications" `
    -SignPath     "/api/v1/qualifications"

# 6. Public settings
Invoke-EndpointTest `
    -Name        "GET /api/v1/settings/public" `
    -SignPath     "/api/v1/settings/public"

# 7. Listings search
# Sign with path only; query string appended to URL for the actual request.
Invoke-EndpointTest `
    -Name        "GET /api/v1/listings/search?per_page=20" `
    -SignPath     "/api/v1/listings/search" `
    -QueryString "?per_page=20"

# 8. Listing detail by UUID
if ($LISTING_UUID) {
    Invoke-EndpointTest `
        -Name     "GET /api/v1/listings/{uuid}" `
        -SignPath  "/api/v1/listings/$LISTING_UUID"
} else {
    Skip-EndpointTest -Name "GET /api/v1/listings/{uuid}" -Reason "no approved+active listing in DB"
}

# 9. Listing detail by QR slug
if ($LISTING_QR_SLUG) {
    Invoke-EndpointTest `
        -Name     "GET /api/v1/listings/slug/{qrSlug} [$LISTING_QR_SLUG]" `
        -SignPath  "/api/v1/listings/slug/$LISTING_QR_SLUG"
} else {
    Skip-EndpointTest -Name "GET /api/v1/listings/slug/{qrSlug}" -Reason "no qr_slug on any approved listing (approve a listing and click Generate QR first)"
}

# 10. Intake — doctor self-registration submitted by Solution
if ($CITY_ID) {
    Invoke-PostTest `
        -Name      "POST /api/v1/listings/intake (solution registration)" `
        -SignPath   "/api/v1/listings/intake" `
        -BodyData   @{
            doctor_name         = "Dr. Test Intake"
            email               = "test.intake@example.com"
            mobile              = "+91 98000 00001"
            clinic_name         = "Test Clinic"
            address             = "123 Test Street, Test Nagar"
            city_id             = [int]$CITY_ID
            registration_number = "REG-TEST-001"
            services            = @()
            qualifications      = @()
        }
} else {
    Skip-EndpointTest -Name "POST /api/v1/listings/intake" -Reason "no active city in DB"
}

# ------------------------------------------------------------------------------
# Summary
# ------------------------------------------------------------------------------
$total   = $script:results.Count
$passed  = @($script:results | Where-Object { $_.Status -eq "PASS" }).Count
$failed  = @($script:results | Where-Object { $_.Status -eq "FAIL" }).Count
$skipped = @($script:results | Where-Object { $_.Status -eq "SKIP" }).Count

Write-Host ""
Write-Host "============================================================" -ForegroundColor White
Write-Host "  SUMMARY" -ForegroundColor White
Write-Host "============================================================" -ForegroundColor White

foreach ($r in $script:results) {
    $color = switch ($r.Status) {
        "PASS"  { "Green"  }
        "FAIL"  { "Red"    }
        default { "Yellow" }
    }
    Write-Host ("  [{0,-4}]  {1}" -f $r.Status, $r.Name) -ForegroundColor $color
}

Write-Host ""
Write-Host ("  Total tested : {0}" -f $total)
Write-Host ("  Passed       : {0}" -f $passed)  -ForegroundColor $(if ($passed  -gt 0) { "Green" } else { "White" })
Write-Host ("  Failed       : {0}" -f $failed)  -ForegroundColor $(if ($failed  -gt 0) { "Red"   } else { "White" })
if ($skipped -gt 0) {
    Write-Host ("  Skipped      : {0}" -f $skipped) -ForegroundColor Yellow
}
Write-Host ""
