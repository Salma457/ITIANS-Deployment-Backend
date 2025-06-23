<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
   public function index(Request $request)
{
    try {
        $user = $request->user();

        if ($user->role !== 'itian') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notifications = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    } catch (\Exception $e) {
        Log::error('Notification Fetch Error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}
}
