<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployerProfileRequest;
use App\Models\EmployerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployerProfileController extends Controller
{
    public function store(StoreEmployerProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->employerProfile) {
            return response()->json(['message' => 'Profile already exists'], 409);
        }

        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo')->store('company_logos', 'public');
        }

        $data['user_id'] = $user->id;

        $profile = EmployerProfile::create($data);

        return response()->json([
            'message' => 'Employer profile created successfully',
            'data' => $profile,
        ], 201);
    }

    public function show()
    {
        $profile = Auth::user()->employerProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $profile->toArray();
        $data['company_logo_url'] = $profile->company_logo_url;

        return response()->json($data);
    }
    public function update(StoreEmployerProfileRequest $request)
    {
        $profile = Auth::user()->employerProfile;
    
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
    
        $data = $request->validated();
    
        if ($request->hasFile('company_logo')) {
            if ($profile->company_logo) {
                Storage::disk('public')->delete($profile->company_logo);
            }
            $data['company_logo'] = $request->file('company_logo')->store('company_logos', 'public');
        }
    
        $profile->fill($data);
    
       
        if (!$profile->isDirty()) {
            return response()->json([
                'message' => 'No changes detected',
                'debug' => [
                    'validated_data' => $data,
                    'original' => $profile->getOriginal(),
                    'dirty' => $profile->getDirty(),
                ]
            ]);
        }
    
        $profile->save();
    
        $data = $profile->toArray();
        $data['company_logo_url'] = $profile->company_logo_url;
    
        return response()->json([
            'message' => 'Employer profile updated successfully',
            'data' => $data,
        ]);
    }
    
    
    public function destroy()
    {
        $profile = Auth::user()->employerProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        if ($profile->company_logo) {
            Storage::disk('public')->delete($profile->company_logo);
        }

        $profile->delete();

        return response()->json(['message' => 'Employer profile deleted']);
    }
}
