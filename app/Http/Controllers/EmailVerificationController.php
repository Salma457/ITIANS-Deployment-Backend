<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ItianRegistrationRequest;
use App\Models\EmployerRegistrationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Determine which request table to check based on user role
        if ($user->role === 'itian') {
            $requestModel = ItianRegistrationRequest::where('user_id', $user->id)->first();
        } elseif ($user->role === 'employer') {
            $requestModel = EmployerRegistrationRequest::where('user_id', $user->id)->first();
        } else {
            // Handle other roles or scenarios if necessary
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/email-verification-failed');
        }

        if (!$requestModel) {
            // Request not found, maybe redirect with an error
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/email-verification-failed');
        }

        if ($requestModel->is_verified) {
            // Already verified, redirect to a "already verified" page or login
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?verified=true');
        }

        // Mark as verified
        $requestModel->is_verified = true;
        $requestModel->save();

        // Redirect to a success page on the frontend
        return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/email-verified-successfully');
    }
}
