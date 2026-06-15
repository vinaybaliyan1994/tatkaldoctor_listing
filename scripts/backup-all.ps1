#Requires -Version 5.1
<#
.SYNOPSIS
    Run all 3 project backup scripts then print a summary.
    Output: C:\tatkaldoctor_backups\YYYY-MM-DD\
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Continue'

$ScriptsDir = $PSScriptRoot
$Date       = Get-Date -Format "yyyy-MM-dd"
$BackupRoot = "C:\tatkaldoctor_backups\$Date"

Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "   TatkalDoctor -- Full Backup  [$Date]" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

$Scripts = @(
    "$ScriptsDir\backup-doctor-listing.ps1",
    "$ScriptsDir\backup-solution.ps1",
    "$ScriptsDir\backup-whatsapp.ps1"
)

$Results = @()

foreach ($Script in $Scripts) {
    $Name = [System.IO.Path]::GetFileNameWithoutExtension($Script)
    Write-Host "----------------------------------------------" -ForegroundColor DarkGray
    try {
        & $Script
        $Results += [PSCustomObject]@{ Script = $Name; Status = "OK" }
    } catch {
        Write-Host "  ERROR in ${Name}: $_" -ForegroundColor Red
        $Results += [PSCustomObject]@{ Script = $Name; Status = "FAILED" }
    }
    Write-Host ""
}

Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "   Backup Summary" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan

foreach ($r in $Results) {
    if ($r.Status -eq "OK") {
        Write-Host ("  [OK   ] " + $r.Script) -ForegroundColor Green
    } else {
        Write-Host ("  [FAIL ] " + $r.Script) -ForegroundColor Red
    }
}
Write-Host ""

if (Test-Path $BackupRoot) {
    $TotalBytes = (Get-ChildItem $BackupRoot -Recurse -File | Measure-Object -Property Length -Sum).Sum
    $TotalMB    = [math]::Round($TotalBytes / 1MB, 1)
    Write-Host "  Total backup size: $TotalMB MB" -ForegroundColor DarkCyan
    Write-Host "  Location: $BackupRoot" -ForegroundColor DarkCyan
}
Write-Host ""

$Failed = $Results | Where-Object { $_.Status -ne "OK" }
if ($Failed) {
    Write-Host "  Some backups FAILED -- check output above." -ForegroundColor Red
    exit 1
} else {
    Write-Host "  All backups completed successfully." -ForegroundColor Green
    exit 0
}
