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

    /**
     * Display a public employer profile by username.
     */
    public function publicShow(Request $request, $id)
    {
        try {
            $profile = EmployerProfile::where('user_id', $id)->first();
            if (!$profile) {
                return response()->json(['message' => 'Employer profile not found.'], 404);
            }
            $user = $profile->user ?? null;
            $data = $profile->toArray();
            $data['name'] = $user ? $user->name : null;
            $data['email'] = $user ? $user->email : null;
            $data['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;
            if (method_exists($this, 'cleanResponseFields')) {
                $this->cleanResponseFields($data);
            }
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error fetching public employer profile for user ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Server error occurred.'], 500);
        }
    }

    public function showPublicProfileById($id)
    {
        $profile = EmployerProfile::where('user_id', $id)->first();
        if (!$profile) {
            return response()->json(['message' => 'Employer profile not found'], 404);
        }
        $data = $profile->toArray();
        $data['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;
        // Trim and strip slashes from string fields
        $fieldsToClean = [
            'company_name',
            'company_description',
            'website_url',
            'industry',
            'company_size',
            'location',
            'contact_person_name',
            'contact_email',
            'phone_number',
        ];
        foreach ($fieldsToClean as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = stripslashes(trim($data[$field], "\"' "));
            }
        }
        return response()->json([
            'message' => 'Employer profile data retrieved successfully',
            'data' => $data
        ]);
    }
}
