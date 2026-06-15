<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SystemCheckCommand extends Command
{
    protected $signature   = 'system:check';
    protected $description = 'Run system dependency checks for doctor-listing service';

    public function handle(): int
    {
        $this->info('');
        $this->info('════════════════════════════════════════');
        $this->info('  TatkalDoctor — doctor-listing checks  ');
        $this->info('════════════════════════════════════════');

        $allPass = true;

        // 1. DB connection
        $allPass = $this->runCheck('DB connection', function () {
            DB::connection()->getPdo();
            $count = DB::table('listings')->count();
            return "connected ({$count} listings)";
        }) && $allPass;

        // 2. Storage symlink
        $allPass = $this->runCheck('Storage symlink', function () {
            $link = public_path('storage');
            if (! file_exists($link)) {
                throw new \RuntimeException('public/storage symlink missing — run: php artisan storage:link');
            }
            return 'symlink exists';
        }) && $allPass;

        // 3. Profile photo path writable
        $allPass = $this->runCheck('Profile photo path writable', function () {
            $dir = storage_path('app/public/profile-photos');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (! is_writable($dir)) {
                throw new \RuntimeException("Directory not writable: {$dir}");
            }
            return 'writable';
        }) && $allPass;

        // 4. QR code path writable
        $allPass = $this->runCheck('QR code path writable', function () {
            $dir = storage_path('app/public/qr-codes');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (! is_writable($dir)) {
                throw new \RuntimeException("Directory not writable: {$dir}");
            }
            return 'writable';
        }) && $allPass;

        // 5. API clients count
        $allPass = $this->runCheck('API clients', function () {
            $count = DB::table('clients')->count();
            if ($count === 0) {
                throw new \RuntimeException('No API clients registered — API auth will fail');
            }
            return "{$count} client(s) registered";
        }) && $allPass;

        // 6. Listings count
        $allPass = $this->runCheck('Listings in DB', function () {
            $count    = DB::table('listings')->count();
            $approved = DB::table('listings')->where('verification_status', 'approved')->count();
            return "{$count} total, {$approved} approved";
        }) && $allPass;

        // 7. WhatsApp business phone configured
        $allPass = $this->runCheck('WhatsApp business phone', function () {
            $phone = config('tatkaldoctor.whatsapp_business_phone', '');
            if (empty($phone) || $phone === '919999999999') {
                return 'WARNING: using placeholder phone number';
            }
            return "configured ({$phone})";
        }) && $allPass;

        $this->info('');
        if ($allPass) {
            $this->info('✅  All checks passed — doctor-listing is ready.');
        } else {
            $this->error('❌  Some checks failed — review above before production.');
        }
        $this->info('');

        return $allPass ? self::SUCCESS : self::FAILURE;
    }

    private function runCheck(string $label, callable $check): bool
    {
        try {
            $detail = $check();
            $this->line("  <fg=green>PASS</> {$label}: <fg=cyan>{$detail}</>");
            return true;
        } catch (\Throwable $e) {
            $this->line("  <fg=red>FAIL</> {$label}: <fg=yellow>{$e->getMessage()}</>");
            return false;
        }
    }
}
