<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdminActivityLog::query()
            ->with('user')
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action')->toString().'%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from')->toString());
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to')->toString());
        }

        $logs = $query->paginate(30)->withQueryString();

        $actions = AdminActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->limit(50)
            ->pluck('action');

        return view('admin.activity-log.index', compact('logs', 'actions'));
    }
}
