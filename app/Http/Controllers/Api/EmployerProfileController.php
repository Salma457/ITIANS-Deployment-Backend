<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployerProfileRequest;
use App\Http\Requests\UpdateEmployerProfileRequest;
use App\Models\EmployerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmployerProfileController extends Controller
{
    public function store(StoreEmployerProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->employerProfile) {
            return response()->json([
                'message' => 'Employer profile already exists'
            ], 409);
        }

        $data = $request->validated();

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo')->store('company_logos', 'public');
        }

        $profile = $user->employerProfile()->create($data);

        return response()->json([
            'message' => 'Employer profile created successfully',
            'data' => [
                'profile' => $profile,
                'company_logo_url' => isset($data['company_logo']) ? asset('storage/' . $data['company_logo']) : null,
            ]
        ], 201);
    }

    public function show(Request $request)
    {
        $user = Auth::user();

        $profile = EmployerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Employer profile not found'], 404);
        }

        $data = $profile->toArray();
        $data['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;

        return response()->json($data);
    }

   public function update(UpdateEmployerProfileRequest $request, $user_id)
{
    $employerProfile = EmployerProfile::where('user_id', $user_id)->first();

    if (!$employerProfile) {
        return response()->json(['message' => 'Employer profile not found'], 404);
    }

    Log::info('Received update data for user_id ' . $user_id . ':', $request->all()); // Debug log

    // Handle company logo upload
    if ($request->hasFile('company_logo')) {
        if ($employerProfile->company_logo) {
            Storage::disk('public')->delete($employerProfile->company_logo);
        }
        $employerProfile->company_logo = $request->file('company_logo')->store('company_logos', 'public');
    } elseif ($request->input('company_logo_removed')) {
        if ($employerProfile->company_logo) {
            Storage::disk('public')->delete($employerProfile->company_logo);
            $employerProfile->company_logo = null;
        }
    }

    // Update validated data
    $data = $request->validated();
    $updated = $employerProfile->update($data);

    if (!$updated) {
        Log::error('Update failed for employer profile ID: ' . $employerProfile->id);
        return response()->json(['message' => 'Failed to update profile'], 500);
    }

    $employerProfile->refresh();
    $data = $employerProfile->toArray();
    $data['company_logo_url'] = $employerProfile->company_logo ? asset('storage/' . $employerProfile->company_logo) : null;

    return response()->json([
        'message' => 'Employer profile updated successfully',
        'data' => $data
    ]);
}

    public function destroy()
    {
        $profile = Auth::user()->employerProfile;

        if (!$profile) {
            return response()->json(['message' => 'Employer profile not found'], 404);
        }

        if ($profile->company_logo) {
            Storage::disk('public')->delete($profile->company_logo);
        }

        $profile->delete();

        return response()->json(['message' => 'Employer profile deleted']);
    }

    public function showPublicProfileById($id)
{
    $profile = EmployerProfile::where('user_id', $id)->first();

    if (!$profile) {
        return response()->json(['message' => 'Employer profile not found'], 404);
    }

    $data = $profile->toArray();
    $data['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;

    return response()->json([
        'message' => 'Employer profile data retrieved successfully',
        'data' => $data
    ]);
}
}