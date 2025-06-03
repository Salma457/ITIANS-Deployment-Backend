<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Models\ItianProfile;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getJobApplications($job_id)
    {
        //
        $job = Job::findOrFail($job_id);
        
   
        if ($job->employer->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $jobApplications = JobApplication::where("job_id", $job_id)->get();
        return response()->json(data: $jobApplications);
    }

    public function store(Request $request)
    {
        
        try {
            $itianProfile = ItianProfile::where('user_id', Auth::id())->firstOrFail();

            if (!$itianProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'ITIAN profile not found.'
                ], 404);
            }

            $storedPath = null;
            if ($request->hasFile('cv')) {
                $storedPath = $request->file('cv')->store('jobApplications', 'public');
            }

            $jobApplication = JobApplication::create(attributes: [
                'job_id' => $request->job_id,
                'itian_id' => $itianProfile->itian_profile_id,
                'cv' => $storedPath,
                'cover_letter' => $request->cover_letter,
                'application_date' => now(),
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'data' => $jobApplication
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(JobApplication $jobApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobApplication $jobApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobApplication $jobApplication)
    {
        //
    }
}
