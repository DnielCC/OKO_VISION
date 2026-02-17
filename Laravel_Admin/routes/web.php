<?php

use Illuminate\Support\Facades\Route;

// Ruta principal - Redirige al login o dashboard según autenticación
Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Rutas accesibles sin autenticación
// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Alertas
Route::get('/alertas', function () {
    return view('alertas');
})->name('alertas');

// Usuarios
Route::get('/usuarios', function () {
    return view('usuarios');
})->name('usuarios');

// Reportes
Route::get('/reportes', function () {
    return view('reportes');
})->name('reportes');

// Rutas adicionales de Laravel (mantenidas por compatibilidad)
Route::get('/home', function () {
    return redirect('/dashboard');
});