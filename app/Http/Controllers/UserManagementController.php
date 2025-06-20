<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    //
    public function allUsers()
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::latest()->get()->map(function ($user) {
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_picture' => null,
            ];

            if ($user->role === 'itian' && $user->itianProfile) {
                $data['profile_picture'] = $user->itianProfile->profile_picture ?? null;
            } elseif ($user->role === 'employer' && $user->employerProfile) {
                $data['profile_picture'] = $user->employerProfile->profile_picture ?? null;
            }

            return $data;
        });

        return response()->json($users);
    }

    public function getUnApprovedEmployers()
    {
        // check user role
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::where('role', 'employer')
            ->where('is_active', false)
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return response()->json($users);
    }
}
