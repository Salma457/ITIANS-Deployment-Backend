<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItianSkillRequest;
use App\Http\Requests\StoreItianProjectRequest;
use App\Models\ItianSkill;
use App\Models\ItianProject;
use Illuminate\Support\Facades\Auth;

class ItianSkillProjectController extends Controller
{
    public function storeSkill(StoreItianSkillRequest $request)
    {
        $itianProfile = Auth::user()->itianProfile;

        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        // Check if skill already exists for this profile
        $exists = ItianSkill::where('itian_profile_id', $itianProfile->itian_profile_id)
            ->where('skill_name', $request->skill_name)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Skill already exists for this profile'], 409);
        }

        $skill = ItianSkill::create([
            'itian_profile_id' => $itianProfile->itian_profile_id,
            'skill_name' => $request->skill_name,
        ]);

        return response()->json(['message' => 'Skill added', 'data' => $skill], 201);
    }

    public function deleteSkill($id)
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $skill = ItianSkill::where('id', $id)
            ->where('itian_profile_id', $itianProfile->itian_profile_id)
            ->first();

        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }

        $skill->delete();

        return response()->json(['message' => 'Skill deleted']);
    }

    public function listSkills()
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $skills = ItianSkill::where('itian_profile_id', $itianProfile->itian_profile_id)->get();

        return response()->json(['skills' => $skills]);
    }

    public function showSkillsByProfile($itian_profile_id)
    {
        $skills = ItianSkill::where('itian_profile_id', $itian_profile_id)->get();

        return response()->json(['skills' => $skills]);
    }

    public function updateSkill(StoreItianSkillRequest $request, $id)
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $skill = ItianSkill::where('id', $id)
            ->where('itian_profile_id', $itianProfile->itian_profile_id)
            ->first();

        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }

        $exists = ItianSkill::where('itian_profile_id', $itianProfile->itian_profile_id)
            ->where('skill_name', $request->skill_name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Skill name already exists for this profile'], 409);
        }

        $skill->skill_name = $request->skill_name;
        $skill->save();

        return response()->json(['message' => 'Skill updated', 'data' => $skill]);
    }

    public function storeProject(StoreItianProjectRequest $request)
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $project = ItianProject::create([
            'itian_profile_id' => $itianProfile->itian_profile_id,
            'project_title' => $request->project_title,
            'description' => $request->description,
            'project_link' => $request->project_link,
        ]);

        return response()->json(['message' => 'Project added', 'data' => $project], 201);
    }

    public function updateProject(StoreItianProjectRequest $request, $id)
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $project = ItianProject::where('id', $id)
            ->where('itian_profile_id', $itianProfile->itian_profile_id)
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->update([
            'project_title' => $request->project_title,
            'description' => $request->description,
            'project_link' => $request->project_link,
        ]);

        return response()->json(['message' => 'Project updated', 'data' => $project]);
    }

    public function deleteProject($id)
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $project = ItianProject::where('id', $id)
            ->where('itian_profile_id', $itianProfile->itian_profile_id)
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }

    public function listProjects()
    {
        $itianProfile = Auth::user()->itianProfile;
        
        if (!$itianProfile) {
            return response()->json(['message' => 'User has no ItianProfile'], 404);
        }

        $projects = ItianProject::where('itian_profile_id', $itianProfile->itian_profile_id)->get();

        return response()->json(['projects' => $projects]);
    }

    public function showProjectsByProfile($itian_profile_id)
    {
        $projects = ItianProject::where('itian_profile_id', $itian_profile_id)->get();

        return response()->json(['projects' => $projects]);
    }
}