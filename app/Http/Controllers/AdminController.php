<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPricing;

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

    
}
