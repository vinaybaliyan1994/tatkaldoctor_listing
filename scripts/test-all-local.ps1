#Requires -Version 5.1
<#
.SYNOPSIS
    Master test script: runs system:check on all 4 projects + health checks.
    Prints PASS/FAIL/SKIP for each check.
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Continue'

$Projects = @(
    @{ Name = "doctor-listing"; Dir = "C:\wamp64\www\doctor_listing";                     Port = 8000; HealthPath = "/api/v1/health" },
    @{ Name = "solution";       Dir = "C:\wamp64\www\solution.tatkaldoctor.com";           Port = 8001; HealthPath = "/health" },
    @{ Name = "whatsapp";       Dir = "C:\wamp64\www\tatkaldoctor.whatsapp.com";           Port = 8002; HealthPath = "/health" },
    @{ Name = "public-website"; Dir = "C:\wamp64\www\taktaldoctor.com";                   Port = 8003; HealthPath = "/health" }
)

# Auto-detect PHP: prefer PATH, fall back to WampServer's php8.2.x
$PhpExe = try { (Get-Command php -ErrorAction Stop).Source } catch { $null }
if (-not $PhpExe) {
    $PhpExe = Get-ChildItem "C:\wamp64\bin\php" -Directory |
        Where-Object { $_.Name -like "php8.2.*" } |
        Sort-Object Name -Descending |
        Select-Object -First 1 -ExpandProperty FullName
    if ($PhpExe) { $PhpExe = "$PhpExe\php.exe" }
}
$Results = @()

function Test-Result($Name, $Status, $Detail) {
    $Color = switch ($Status) {
        "PASS" { "Green" }
        "WARN" { "Yellow" }
        "SKIP" { "DarkGray" }
        default { "Red" }
    }
    Write-Host ("  [{0,-4}] {1,-45} {2}" -f $Status, $Name, $Detail) -ForegroundColor $Color
    [PSCustomObject]@{ Name = $Name; Status = $Status; Detail = $Detail }
}

Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "   TatkalDoctor -- Local Test Suite" -ForegroundColor Cyan
Write-Host "   $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

# ── PHP availability ────────────────────────────────────────────────────────
Write-Host "[ PHP ]" -ForegroundColor DarkCyan
if ($PhpExe -and (Test-Path $PhpExe)) {
    $PhpVer = & $PhpExe -r "echo PHP_VERSION;" 2>&1
    $Results += Test-Result "PHP executable" "PASS" "v$PhpVer ($PhpExe)"
} else {
    $Results += Test-Result "PHP executable" "FAIL" "PHP not found in PATH or C:\wamp64\bin\php\php8.2.*"
    $PhpExe = $null
}
Write-Host ""

# ── Health endpoints ────────────────────────────────────────────────────────
Write-Host "[ Health Checks ]" -ForegroundColor DarkCyan
foreach ($p in $Projects) {
    $Url = "http://127.0.0.1:$($p.Port)$($p.HealthPath)"
    try {
        $r = Invoke-RestMethod -Uri $Url -TimeoutSec 5 -ErrorAction Stop
        if ($r.status -eq "running" -or $r.status -eq "ok" -or $r.success -eq $true) {
            $Results += Test-Result "$($p.Name) /health" "PASS" "HTTP 200 ($Url)"
        } else {
            $Results += Test-Result "$($p.Name) /health" "FAIL" "Unexpected status: $($r.status)"
        }
    } catch {
        $Results += Test-Result "$($p.Name) /health" "FAIL" "Could not reach $Url -- is the server running?"
    }
}
Write-Host ""

# ── php artisan system:check ────────────────────────────────────────────────
Write-Host "[ system:check (artisan) ]" -ForegroundColor DarkCyan
foreach ($p in $Projects) {
    if (-not (Test-Path $p.Dir)) {
        $Results += Test-Result "$($p.Name) system:check" "SKIP" "Directory not found: $($p.Dir)"
        continue
    }
    if (-not (Test-Path $PhpExe)) {
        $Results += Test-Result "$($p.Name) system:check" "SKIP" "PHP not found"
        continue
    }
    Push-Location $p.Dir
    try {
        $Output = & $PhpExe artisan system:check 2>&1
        $ExitCode = $LASTEXITCODE
        if ($ExitCode -eq 0) {
            $Results += Test-Result "$($p.Name) system:check" "PASS" "All checks passed"
        } else {
            $FailLines = @($Output | Where-Object { $_ -match "FAIL" })
            $Detail = if ($FailLines.Count -gt 0) { "$($FailLines.Count) check(s) failed" } else { "exit $ExitCode" }
            $Results += Test-Result "$($p.Name) system:check" "FAIL" $Detail
            Write-Host ($Output | Out-String).Trim() -ForegroundColor DarkGray
        }
    } catch {
        $Results += Test-Result "$($p.Name) system:check" "FAIL" $_.Exception.Message
    } finally {
        Pop-Location
    }
}
Write-Host ""

# ── Migration status ────────────────────────────────────────────────────────
Write-Host "[ Migration Status ]" -ForegroundColor DarkCyan
foreach ($p in $Projects) {
    if (-not (Test-Path $p.Dir) -or -not (Test-Path $PhpExe)) {
        $Results += Test-Result "$($p.Name) migrations" "SKIP" "Prerequisite missing"
        continue
    }
    Push-Location $p.Dir
    try {
        $Output  = & $PhpExe artisan migrate:status 2>&1 | Out-String
        $Pending = @($Output -split "`n" | Where-Object { $_ -match "Pending" })
        if ($Pending.Count -gt 0) {
            $Results += Test-Result "$($p.Name) migrations" "WARN" "$($Pending.Count) pending migration(s)"
        } else {
            $Results += Test-Result "$($p.Name) migrations" "PASS" "All migrations run"
        }
    } catch {
        $Results += Test-Result "$($p.Name) migrations" "FAIL" $_.Exception.Message
    } finally {
        Pop-Location
    }
}
Write-Host ""

# ── Summary ─────────────────────────────────────────────────────────────────
$PassCount = @($Results | Where-Object { $_.Status -eq "PASS" }).Count
$WarnCount = @($Results | Where-Object { $_.Status -eq "WARN" }).Count
$FailCount = @($Results | Where-Object { $_.Status -eq "FAIL" }).Count
$SkipCount = @($Results | Where-Object { $_.Status -eq "SKIP" }).Count
$Total     = $Results.Count

Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "   Summary: $PassCount/$Total PASS  |  $WarnCount WARN  |  $FailCount FAIL  |  $SkipCount SKIP" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan

if ($FailCount -gt 0) {
    Write-Host ""
    Write-Host "  FAILED checks:" -ForegroundColor Red
    $Results | Where-Object { $_.Status -eq "FAIL" } | ForEach-Object {
        Write-Host "    - $($_.Name): $($_.Detail)" -ForegroundColor Red
    }
}
if ($WarnCount -gt 0) {
    Write-Host ""
    Write-Host "  WARNINGS:" -ForegroundColor Yellow
    $Results | Where-Object { $_.Status -eq "WARN" } | ForEach-Object {
        Write-Host "    - $($_.Name): $($_.Detail)" -ForegroundColor Yellow
    }
}

Write-Host ""
if ($FailCount -eq 0) {
    Write-Host "  System is ready." -ForegroundColor Green
    exit 0
} else {
    Write-Host "  Fix failed checks before production." -ForegroundColor Red
    exit 1
}
