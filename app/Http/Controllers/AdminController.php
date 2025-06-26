<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPricing;
use App\Models\User;
use App\Mail\RoundEndedNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\AdminNotification;

class AdminController extends Controller
{
    
    public function showPricing()
    {
        $latest = JobPricing::latest()->first();
        return view('admin.job_pricing', compact('latest'));
    }

    
    // public function updatePricing(Request $request)
    // {
    //     $request->validate([
    //         'price' => 'required|numeric|min:0.5',
    //     ]);

    //     JobPricing::create([
    //         'price' => $request->price,
    //     ]);

    //     return redirect()->back()->with('success', 'price u[dated');
    // }

    public function getLatestPrice()
{
    $latest = \App\Models\JobPricing::latest()->first();
    return response()->json(['price' => $latest?->price ?? 3.00]);
}

public function updatePricing(Request $request)
{
    $request->validate(['price' => 'required|numeric|min:0.5']);
    \App\Models\JobPricing::create(['price' => $request->price]);
    return response()->json(['message' => 'Price updated']);
}

   public function sendRoundEndedEmail(Request $request)
{
    $request->validate([
        'message' => 'required|string',
        'company_ids' => 'required|array',
        'company_ids.*' => 'exists:users,id',
    ]);

    $companies = User::whereIn('id', $request->company_ids)->get();

    foreach ($companies as $company) {
        Mail::to($company->email)->send(new RoundEndedNotification($request->message, $company->name));
    }

    //  Save to DB
    AdminNotification::create([
        'message' => $request->message,
        'company_ids' => $request->company_ids,
    ]);

    return response()->json(['message' => 'Emails sent and notification saved successfully']);
}

}
