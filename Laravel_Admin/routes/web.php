<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', function () {
    return view('auth.login');
})->name('login');


Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


Route::get('/alertas', function () {
    return view('alertas');
})->name('alertas');


Route::get('/usuarios', function () {
    return view('usuarios');
})->name('usuarios');


Route::get('/reportes', function () {
    return view('reportes');
})->name('reportes');


Route::get('/home', function () {
    return redirect('/dashboard');
});