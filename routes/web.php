<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/teacher/dashboard', function () {
    return view('teacher.dashboard');
});

// Add student dashboard route
Route::get('/student/dashboard', function () {
    return view('student.dashboard');
});

Route::get('/parent/dashboard', function () {
    return view('parent.dashboard');
});
