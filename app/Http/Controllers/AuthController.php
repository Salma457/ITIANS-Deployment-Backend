<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\EmployerRegistrationRequest;
use App\Models\ItianRegistrationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\VerifyRegistrationEmail;
use Illuminate\Support\Facades\Mail;



class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $uuid = $request->input('uuid');

        $user = User::create(array_merge(
            $request->all(),
            ['uuid' => $uuid] // اربط اليوزر بالـ UUID بتاع Supabase
        ));
        Mail::to($user->email)->send(new VerifyRegistrationEmail($user));

        // $token = $user->createToken('auth_token')->plainTextToken;
        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('certificates', 'public');

            ItianRegistrationRequest::create([
                'user_id' => $user->id,
                'certificate' => $path,
                'status' => 'Pending',
            ]);
        }

        // Handle employer registration request
        if ($user->role === 'employer' && $request->has('company_brief')) {
            $existing = EmployerRegistrationRequest::where('user_id', $user->id)->first();
            if ($existing) {
                return response()->json(['message' => 'You already submitted a registration request.'], 400);
            }
            EmployerRegistrationRequest::create([
                'user_id' => $user->id,
                'company_brief' => $request->input('company_brief'),
                'status' => 'Pending',
            ]);
        }

        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => $user,
        ], 201);
    }

 public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();

    if (!$user->is_active) {
        $message = 'Your account is not active.'; // Fallback message
        $emailVerified = false;

        if ($user->role === 'itian') {
            $registrationRequest = \App\Models\ItianRegistrationRequest::where('user_id', $user->id)->first();
            $emailVerified = $registrationRequest?->is_verified ?? false;
            if ($emailVerified) {
                $message = 'Your certificate is in progress, we will reply soon.';
            }
        } elseif ($user->role === 'employer') {
            $registrationRequest = \App\Models\EmployerRegistrationRequest::where('user_id', $user->id)->first();
            $emailVerified = $registrationRequest?->is_verified ?? false;
            if ($emailVerified) {
                $message = 'Your profile is in progress, please wait we will reply soon.';
            }
        }

        // If the email is not verified, this message takes precedence.
        if (!$emailVerified) {
            $message = 'Please verify your email.';
        }

        return response()->json(['message' => $message], 403);
    }


    $token = $user->createToken('access-token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'user' => $user
    ]);
}


    public function logout(Request $request)
    {
        // Revoke token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
