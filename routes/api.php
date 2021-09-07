<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('auth/login', [AuthController::class, 'login']);
Route::group(['middleware' => 'jwtAuth'], function () {
    Route::get('auth/logout', [AuthController::class, 'logout']);
    Route::get('get-number', [DataController::class, 'getRandomNumber']);
    Route::get('get-chart/{game}/{report}', [DataController::class, 'getChart']);
    Route::get('get-menu', [DataController::class, 'getMenu']);
});
