$ErrorActionPreference = "Stop"

cd "C:\wamp64\www\doctor_listing"

$baseUrl = "http://127.0.0.1:8000"

Write-Host "Fetching active API client..."

$clientJson = php artisan tinker --execute="
`$client = App\Models\Client::where('status', 'active')->first();
if (! `$client) { echo json_encode(['error' => 'No active client found']); return; }
echo json_encode([
    'api_key' => `$client->api_key,
    'secret_key' => `$client->getDecryptedSecretKey(),
]);
"

$client = $clientJson | ConvertFrom-Json

if ($client.error) {
    Write-Host $client.error
    Read-Host "Press Enter to exit"
    exit
}

Write-Host "API Key:" $client.api_key

function New-HmacHeaders {
    param (
        [string] $Method,
        [string] $Path,
        [string] $SecretKey,
        [string] $ApiKey,
        [string] $Body = ""
    )

    $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $nonce = [guid]::NewGuid().ToString("N")
    Write-Host "Timestamp:" $timestamp

    $sha = [System.Security.Cryptography.SHA256]::Create()
    $bodyHash = [System.BitConverter]::ToString(
        $sha.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($Body))
    ).Replace("-", "").ToLower()

    $stringToSign = "$Method`n$timestamp`n$nonce`n$Path`n$bodyHash"

    $hmac = New-Object System.Security.Cryptography.HMACSHA256
    $hmac.Key = [System.Text.Encoding]::UTF8.GetBytes($SecretKey)

    $signature = [System.BitConverter]::ToString(
        $hmac.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($stringToSign))
    ).Replace("-", "").ToLower()

    return @{
        "X-Api-Key" = $ApiKey
        "X-Timestamp" = "$timestamp"
        "X-Nonce" = $nonce
        "X-Signature" = $signature
    }
}

Write-Host ""
Write-Host "Testing listings search..."

$path = "/api/v1/listings/search"
$queryString = "?per_page=20"
$url = "$baseUrl$path$queryString"
$signaturePath = $path

Write-Host "Search URL:" $url

$searchHeaders = New-HmacHeaders `
    -Method "GET" `
    -Path $signaturePath `
    -SecretKey $client.secret_key `
    -ApiKey $client.api_key

try {
    $searchResponse = Invoke-RestMethod `
        -Method GET `
        -Uri $url `
        -Headers $searchHeaders
} catch {
    Write-Host ""
    Write-Host "Search request failed:"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        Write-Host $reader.ReadToEnd()
    } else {
        Write-Host $_.Exception.Message
    }
    Read-Host "Press Enter to exit"
    exit
}

Write-Host ""
Write-Host "Search Response:"
$searchResponse | ConvertTo-Json -Depth 10

if (-not $searchResponse.data -or $searchResponse.data.Count -eq 0) {
    Write-Host ""
    Write-Host "No active listing found for detail test."
    Read-Host "Press Enter to exit"
    exit
}

$uuid = $searchResponse.data[0].uuid

Write-Host ""
Write-Host "Testing listing detail UUID:" $uuid

$detailPath = "/api/v1/listings/$uuid"
$detailUrl = "$baseUrl$detailPath"

Write-Host "Detail URL:" $detailUrl

$detailHeaders = New-HmacHeaders `
    -Method "GET" `
    -Path $detailPath `
    -SecretKey $client.secret_key `
    -ApiKey $client.api_key

try {
    $detailResponse = Invoke-RestMethod `
        -Method GET `
        -Uri $detailUrl `
        -Headers $detailHeaders
} catch {
    Write-Host ""
    Write-Host "Detail request failed:"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        Write-Host $reader.ReadToEnd()
    } else {
        Write-Host $_.Exception.Message
    }
    Read-Host "Press Enter to exit"
    exit
}

Write-Host ""
Write-Host "Detail Response:"
$detailResponse | ConvertTo-Json -Depth 10

Read-Host "Press Enter to exit"
