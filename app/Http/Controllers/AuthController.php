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

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $user = User::create($request->all());

        $token = $user->createToken('auth_token')->plainTextToken;
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
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        if ($user->role != 'admin') {
            if (!$user->is_active) {
                return response()->json(['message' => 'User account is not active'], 403);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->last_login = now();
        $user->save();

        return response()->json([
            'message' => 'User logged in successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
