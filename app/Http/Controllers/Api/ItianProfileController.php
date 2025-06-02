<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItianProfileRequest;
use App\Models\ItianProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }
        if ($request->hasFile('profile_picture')) {
    if ($request->profile_picture) {
        Storage::disk('public')->delete($request->profile_picture);
    }
    $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
}


        if ($request->hasFile('cv')) {
            $data['cv'] = $request->file('cv')->store('cvs', 'public');
        }

        $profile = $user->itianProfile()->create($data);

        return response()->json([
            'message' => 'Profile created successfully',
            'data' => [
                'profile' => $profile,
                'cv_url' => isset($data['cv']) ? asset('storage/' . $data['cv']) : null,
            ]
        ], 201);
    }

    public function show()
    {
        $profile = Auth::user()->itianProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $profile->toArray();
        $data['cv_url'] = $profile->cv ? asset('storage/' . $profile->cv) : null;

        return response()->json($data);
    }

    public function update(StoreItianProfileRequest $request)
    {
        $profile = Auth::user()->itianProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('cv')) {
           
            if ($profile->cv) {
                Storage::disk('public')->delete($profile->cv);
            }
            $data['cv'] = $request->file('cv')->store('cvs', 'public');
        }
        if ($request->hasFile('profile_picture')) {
            if ($profile->profile_picture) {
                Storage::disk('public')->delete($profile->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }
        
        $profile->update($data);

        $responseData = $profile->toArray();
        $responseData['cv_url'] = isset($data['cv']) ? asset('storage/' . $data['cv']) : ($profile->cv ? asset('storage/' . $profile->cv) : null);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $responseData,
        ]);
    }

    public function destroy()
    {
        $profile = Auth::user()->itianProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

      
        if ($profile->cv) {
            Storage::disk('public')->delete($profile->cv);
        }

        $profile->delete();

        return response()->json(['message' => 'Profile deleted']);
    }
}
