<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/hello', 'hello')->name('hello');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('tickets', 'pages::tickets.index')->name('tickets.index');
    Route::livewire('tickets', 'pages::tickets.index')->name('tickets.index');
    Route::livewire('tickets/create', 'pages::tickets.create')->name('tickets.create');
    Route::livewire('tickets/{ticket}', 'pages::tickets.show')->name('tickets.show');
});

require __DIR__.'/settings.php';
