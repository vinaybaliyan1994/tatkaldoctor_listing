#Requires -Version 5.1
<#
.SYNOPSIS
    Backup whatsapp: DB dump + uploads + logs (last 7 days).
    Output: C:\tatkaldoctor_backups\YYYY-MM-DD\whatsapp\
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ProjectRoot = "C:\wamp64\www\tatkaldoctor.whatsapp.com"
$ProjectName = "whatsapp"
$DbName      = "tatkaldoctor_whatsapp_db"
$DbHost      = "127.0.0.1"
$DbPort      = "3306"
$DbUser      = "root"
$DbPassword  = ""
$MysqlDump   = "C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe"
$BackupRoot  = "C:\tatkaldoctor_backups"

$Date      = Get-Date -Format "yyyy-MM-dd"
$Timestamp = Get-Date -Format "HHmmss"
$OutDir    = "$BackupRoot\$Date\$ProjectName"

New-Item -ItemType Directory -Force -Path $OutDir | Out-Null

Write-Host "[$ProjectName] Backup started -> $OutDir" -ForegroundColor Cyan

# 1. DB dump
Write-Host "  [1/4] Dumping database $DbName ..." -ForegroundColor Yellow
$SqlFile  = "$OutDir\db_${Timestamp}.sql"
$DumpArgs = @("--host=$DbHost", "--port=$DbPort", "--user=$DbUser", "--single-transaction", "--routines", "--triggers", $DbName)
if ($DbPassword -ne "") { $DumpArgs = @("--password=$DbPassword") + $DumpArgs }
& $MysqlDump @DumpArgs | Out-File -FilePath $SqlFile -Encoding utf8
if ($LASTEXITCODE -ne 0) { throw "mysqldump failed with exit code $LASTEXITCODE" }
$SqlSizeMB = [math]::Round((Get-Item $SqlFile).Length / 1MB, 2)
Write-Host "    -> $SqlFile ($SqlSizeMB MB)" -ForegroundColor Green

# 2. Uploads
Write-Host "  [2/4] Copying uploads ..." -ForegroundColor Yellow
$UploadsDir = "$ProjectRoot\storage\app\public"
if (Test-Path $UploadsDir) {
    robocopy $UploadsDir "$OutDir\uploads" /E /NFL /NDL /NJH /NJS /nc /ns /np /XD "vendor" "node_modules" 2>&1 | Out-Null
    $FileCount = @(Get-ChildItem "$OutDir\uploads" -Recurse -File -ErrorAction SilentlyContinue).Count
    Write-Host "    -> $FileCount file(s) copied" -ForegroundColor Green
} else {
    Write-Host "    -> storage/app/public not found, skipped" -ForegroundColor DarkGray
}

# 3. .env.example
Write-Host "  [3/4] Copying .env.example ..." -ForegroundColor Yellow
if (Test-Path "$ProjectRoot\.env.example") {
    Copy-Item "$ProjectRoot\.env.example" "$OutDir\.env.example"
    Write-Host "    -> copied" -ForegroundColor Green
} else {
    Write-Host "    -> not found, skipped" -ForegroundColor DarkGray
}

# 4. Logs (last 7 days)
Write-Host "  [4/4] Copying recent logs ..." -ForegroundColor Yellow
$LogsDir    = "$ProjectRoot\storage\logs"
$LogsOutDir = "$OutDir\logs"
New-Item -ItemType Directory -Force -Path $LogsOutDir | Out-Null
if (Test-Path $LogsDir) {
    $Cutoff   = (Get-Date).AddDays(-7)
    $LogFiles = @(Get-ChildItem $LogsDir -File -Recurse | Where-Object { $_.LastWriteTime -gt $Cutoff })
    foreach ($f in $LogFiles) { Copy-Item $f.FullName $LogsOutDir -ErrorAction SilentlyContinue }
    Write-Host "    -> $($LogFiles.Count) log file(s) copied (last 7 days)" -ForegroundColor Green
} else {
    Write-Host "    -> storage/logs not found, skipped" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "[$ProjectName] Backup complete -> $OutDir" -ForegroundColor Cyan
