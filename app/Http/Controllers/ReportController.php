<?php

namespace App\Http\Controllers;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Store a newly created report in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'content' => 'required|string', // Content is required and must be a string
        ]);

        // Automatically set reporter_user_id to the authenticated user's ID
        // This ensures the current user is creating the report, enhancing security
        $validated['reporter_user_id'] = Auth::id();

        // Create the report in the database
        $report = Report::create($validated);

        // Return the created report with HTTP status 201 (Created)
        return response()->json($report, 201);
    }

    /**
     * Display a listing of the reports with pagination and filtering.
     * This method is intended for admin use.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get pagination and filter parameters from the request
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);
        $status = $request->input('status', 'all');
        $dateRange = $request->input('date_range', 'all'); // 'all', 'today', 'last_7_days', 'last_30_days'
        $search = $request->input('search', '');
        $reporterType = $request->input('reporter_type', 'all');

        // Build base query with reporter and resolver relationships loaded
        $query = Report::with(['reporter', 'resolver'])
            ->orderBy('created_at', 'desc'); // Order by creation date, newest first

        // Apply status filter if not 'all'
        if ($status !== 'all') {
            $query->where('report_status', $status);
        }

        // Apply date range filter if not 'all'
        if ($dateRange !== 'all') {
            $now = Carbon::now();
            $startDate = null;

            switch ($dateRange) {
                case 'today':
                    $startDate = $now->copy()->startOfDay();
                    break;
                case 'last_7_days': // Matches frontend 'week' filter
                    $startDate = $now->copy()->subDays(7)->startOfDay();
                    break;
                case 'last_30_days': // Matches frontend 'month' filter
                    $startDate = $now->copy()->subDays(30)->startOfDay();
                    break;
            }

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
        }

        // Apply reporter type filter if not 'all'
        if ($reporterType !== 'all') {
            $query->whereHas('reporter', function ($q) use ($reporterType) {
                $q->where('role', $reporterType);
            });
        }

        // Apply search filter if search term is provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', '%' . $search . '%') // Search in report content
                  ->orWhereHas('reporter', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%'); // Search in reporter's name
                  });
            });
        }

        // Execute paginated query
        $reports = $query->paginate($perPage, ['*'], 'page', $page);

        // Return a JSON response with the paginated reports and pagination metadata
        return response()->json([
            'reports' => $reports->items(), // The actual array of report data for the current page
            'pagination' => [ // Pagination metadata
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Find the report by its ID and eager load reporter and resolver relationships
        // If the report is not found, a 404 (Not Found) response will be returned automatically
        $report = Report::with(['reporter', 'resolver'])->findOrFail($id);
        return response()->json($report);
    }

    /**
     * Update the status of the specified report in storage.
     * This method is intended for admin use.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'report_status' => 'required|in:Pending,Resolved,Rejected',
                'resolved_by_admin_id' => 'nullable|exists:users,id',
            ]);

            $report = Report::with(['reporter', 'resolver'])->findOrFail($id);

            if (!isset($request->resolved_by_admin_id) && in_array($request->report_status, ['Resolved', 'Rejected'])) {
                $report->resolved_by_admin_id = Auth::id();
            } else if ($request->report_status === 'Pending') {
                $report->resolved_by_admin_id = null;
            }

            $report->report_status = $request->report_status;
            $report->save();

            // إعادة تحميل العلاقات لضمان استجابة كاملة
            $report->load('reporter', 'resolver');

            return response()->json([
                'message' => 'Application status updated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            \Log::error('خطأ في تحديث حالة التقرير: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ داخلي في السيرفر'], 500);
        }
    }

    /**
     * Remove the specified report from storage.
     * This method is intended for admin use.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the report and delete it
        Report::findOrFail($id)->delete();

        // Return a success message with HTTP status 204 (No Content)
        return response()->json(null, 204);
    }

    // Add a method for 'my-reports' if you decide to have a separate route for users to view their own reports
  
    public function myReports(Request $request)
    {
        $userId = Auth::id();
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);
        $status = $request->input('status', 'all');
        $dateRange = $request->input('date_range', 'all');

        $query = Report::with(['reporter', 'resolver'])
            ->where('reporter_user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('report_status', $status);
        }

        if ($dateRange !== 'all') {
            $now = Carbon::now();
            $startDate = null;

            switch ($dateRange) {
                case 'today':
                    $startDate = $now->copy()->startOfDay();
                    break;
                case 'last_7_days':
                    $startDate = $now->copy()->subDays(7)->startOfDay();
                    break;
                case 'last_30_days':
                    $startDate = $now->copy()->subDays(30)->startOfDay();
                    break;
            }

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
        }

        $reports = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'reports' => $reports->items(),
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ]
        ]);
    }
    

    // Add a method for 'getStats' if you decide to display statistics on the dashboard
    /*
    public function getStats()
    {
        $totalReports = Report::count();
        $pendingReports = Report::where('report_status', 'Pending')->count();
        $resolvedReports = Report::where('report_status', 'Resolved')->count();
        $rejectedReports = Report::where('report_status', 'Rejected')->count();

        return response()->json([
            'total' => $totalReports,
            'pending' => $pendingReports,
            'resolved' => $resolvedReports,
            'rejected' => $rejectedReports,
        ]);
    }
    */
}