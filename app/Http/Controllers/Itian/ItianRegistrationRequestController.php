<?php

namespace App\Http\Controllers\Itian;

use App\Http\Requests\ItianRegistrationRequestRequest;
use App\Models\ItianRegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ItianRegistrationRequestController extends Controller
{

    public function index()
    {
        return ItianRegistrationRequest::with('user')->get();
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

        return response()->json(['message' => 'Request updated.', 'request' => $regRequest]);
    }

    public function show(ItianRegistrationRequest $itianRegistrationRequest)
    {
        //
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
