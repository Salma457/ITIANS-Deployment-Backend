<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;

class PublicJobController extends Controller
{
    public function show(Request $request, $id)
    {
        $job = Job::with(['employer', 'statusChanger'])->findOrFail($id);

        $user = $request->user();

        if (!$user || $user->role === 'itian') {
            $job->increment('views_count');
        }

        return response()->json([
            'data' => $job
        ]);
    }
}

