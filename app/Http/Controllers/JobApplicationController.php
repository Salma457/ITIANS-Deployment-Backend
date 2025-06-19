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
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{

    // display all applicants for a job (employer)
   public function getJobApplications($job_id)
{
    try {
        $job = Job::findOrFail($job_id);     

        if (!$job->employer || $job->employer->id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $jobApplications = JobApplication::where("job_id", $job_id)->get();

        return response()->json(['data' => $jobApplications]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    // Itian send application 
   // app/Http/Controllers/JobApplicationController.php

public function store(Request $request)
{
    try {
        // التحقق من المصادقة
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // التحقق من وجود ملف السيرة الذاتية
        if (!$request->hasFile('cv')) {
            return response()->json([
                'success' => false,
                'message' => 'CV file is required.'
            ], 422);
        }

        // التحقق من صحة الملف
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'cover_letter' => 'required|string',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // البحث عن ملف تعريف ITIAN
        $itianProfile = ItianProfile::where('user_id', Auth::id())->first();

        if (!$itianProfile) {
            return response()->json([
                'success' => false,
                'message' => 'You need an ITIAN profile to apply for jobs.'
            ], 403);
        }

        // تخزين الملف
        $storedPath = $request->file('cv')->store('job_applications', 'public');

        // إنشاء طلب الوظيفة
        $jobApplication = JobApplication::create([
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
        \Log::error('Job application error: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Server error occurred.',
            'error' => $e->getMessage() // في البيئة الانتاجية، أزل هذا السطر
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
        \Log::info('Trying to fetch application ID: ' . $id);

        $application = JobApplication::with(['job', 'itian'])->findOrFail($id);

        return response()->json(['data' => $application]);
    } catch (\Exception $e) {
        \Log::error('Error fetching application: ' . $e->getMessage());

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

public function checkIfApplied($job_id)
{
    try {
        if (!Auth::check()) {
            return response()->json(['hasApplied' => false], 401);
        }

        $itianProfile = ItianProfile::where('user_id', Auth::id())->first();
        if (!$itianProfile) {
            return response()->json(['hasApplied' => false], 403);
        }

        $application = JobApplication::where('job_id', $job_id)
            ->where('itian_id', $itianProfile->itian_profile_id)
            ->first();

        return response()->json([
            'hasApplied' => $application ? true : false,
            'applicationId' => $application ? $application->id : null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'hasApplied' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
public function update(Request $request, $id)
{
    try {
        $application = JobApplication::findOrFail($id);
        $itianProfile = ItianProfile::where('user_id', Auth::id())->firstOrFail();

        if ($application->itian_id !== $itianProfile->itian_profile_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string|min:100',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ], [
            'cover_letter.min' => 'Cover letter must be at least 100 characters',
            'cv.max' => 'CV file must be less than 2MB'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $application->cover_letter = $request->cover_letter;

        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if ($application->cv && Storage::exists('public/' . $application->cv)) {
                Storage::delete('public/' . $application->cv);
            }
            
            $path = $request->file('cv')->store('job_applications', 'public');
            $application->cv = $path;
        }

        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Application updated successfully',
            'data' => $application
        ]);

    } catch (\Exception $e) {
        \Log::error('Update error: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}


}


// check if current authenticated ITIAN has applied to a specific job
