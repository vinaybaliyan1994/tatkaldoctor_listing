<?php

namespace App\Services;

use App\Models\ListingAuditLog;
use Illuminate\Support\Facades\Auth;

class ListingAuditService
{
    public function log(
        int $listingId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $remarks = null
    ): ListingAuditLog {
        return ListingAuditLog::create([
            'listing_id' => $listingId,
            'action'     => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'remarks'    => $remarks,
            'changed_by' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
