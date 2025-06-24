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
use App\Mail\ApprovedForInterviewMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationRequestRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\ApprovedApplicationNotification;
use Illuminate\Support\Facades\Http;

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

        $jobApplications = JobApplication::with('itian')->where("job_id", $job_id)->get();

            return response()->json(['data' => $jobApplications]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
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
    public function getEmployerAllJobApplications()
    {
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


    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected,pending',
            ]);

            $application = JobApplication::with('itian.user', 'job')->findOrFail($id);
            $application->status = $request->status;
            $application->save();

            if (in_array($request->status, ['approved', 'rejected'])) {
                $email = $application->itian->user->email ?? null;

                if ($email) {
                    try {
                        if ($request->status === 'approved') {
                            Mail::to($email)->send(new ApprovedForInterviewMail($application));

                             Http::withHeaders([
                            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                            'apikey' => env('SUPABASE_ANON_KEY'), // ✅ لازم يتكرر هنا
                            'Content-Type' => 'application/json',
                            'X-Client-Info' => 'supabase-js/2.0.0', // ✅ دا بيخلي Supabase يبعت Realtime event
                        ])
                        ->post('https://obrhuhasrppixjwkznri.supabase.co/rest/v1/notifications?select=*', [
                            'user_id' => $application->itian->user_id,
                            'title' => 'You have been approved for a job',
                            'message' => 'You have been accepted for the job: ' . $application->job->title,
                            'notifiable_type' => 'App\\Models\\User',
                            'notifiable_id' => $application->itian->user_id,
                            'type' => 'system',
                            'seen' => false,
                            'job_id' => $application->job->id,
                        ]);

                        } elseif ($request->status === 'rejected') {
                            Mail::to($email)->send(new RegistrationRequestRejected($application));
                        }
                    } catch (\Exception $e) {
                        \Log::error('Mail or Notification failed: ' . $e->getMessage());
                    }
                }
            }

            return response()->json(['message' => 'Application status updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Status Update Error: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
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


