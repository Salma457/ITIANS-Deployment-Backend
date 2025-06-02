<?php

namespace App\Http\Controllers;

use App\Models\JobReviews;
use Illuminate\Http\Request;

class JobReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($jobId)
    {
        //
        
        $job = JobReviews::where("job_id", $jobId)
            ->with("user")
            ->latest()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(JobReviews $jobReviews)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobReviews $jobReviews)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobReviews $jobReviews)
    {
        //
    }
}
