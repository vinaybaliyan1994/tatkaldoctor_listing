<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ListingAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $query = ListingAuditLog::with(['listing', 'changedBy'])->latest();

        if ($request->filled('listing_id')) {
            $query->where('listing_id', $request->integer('listing_id'));
        }
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->paginate(30)->withQueryString();
        $actions = ListingAuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $listings = Listing::orderBy('name')->get(['id', 'name']);

        return view('listing-audit-logs.index', compact('logs', 'actions', 'listings'));
    }

    public function show(ListingAuditLog $listingAuditLog): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $listingAuditLog->load(['listing', 'changedBy']);

        return view('listing-audit-logs.show', compact('listingAuditLog'));
    }
}
