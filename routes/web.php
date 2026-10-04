<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::get('/tasks', function () {
    return view('tasks');
})->name('tasks');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/users', function () {
    return view('users');
})->name('users');
