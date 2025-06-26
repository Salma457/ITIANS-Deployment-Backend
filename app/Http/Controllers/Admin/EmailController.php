<?php
// app/Http/Controllers/Admin/EmailController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class EmailController extends Controller
{
     public function getEmployers()
    {
        $employers = User::where('role', 'employer')
                         ->select('id', 'name', 'email')
                         ->get();

        return response()->json($employers);
    }
}
