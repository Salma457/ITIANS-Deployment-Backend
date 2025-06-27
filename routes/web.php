<?php

use App\Mail\RegistrationRequestReviewed;
use App\Models\ItianRegistrationRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
     return view('welcome');
});

