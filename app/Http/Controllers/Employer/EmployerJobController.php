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
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Http\Request;
class EmployerJobController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
{
    $query = Job::with(['employer', 'statusChanger']);

    if ($request->filled('title')) {
        $query->where('job_title', 'like', '%' . $request->title . '%');
    }

    if ($request->filled('job_type')) {
        $query->where('job_type', $request->job_type);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('job_location')) {
        $query->where('job_location', $request->job_location);
    }

    if ($request->filled('min_salary')) {
        $query->where('salary_range_min', '>=', $request->min_salary);
    }

    if ($request->filled('max_salary')) {
        $query->where('salary_range_max', '<=', $request->max_salary);
    }

    return new JobCollection($query->paginate(10));
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

    public function show(Job $job)
    {
        $job->increment('views_count');
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

    // Restore a trashed job
    public function restore($id)
    {
        $job = Job::onlyTrashed()->where('id', $id)->where('employer_id', auth()->id())->firstOrFail();
        $job->restore();
        return response()->json(['message' => 'Job restored successfully.']);
    }

    // Permanently delete a trashed job
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
