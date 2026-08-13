<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        $logs = $query->latest()->paginate(20);

        return view('admin.audit_logs.index', compact('logs'));
    }
}
