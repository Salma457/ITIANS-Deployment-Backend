<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class JobsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except([
            'index',           
            'show',            
            'openJobs',        
            'search'       
        ]);

        $this->middleware(['auth', 'role:employer,admin'])->only([
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            'changeStatus',
            'bulkUpdateStatus',
            'statistics',
            'expiringSoon'
        ]);
    }

 
    public function index(Request $request): View|JsonResponse
    {
        $query = Job::query();

       
        if (!auth()->check() || auth()->user()->role === 'itian') {
            $query->where('status', Job::STATUS_OPEN)
                  ->where('application_deadline', '>=', now()); 
        }

        if (auth()->check() && auth()->user()->role === 'employer') {
            $query->where('employer_id', auth()->id());
        }


        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->filled('job_location')) {
            $query->where('job_location', $request->job_location);
        }

        if ($request->filled('status') && auth()->check() && in_array(auth()->user()->role, ['employer', 'admin'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('min_salary')) {
            $query->where('salary_range_min', '>=', $request->min_salary);
        }

        if ($request->filled('max_salary')) {
            $query->where('salary_range_max', '<=', $request->max_salary);
        }

        $jobs = $query->orderBy('posted_date', 'desc')
                     ->paginate(15)
                     ->appends($request->query());

        if ($request->expectsJson()) {
            return response()->json([
                'jobs' => $jobs,
                'filters' => [
                    'job_types' => Job::getJobTypes(),
                    'locations' => Job::getLocations(),
                ]
            ]);
        }

        $jobTypes = Job::getJobTypes();
        $locations = Job::getLocations();

        return view('jobs.index', compact('jobs', 'jobTypes', 'locations'));
    }


    public function show(Job $job): View|JsonResponse
    {
        if (!auth()->check() || auth()->user()->role === 'itian') {
            if ($job->status !== Job::STATUS_OPEN || $job->application_deadline < now()) {
                abort(404, 'Job not found or no longer available');
            }
        }

        if (auth()->check() && auth()->user()->role === 'employer') {
            if ($job->employer_id !== auth()->id()) {
                abort(403, 'Unauthorized to view this job');
            }
        }

        if (!auth()->check() || auth()->user()->role === 'itian') {
            $job->increment('views_count');
        }

        if (request()->expectsJson()) {
            return response()->json($job);
        }

        return view('jobs.show', compact('job'));
    }


    public function search(Request $request): JsonResponse
    {
        $query = Job::where('status', Job::STATUS_OPEN)
                   ->where('application_deadline', '>=', now());

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('job_title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->filled('job_location')) {
            $query->where('job_location', $request->job_location);
        }

        if ($request->filled('salary_min')) {
            $query->where('salary_range_min', '>=', $request->salary_min);
        }

        if ($request->filled('salary_max')) {
            $query->where('salary_range_max', '<=', $request->salary_max);
        }

        $jobs = $query->orderBy('posted_date', 'desc')
                     ->paginate($request->get('per_page', 15));

        return response()->json($jobs);
    }


    public function openJobs(Request $request): View|JsonResponse
    {
        $query = Job::where('status', Job::STATUS_OPEN)
                   ->where('application_deadline', '>=', now());

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->filled('job_location')) {
            $query->where('job_location', $request->job_location);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $jobs = $query->orderBy('posted_date', 'desc')
                     ->paginate(15)
                     ->appends($request->query());

        if ($request->expectsJson()) {
            return response()->json($jobs);
        }

        $jobTypes = Job::getJobTypes();
        $locations = Job::getLocations();

        return view('jobs.public', compact('jobs', 'jobTypes', 'locations'));
    }

    // ============== EMPLOYER/ADMIN ONLY METHODS ==============

    public function create(): View
    {
        $jobTypes = Job::getJobTypes();
        $statuses = Job::getStatuses();
        $locations = Job::getLocations();
        
        return view('jobs.create', compact('jobTypes', 'statuses', 'locations'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'job_location' => ['nullable', Rule::in(Job::getLocations())],
            'job_type' => ['nullable', Rule::in(Job::getJobTypes())],
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'currency' => 'nullable|string|max:10',
            'posted_date' => 'nullable|date',
            'application_deadline' => 'nullable|date|after:today',
            'status' => ['nullable', Rule::in(Job::getStatuses())],
        ]);

        if (auth()->user()->role === 'employer') {
            $validated['employer_id'] = auth()->id();
        }

        $validated['posted_date'] = $validated['posted_date'] ?? now();
        $validated['status'] = $validated['status'] ?? Job::STATUS_PENDING;
        $validated['views_count'] = 0;

        $job = Job::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job created successfully',
                'job' => $job
            ], 201);
        }

        return redirect()->route('jobs.show', $job->id)
         ->with('success', 'Job created successfully!');
    }


    public function edit(Job $job): View
    {
        if (auth()->user()->role === 'employer' && $job->employer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $jobTypes = Job::getJobTypes();
        $statuses = Job::getStatuses();
        $locations = Job::getLocations();
        
        return view('jobs.edit', compact('job', 'jobTypes', 'statuses', 'locations'));
    }


    public function update(Request $request, Job $job): RedirectResponse|JsonResponse
    {
        if (auth()->user()->role === 'employer' && $job->employer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'job_location' => ['nullable', Rule::in(Job::getLocations())],
            'job_type' => ['nullable', Rule::in(Job::getJobTypes())],
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'currency' => 'nullable|string|max:10',
            'posted_date' => 'nullable|date',
            'application_deadline' => 'nullable|date',
            'status' => ['nullable', Rule::in(Job::getStatuses())],
        ]);

        $job->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job updated successfully',
                'job' => $job
            ]);
        }

        return redirect()->route('jobs.show', $job->id)
                        ->with('success', 'Job updated successfully!');
    }


    public function destroy(Job $job): RedirectResponse|JsonResponse
    {
        if (auth()->user()->role === 'employer' && $job->employer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $job->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Job deleted successfully'
            ]);
        }

        return redirect()->route('jobs.index')
                        ->with('success', 'Job deleted successfully!');
    }


    public function changeStatus(Request $request, Job $job): RedirectResponse|JsonResponse
    {
        // Employers can only change status of their own jobs
        if (auth()->user()->role === 'employer' && $job->employer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(Job::getStatuses())]
        ]);

        $job->update(['status' => $validated['status']]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job status updated successfully',
                'status' => $job->status
            ]);
        }

        return back()->with('success', 'Job status updated successfully!');
    }


    public function statistics(): JsonResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $stats = [
            'total_jobs' => Job::count(),
            'open_jobs' => Job::where('status', Job::STATUS_OPEN)->count(),
            'closed_jobs' => Job::where('status', Job::STATUS_CLOSED)->count(),
            'pending_jobs' => Job::where('status', Job::STATUS_PENDING)->count(),
            'total_views' => Job::sum('views_count'),
            'jobs_by_type' => Job::selectRaw('job_type, count(*) as count')
                                 ->groupBy('job_type')
                                 ->pluck('count', 'job_type'),
            'jobs_by_location' => Job::selectRaw('job_location, count(*) as count')
                                     ->groupBy('job_location')
                                     ->pluck('count', 'job_location'),
            'recent_jobs' => Job::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json($stats);
    }
}