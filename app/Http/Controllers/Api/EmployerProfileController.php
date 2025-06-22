<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployerProfileRequest;
use App\Http\Requests\UpdateEmployerProfileRequest; // تأكد من استيرادها
use App\Models\EmployerProfile;
use App\Models\User; // تأكد من استيرادها لاستخدامها في showPublic
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmployerProfileController extends Controller
{
    /**
     * Store a newly created employer profile.
     */
    public function store(StoreEmployerProfileRequest $request)
    {
        $user = auth()->user();

        // Check if an employer profile already exists for this user
        if ($user->employerProfile) {
            return response()->json(['message' => 'Employer profile already exists for this user.'], 409);
        }

        $data = $request->validated();

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo')->store('company_logos', 'public');
        }

        $data['user_id'] = $user->id; // Associate with the authenticated user

        $profile = EmployerProfile::create($data);

        // Prepare response data, including the full URL for the logo
        $profileData = $profile->toArray();
        $profileData['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;

        $this->cleanResponseFields($profileData); // Clean sensitive/unnecessary fields

        return response()->json([
            'message' => 'Employer profile created successfully.',
            'data' => $profileData,
        ], 201);
    }

    /**
     * Display the authenticated user's employer profile.
     */
    public function show()
    {
        $profile = Auth::user()->employerProfile;

        if (!$profile) {
            return response()->json(['message' => 'Employer profile not found for this user.'], 404);
        }

        $data = $profile->toArray();
        $data['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;

        $this->cleanResponseFields($data);

        return response()->json(['data' => $data]);
    }

    /**
     * Update the authenticated user's employer profile.
     */
    public function update(UpdateEmployerProfileRequest $request)
    {
        $user = Auth::user();
        $employerProfile = $user->employerProfile;

        if (!$employerProfile) {
            return response()->json(['message' => 'Employer profile not found for this user.'], 404);
        }

        $data = $request->validated();

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            // Delete old logo if it exists
            if ($employerProfile->company_logo) {
                Storage::disk('public')->delete($employerProfile->company_logo);
            }
            $data['company_logo'] = $request->file('company_logo')->store('company_logos', 'public');
        } elseif ($request->has('company_logo') && $request->input('company_logo') === null) {
            // If 'company_logo' is explicitly sent as null/empty string, it means delete the old one
            if ($employerProfile->company_logo) {
                Storage::disk('public')->delete($employerProfile->company_logo);
            }
            $data['company_logo'] = null; // Set to null in DB
        }


        $employerProfile->update($data);

        // Reload the profile to get the latest data, especially for accessors like company_logo_url
        $employerProfile->refresh();

        $profileData = $employerProfile->toArray();
        $profileData['company_logo_url'] = $employerProfile->company_logo ? asset('storage/' . $employerProfile->company_logo) : null;

        $this->cleanResponseFields($profileData);

        return response()->json([
            'message' => 'Employer profile updated successfully!',
            'data' => $profileData,
        ], 200);
    }

    /**
     * Remove the authenticated user's employer profile.
     */
    public function destroy()
    {
        $profile = Auth::user()->employerProfile;

        if (!$profile) {
            return response()->json(['message' => 'Employer profile not found.'], 404);
        }

        // Delete company logo if it exists
        if ($profile->company_logo) {
            Storage::disk('public')->delete($profile->company_logo);
        }

        $profile->delete();

        return response()->json(['message' => 'Employer profile deleted successfully.']);
    }

    /**
     * Display a public employer profile by username.
     */
    public function showPublic($username)
    {
        try {
            // Find the user by username and eager load their employer profile
            $user = User::where('username', $username)->with('employerProfile')->first();

            if (!$user || !$user->employerProfile) {
                return response()->json(['message' => 'Employer profile not found.'], 404);
            }

            $profile = $user->employerProfile;

            $data = $profile->toArray();
            // Add user-related data
            $data['username'] = $user->username;
            $data['email'] = $user->email; // Or any other public user data

            // Add full URL for the company logo
            $data['company_logo_url'] = $profile->company_logo ? asset('storage/' . $profile->company_logo) : null;

            $this->cleanResponseFields($data);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error fetching public employer profile for username ' . $username . ': ' . $e->getMessage());
            return response()->json(['message' => 'Server error occurred.'], 500);
        }
    }

    /**
     * Cleans sensitive or unnecessary fields from the response data.
     *
     * @param array $data
     * @return void
     */
    private function cleanResponseFields(array &$data): void
    {
        // Remove fields that should not be exposed or are redundant after processing
        unset($data['user_id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        // If 'company_logo' raw path is not needed in frontend, unset it too
        // unset($data['company_logo']);

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
            'phone_number'
        ];

        foreach ($fieldsToClean as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = stripslashes(trim($data[$field], "\"' "));
            }
        }
    }
}