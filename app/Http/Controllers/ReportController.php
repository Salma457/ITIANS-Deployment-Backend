<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reporter_user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $report = Report::create($validated);

        return response()->json($report, 201);
    }

    public function index()
    {
        return Report::with(['reporter', 'resolver'])->get();
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'report_status' => 'required|in:Pending,Resolved,Rejected',
            'resolved_by_admin_id' => 'nullable|exists:users,id',
        ]);

        $report = Report::findOrFail($id);
        $report->update($validated);

        return response()->json($report);
    }

    public function destroy($id)
    {
        Report::findOrFail($id)->delete();

        return response()->json(['message' => 'Report deleted']);
    }
}
