<?php

namespace App\Http\Controllers\Itian;

use App\Http\Requests\ItianRegistrationRequestRequest;
use App\Mail\RegistrationRequestReviewed;
use App\Models\ItianRegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ItianRegistrationRequestController extends Controller
{

    public function index()
    {
        //
        // return ItianRegistrationRequest::with('user')->get();
        $requests = ItianRegistrationRequest::with('user')
            ->where('is_verified', true)
            ->where('status', 'Pending')
            ->get();
        return response()->json($requests);
    }

    public function store(ItianRegistrationRequestRequest $request)
    {
        $existing = ItianRegistrationRequest::where('user_id', Auth::id())->first();
        if ($existing) {
            return response()->json(['message' => 'You already submitted a registration request.'], 400);
        }

        $path = $request->file('certificate')->store('certificates', 'public');

        $requestModel = ItianRegistrationRequest::create([
            'user_id' => Auth::id(),
            'certificate' => $path,
            'status' => 'Pending',
        ]);

        return response()->json([
            'message' => 'Registration request submitted.',
            'request' => $requestModel
        ], 201);
    }

    public function review(Request $request, $id)
    {

        $request->validate([
            'status' => 'required|in:Approved,Rejected',
        ]);

        $regRequest = ItianRegistrationRequest::findOrFail($id);
        $regRequest->status = $request->status;
        $regRequest->reviewed_by_admin_id = Auth::id();
        $regRequest->save();

        // If rejected, delete the user
        if ($request->status === 'Rejected' && $regRequest->user) {
            $regRequest->user->delete();
        }

        Mail::to($regRequest->user->email)->send(new RegistrationRequestReviewed($regRequest));
        if ($request->status === 'Approved') {
            $regRequest->user->is_active = true;
            $regRequest->user->save();
            $regRequest->delete();
        }
        return response()->json(['message' => 'Request updated.', 'request' => $regRequest]);
    }

    public function show(Request $request, $id)
    {
        $regRequest = ItianRegistrationRequest::with('user')->findOrFail($id);
        return response()->json($regRequest);
    }



    public function update(Request $request, ItianRegistrationRequestRequest $itianRegistrationRequest)
    {
        //
    }

    public function destroy(ItianRegistrationRequest $itianRegistrationRequest)
    {
        //
    }
}
