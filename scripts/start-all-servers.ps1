#Requires -Version 5.1
<#
.SYNOPSIS
    Start all 4 TatkalDoctor PHP development servers in separate windows.
    Each server runs in its own PowerShell window so you can see its output.
    WampServer (Apache + MySQL) must already be running.

    Ports:
      8000 - doctor-listing
      8001 - solution
      8002 - whatsapp
      8003 - public-website
#>

$PhpExe = try { (Get-Command php -ErrorAction Stop).Source } catch { $null }
if (-not $PhpExe) {
    $PhpExe = Get-ChildItem "C:\wamp64\bin\php" -Directory |
        Where-Object { $_.Name -like "php8.2.*" } |
        Sort-Object Name -Descending |
        Select-Object -First 1 -ExpandProperty FullName
    if ($PhpExe) { $PhpExe = "$PhpExe\php.exe" }
}

if (-not $PhpExe -or -not (Test-Path $PhpExe)) {
    Write-Host "PHP not found. Install PHP or add it to PATH." -ForegroundColor Red
    exit 1
}

$Services = @(
    @{ Name = "doctor-listing";  Dir = "C:\wamp64\www\doctor_listing";                Port = 8000 },
    @{ Name = "solution";        Dir = "C:\wamp64\www\solution.tatkaldoctor.com";      Port = 8001 },
    @{ Name = "whatsapp";        Dir = "C:\wamp64\www\tatkaldoctor.whatsapp.com";      Port = 8002 },
    @{ Name = "public-website";  Dir = "C:\wamp64\www\taktaldoctor.com";              Port = 8003 }
)

Write-Host ""
Write-Host "Starting TatkalDoctor servers..." -ForegroundColor Cyan
Write-Host ""

foreach ($svc in $Services) {
    # Check if something is already listening on the port
    $inUse = Get-NetTCPConnection -LocalPort $svc.Port -State Listen -ErrorAction SilentlyContinue
    if ($inUse) {
        Write-Host "  [SKIP] $($svc.Name) -- port $($svc.Port) already in use" -ForegroundColor Yellow
        continue
    }

    if (-not (Test-Path $svc.Dir)) {
        Write-Host "  [SKIP] $($svc.Name) -- directory not found: $($svc.Dir)" -ForegroundColor DarkGray
        continue
    }

    # Set PHP_CLI_SERVER_WORKERS=4 for concurrent request handling
    $cmd = "Set-Location '$($svc.Dir)'; `$env:PHP_CLI_SERVER_WORKERS=4; & '$PhpExe' artisan serve --port=$($svc.Port) --host=127.0.0.1"
    Start-Process powershell.exe -ArgumentList "-NoExit", "-Command", $cmd `
        -WindowStyle Normal

    Write-Host "  [START] $($svc.Name) -> http://127.0.0.1:$($svc.Port)" -ForegroundColor Green
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "All servers launched. Waiting 4s for startup..." -ForegroundColor Cyan
Start-Sleep -Seconds 4

# Quick health check
Write-Host ""
Write-Host "Health checks:" -ForegroundColor DarkCyan
$HealthPaths = @{
    8000 = "/api/v1/health"
    8001 = "/health"
    8002 = "/health"
    8003 = "/health"
}
foreach ($svc in $Services) {
    $path = $HealthPaths[$svc.Port]
    $url  = "http://127.0.0.1:$($svc.Port)$path"
    try {
        $r = Invoke-RestMethod $url -TimeoutSec 5 -ErrorAction Stop
        Write-Host "  [OK  ] $($svc.Name) ($url)" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] $($svc.Name) ($url) -- may still be starting up" -ForegroundColor Yellow
    }
}
Write-Host ""
