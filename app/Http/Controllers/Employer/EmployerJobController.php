<?php

namespace App\Http\Controllers\Employer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Requests\UpdateJobStatusRequest;
use App\Http\Resources\JobResource;
use App\Http\Resources\JobCollection;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Http\Request;
class EmployerJobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::with(['employer', 'statusChanger']);

        // Handle search (title or search param)
        $searchTerm = $request->filled('search') ? $request->search : ($request->filled('title') ? $request->title : null);
        if ($searchTerm) {
            $query->where('job_title', 'like', '%' . $searchTerm . '%');
        }

        // Handle job type filter
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        // Handle status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Handle location filter
        if ($request->filled('job_location')) {
            $query->where('job_location', $request->job_location);
        }

        // Handle salary range filters
        if ($request->filled('min_salary')) {
            $query->where('salary_range_min', '>=', (float) $request->min_salary);
        }
        if ($request->filled('max_salary')) {
            $query->where('salary_range_max', '<=', (float) $request->max_salary);
        }

        // Handle sorting
        if ($request->filled('sort')) {
            $sortDirection = str_starts_with($request->sort, '-') ? 'desc' : 'asc';
            $sortField = ltrim($request->sort, '-');
            $sortableFields = ['posted_date', 'salary_range_min', 'salary_range_max', 'application_deadline'];
            if (in_array($sortField, $sortableFields)) {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->latest('posted_date');
        }

        // Pagination with default values
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => JobResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function employerJobs(Request $request)
    {
        $user = $request->user();
        $jobs = Job::withTrashed()->where('employer_id', $user->id)->get();
        return response()->json($jobs);
    }

    public function store(StoreJobRequest $request)
    {
        $job = Job::create($request->validated() + [
            'employer_id' => $request->user()->id,
            'posted_date' => now(),
            'status' => Job::STATUS_PENDING
        ]);
        return new JobResource($job->load(['employer', 'statusChanger']));
    }

    public function show(Request $request, Job $job)
    {
        $user = $request->user();
        // Only increment views if the user is NOT admin and NOT the employer who created the job
        if (!$user || ($user->role !== 'admin' && $user->id !== $job->employer_id)) {
            $job->increment('views_count');
        }
        return new JobResource($job->load(['employer', 'statusChanger']));
    }

    public function update(UpdateJobRequest $request, Job $job)
    {
        $job->update($request->validated());
        return new JobResource($job->fresh()->load(['employer', 'statusChanger']));
    }

    public function destroy(Job $job)
    {
        $job->delete(); // Soft delete
        return response()->json(['message' => 'Job moved to trash.']);
    }

    public function trashed()
    {
        $user = auth()->user();
        $jobs = Job::onlyTrashed()
            ->where('employer_id', $user->id)
            ->with(['employer', 'statusChanger'])
            ->get();
        return JobResource::collection($jobs);
    }

    public function restore($id)
    {
        $job = Job::onlyTrashed()->where('id', $id)->where('employer_id', auth()->id())->firstOrFail();
        $job->restore();
        return response()->json(['message' => 'Job restored successfully.']);
    }

    public function forceDelete($id)
    {
        $job = Job::onlyTrashed()->where('id', $id)->where('employer_id', auth()->id())->firstOrFail();
        $job->forceDelete();
        return response()->json(['message' => 'Job permanently deleted.']);
    }

    public function updateStatus(UpdateJobStatusRequest $request, Job $job)
    {
        if (Gate::denies('update-job-status', $job)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $job->update([
            'status' => $request->status,
            'status_changed_by' => auth()->id(),
            'status_changed_at' => Carbon::now(),
        ]);
        return new JobResource($job->fresh()->load(['employer', 'statusChanger']));
    }

    public function getJobById($id)
    {
        $job = Job::with(['employer', 'statusChanger'])->findOrFail($id);
        return new JobResource($job);
    }


}
