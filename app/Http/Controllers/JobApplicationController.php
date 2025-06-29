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

public function getMyApplications()
{
    try {
        $itianProfile = ItianProfile::where('user_id', Auth::id())->first();

        if (!$itianProfile) {
            return response()->json([
                'success' => false,
                'message' => 'ITIAN profile not found.'
            ], 404);
        }

        $applications = JobApplication::with(['job', 'job.employer']) // <<< أهم حاجة هنا
            ->where('itian_id', $itianProfile->itian_profile_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'applications' => $applications
        ]);
    } catch (\Exception $e) {
        \Log::error('Fetching my applications failed: ' . $e->getMessage());
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
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            if (!$request->hasFile('cv')) {
                return response()->json([
                    'success' => false,
                    'message' => 'CV file is required.'
                ], 422);
            }

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

            $itianProfile = ItianProfile::where('user_id', Auth::id())->first();

            if (!$itianProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an ITIAN profile to apply for jobs.'
                ], 403);
            }

            $storedPath = $request->file('cv')->store('job_applications', 'public');
            $job = Job::find($request->job_id);
            $jobApplication = JobApplication::create([
                'job_id' => $request->job_id,
                'itian_id' => $itianProfile->itian_profile_id,
                'cv' => $storedPath,
                'cover_letter' => $request->cover_letter,
                'application_date' => now(),
                'status' => 'pending',
            ]);
            $employerUserId = $job?->employer_id; 
            \Log::info('Employer user ID: ' . $employerUserId);
            if ($employerUserId) {
                try {
                   $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'), // استخدم SERVICE_ROLE_KEY وليس ANON_KEY هنا لو تقدر
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://obrhuhasrppixjwkznri.supabase.co/rest/v1/notifications', [
                    [
                        'user_id' => $employerUserId,
                        'title' => 'New Job Application',
                        'message' => 'Someone applied for your job: ' . $job->title,
                        'notifiable_type' => 'App\\Models\\User',
                        'notifiable_id' => $employerUserId,
                        'type' => 'application',
                        'seen' => false,
                        'job_id' => $job->id,
                        'created_at' => now()
                    ]
                ]);

                } catch (\Exception $e) {
                    \Log::error('Employer notification failed: ' . $e->getMessage());
                }
            }

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
                'error' => $e->getMessage() 
            ], 500);
        }
    }
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
    public function index(Request $request)
    {
        try {
            $query = Job::query();

            // Case-insensitive search by job title
            if ($request->has('search') && $request->search) {
                $search = strtolower($request->search);
                $query->whereRaw('LOWER(job_title) LIKE ?', ["%{$search}%"]);
            }

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;

                if (!empty($filters['job_type'])) {
                    $query->where('job_type', $filters['job_type']);
                }

                if (!empty($filters['job_location'])) {
                    $query->where('job_location', $filters['job_location']);
                }

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                if (!empty($filters['employer_id'])) {
                    $query->where('employer_id', $filters['employer_id']);
                }

                if (!empty($filters['min_salary'])) {
                    $query->where('salary_range_min', '>=', $filters['min_salary']);
                }

                if (!empty($filters['max_salary'])) {
                    $query->where('salary_range_max', '<=', $filters['max_salary']);
                }
            }

            // Sorting
            if ($request->has('sort')) {
                $sortField = ltrim($request->sort, '-');
                $sortDirection = str_starts_with($request->sort, '-') ? 'desc' : 'asc';
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('posted_date', 'desc'); // default sort
            }

            // Pagination
            $perPage = $request->input('perPage', 10);
            $page = $request->input('page', 1);

            $jobs = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json($jobs);

        } catch (\Exception $e) {
            \Log::error('Job fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


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

            $application = JobApplication::with('job.employer.employerProfile', 'itian.user')->findOrFail($id);
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
                            'created_at' => now()

                        ]);
                        \Log::info('Employer user ID: ' . $employerUserId);

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
                'applicationId' => $application ? $application->id : null,
                'status' => $application->status,

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
public function getJobApplications($jobId)
{
    try {
        $applications = JobApplication::with('itian.user')
            ->where('job_id', $jobId)
            ->get();

        return response()->json($applications);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}



}


