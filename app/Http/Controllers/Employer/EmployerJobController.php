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

class EmployerJobController extends Controller
{
    public function index()
    {
        return new JobCollection(Job::with(['employer', 'statusChanger'])->paginate(10));
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
        $job->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(UpdateJobStatusRequest $request, Job $job)
    {
        $job->update([
            'status' => $request->status,
        ]);

        return new JobResource($job->load(['employer', 'statusChanger']));
    }

    public function employerJobs()
    {
        $jobs = auth()->user()->jobs()->with(['employer', 'statusChanger'])->get();
        return JobResource::collection($jobs);
    }

    public function statistics(): JsonResponse
    {
        // Check if user is authenticated and has admin role
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }
    
        $stats = [
            'total_jobs' => Job::count(),
            'open_jobs' => Job::where('status', Job::STATUS_OPEN)->count(),
            'pending_jobs' => Job::where('status', Job::STATUS_PENDING)->count(),
            'closed_jobs' => Job::where('status', Job::STATUS_CLOSED)->count(),
            'jobs_per_type' => Job::groupBy('job_type')->selectRaw('job_type, count(*) as count')->get(),
            'jobs_per_location' => Job::groupBy('job_location')->selectRaw('job_location, count(*) as count')->get(),
        ];
    
        return response()->json($stats);
    }
    
}