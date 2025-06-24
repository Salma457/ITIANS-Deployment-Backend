<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItianProfileRequest;
use App\Models\ItianProfile;
use App\Models\ItianSkill;
use App\Models\ItianProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateItianProfileRequest;
use Illuminate\Support\Facades\Log;

class ItianProfileController extends Controller
{
    public function store(StoreItianProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->itianProfile) {
            return response()->json([
                'message' => 'Profile already exists'
            ], 409);
        }

        $data = $request->validated();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Handle CV upload
        if ($request->hasFile('cv')) {
            $data['cv'] = $request->file('cv')->store('cvs', 'public');
        }

        $profile = $user->itianProfile()->create($data);

        // Handle Skills
        if ($request->has('skills')) {
            foreach ($request->input('skills') as $skillData) {
                if (isset($skillData['skill_name']) && !empty(trim($skillData['skill_name']))) {
                    ItianSkill::create([
                        'itian_profile_id' => $profile->itian_profile_id,
                        'skill_name' => trim($skillData['skill_name']),
                    ]);
                }
            }
        }

        // Handle Projects
        if ($request->has('projects')) {
            foreach ($request->input('projects') as $projectData) {
                if (isset($projectData['project_title']) && !empty(trim($projectData['project_title']))) {
                    ItianProject::create([
                        'itian_profile_id' => $profile->itian_profile_id,
                        'project_title' => trim($projectData['project_title']),
                        'description' => $projectData['description'] ?? null,
                        'project_link' => $projectData['project_link'] ?? null,
                    ]);
                }
            }
        }

        $profile->load(['skills', 'projects']);

        return response()->json([
            'message' => 'Profile created successfully',
            'data' => [
                'profile' => $profile,
                'cv_url' => isset($data['cv']) ? asset('storage/' . $data['cv']) : null,
                'profile_picture_url' => isset($data['profile_picture']) ? asset('storage/' . $data['profile_picture']) : null,
            ]
        ], 201);
    }

    public function show(Request $request)
    {
        $user = Auth::user();

        $profile = ItianProfile::with(['skills', 'projects'])
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $profile->toArray();
        $data['cv_url'] = $profile->cv ? asset('storage/' . $profile->cv) : null;
        $data['profile_picture_url'] = $profile->profile_picture ? asset('storage/' . $profile->profile_picture) : null;

        return response()->json($data);
    }

   public function update(Request $request, $user_id)
{
    $itianProfile = ItianProfile::where('user_id', $user_id)->first();

    if (!$itianProfile) {
        return response()->json(['message' => 'Profile not found'], 404);
    }

    $itianProfile->update([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'bio' => $request->input('bio'),
        'iti_track' => $request->input('iti_track'),
        'graduation_year' => $request->input('graduation_year'),
        'portfolio_url' => $request->input('portfolio_url'),
        'linkedin_profile_url' => $request->input('linkedin_profile_url'),
        'github_profile_url' => $request->input('github_profile_url'),
        'is_open_to_work' => $request->boolean('is_open_to_work'),
        'experience_years' => $request->input('experience_years'),
        'current_job_title' => $request->input('current_job_title'),
        'current_company' => $request->input('current_company'),
        'preferred_job_locations' => $request->input('preferred_job_locations'),
        'email' => $request->input('email'),
        'number' => $request->input('number'),
    ]);

    if ($request->hasFile('profile_picture')) {
        if ($itianProfile->profile_picture && Storage::disk('public')->exists($itianProfile->profile_picture)) {
            Storage::disk('public')->delete($itianProfile->profile_picture);
        }
        $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        $itianProfile->profile_picture = $imagePath;
    }

    if ($request->hasFile('cv')) {
        if ($itianProfile->cv && Storage::disk('public')->exists($itianProfile->cv)) {
            Storage::disk('public')->delete($itianProfile->cv);
        }
        $cvPath = $request->file('cv')->store('cvs', 'public');
        $itianProfile->cv = $cvPath;
    }

    $itianProfile->save();

    if ($request->has('skills')) {
        $skillIds = [];
        foreach ($request->input('skills') as $skillData) {
            if (isset($skillData['skill_name']) && !empty(trim($skillData['skill_name']))) {
                if (isset($skillData['id'])) {
                    $skill = ItianSkill::find($skillData['id']);
                    if ($skill && $skill->itian_profile_id == $itianProfile->itian_profile_id) {
                        $skill->update(['skill_name' => trim($skillData['skill_name'])]);
                        $skillIds[] = $skill->id;
                    }
                } else {
                    $newSkill = ItianSkill::create([
                        'itian_profile_id' => $itianProfile->itian_profile_id,
                        'skill_name' => trim($skillData['skill_name']),
                    ]);
                    $skillIds[] = $newSkill->id;
                }
            }
        }
        ItianSkill::where('itian_profile_id', $itianProfile->itian_profile_id)
            ->whereNotIn('id', $skillIds)
            ->delete();
    }

    // تحديث الـ Projects
    if ($request->has('projects')) {
        $projectIds = [];
        foreach ($request->input('projects') as $projectData) {
            if (isset($projectData['project_title']) && !empty(trim($projectData['project_title']))) {
                if (isset($projectData['id'])) {
                    $project = ItianProject::find($projectData['id']);
                    if ($project && $project->itian_profile_id == $itianProfile->itian_profile_id) {
                        $project->update([
                            'project_title' => trim($projectData['project_title']),
                            'description' => $projectData['description'] ?? null,
                            'project_link' => $projectData['project_link'] ?? null,
                        ]);
                        $projectIds[] = $project->id;
                    }
                } else {
                    $newProject = ItianProject::create([
                        'itian_profile_id' => $itianProfile->itian_profile_id,
                        'project_title' => trim($projectData['project_title']),
                        'description' => $projectData['description'] ?? null,
                        'project_link' => $projectData['project_link'] ?? null,
                    ]);
                    $projectIds[] = $newProject->id;
                }
            }
        }
        ItianProject::where('itian_profile_id', $itianProfile->itian_profile_id)
            ->whereNotIn('id', $projectIds)
            ->delete();
    }

    $itianProfile->refresh();
    $itianProfile->load(['skills', 'projects']);
    
    $data = $itianProfile->toArray();
    $data['cv_url'] = $itianProfile->cv ? asset('storage/' . $itianProfile->cv) : null;
    $data['profile_picture_url'] = $itianProfile->profile_picture ? asset('storage/' . $itianProfile->profile_picture) : null;

    return response()->json([
        'message' => 'Profile updated successfully',
        'data' => $data
    ]);
}



    public function showProfileByUserId($user_id)
    {
        $profile = ItianProfile::with(['skills', 'projects'])
            ->where('user_id', $user_id)
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $profile->toArray();
        $data['cv_url'] = $profile->cv ? asset('storage/' . $profile->cv) : null;
        $data['profile_picture_url'] = $profile->profile_picture ? asset('storage/' . $profile->profile_picture) : null;

        return response()->json([
            'message' => 'Profile data retrieved successfully',
            'data' => $data
        ]);
    }
}