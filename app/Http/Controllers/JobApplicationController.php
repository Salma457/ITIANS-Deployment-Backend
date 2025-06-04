<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Models\ItianProfile;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobApplicationController extends Controller
{

    // display all applicants for a job (employer)
    public function getJobApplications($job_id)
    {
        try{
            $job = Job::findOrFail($job_id);     
            // return $job;

            if ($job->employer->id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $jobApplications = JobApplication::where("job_id", $job_id)->get();

            return response()->json(data: $jobApplications);
            
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Itian send application 
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



    // get all employer's applications for all jobs
    public function getEmployerAllJobApplications(){
        try{

            $jobApplications = JobApplication::get();

            return response()->json(data: $jobApplications);
            
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // get all Itian job applications
    public function index()
    {
        try {
            $itianProfile = ItianProfile::where('user_id', Auth::id())->firstOrFail();

            $applications = JobApplication::where('itian_id', $itianProfile->itian_profile_id)
                ->with('job') 
                ->get();

            return response()->json(['data' => $applications]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // show job application by Id
    public function show($id)
    {
        try {

            $application = JobApplication::with(['job', 'itian'])->findOrFail($id);
            return response()->json(['data' => $application]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    // update job application status. employer sends status of job application.
    public function updateStatus(UpdateJobApplicationRequest $request, $id)
    {

        try {
            $application = JobApplication::findOrFail($id);
            $job = $application->job;

            if ($job->employer->id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $application->status = $request->status;
            $application->save();

            return response()->json(['message' => 'Status updated.', 'data' => $application]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // job applicant deletes job application
    public function destroy($id)
    {
        try {
            $application = JobApplication::findOrFail($id);
            $itianProfile = ItianProfile::where('user_id', Auth::id())->firstOrFail();

            if ($application->itian_id !== $itianProfile->itian_profile_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $application->delete();

            return response()->json(['message' => 'Application withdrawn.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




}
