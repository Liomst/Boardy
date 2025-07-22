<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TodoController;

Route::get('/', [TodoController::class, 'index'])->name('todos.board');
Route::post('/todos', [TodoController::class, 'store']);
Route::put('/todos/reorder', [TodoController::class, 'reorder']);
