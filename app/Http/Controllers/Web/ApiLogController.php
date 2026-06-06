<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ApiLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if (! Schema::hasTable('api_logs')) {
            $logs = new LengthAwarePaginator(collect(), 0, 25);
            $clients = Client::orderBy('name')->get(['id', 'name']);

            return view('api-logs.index', compact('logs', 'clients'));
        }

        $query = ApiLog::with('client')->latest('created_at');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('success', $request->status === 'success');
        }

        if ($request->filled('endpoint')) {
            $query->where('endpoint', 'like', '%'.$request->endpoint.'%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(25)->withQueryString();
        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('api-logs.index', compact('logs', 'clients'));
    }

    public function show(Request $request, ApiLog $apiLog): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $apiLog->load('client');

        return view('api-logs.show', compact('apiLog'));
    }
}
