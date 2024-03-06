<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscribeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/subscribe', [SubscribeController::class, 'post']);
Route::get('/subscribe/{hash}', [SubscribeController::class, 'verify'])->name('subscribe.confirm');
