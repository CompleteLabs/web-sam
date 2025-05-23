<?php

use App\Helpers\SendNotif;
use App\Http\Controllers\API\LeadController;
use App\Http\Controllers\API\NooController;
use App\Http\Controllers\API\OutletController;
use App\Http\Controllers\API\PlanVisitController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VisitController;
use App\Http\Controllers\Auth\WhatsappOtpController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum', 'logku'])->group(function () {
    // USER
    Route::get('user', [UserController::class, 'profile']);
    Route::post('user/logout', [UserController::class, 'logout']);

    // OUTLET
    Route::get('outlet', [OutletController::class, 'index']);
    Route::get('outlet/{kode}', [OutletController::class, 'show']);
    Route::put('outlet/{kode}', [OutletController::class, 'update']);

    // VISIT
    Route::get('visit', [VisitController::class, 'index']);
    Route::get('visit/{id}', [VisitController::class, 'show']);
    Route::post('visit', [VisitController::class, 'store']);
    Route::put('visit/{id}', [VisitController::class, 'update']);
    Route::get('visit/check', [VisitController::class, 'check']);
    Route::get('visit/monitor', [VisitController::class, 'monitor']);

    // PLAN VISIT
    Route::get('planvisit', [PlanVisitController::class, 'index']);
    Route::post('planvisit', [PlanVisitController::class, 'store']);
    Route::delete('planvisit/{id}', [PlanVisitController::class, 'destroy']);

    // NOO
    Route::get('noo/getbu', [NooController::class, 'getbu']);
    Route::get('noo/getdiv', [NooController::class, 'getdiv']);
    Route::get('noo/getreg', [NooController::class, 'getreg']);
    Route::get('noo/getclus', [NooController::class, 'getclus']);
    Route::post('noo', [NooController::class, 'submit']);
    Route::get('noo/all', [NooController::class, 'all']);
    Route::get('noo', [NooController::class, 'fetch']);
    Route::get('noo/{kodeOutlet}', [NooController::class, 'singleOutlet']);
    Route::get('nooOutlet', [NooController::class, 'getnoooutlet']);

    Route::post('noo/confirm', [NooController::class, 'confirm']);
    Route::post('noo/approved', [NooController::class, 'approved']);
    Route::post('noo/reject', [NooController::class, 'reject']);

    // LEAD
    Route::post('lead', [LeadController::class, 'create']);
    Route::post('lead/update', [LeadController::class, 'update']);
});

// Route::post('user/register', [UserController::class, 'register']);
Route::post('user/login', [UserController::class, 'login']);
Route::post('user/send-otp', [WhatsappOtpController::class, 'sendOtp']);
Route::post('user/verify-otp', [WhatsappOtpController::class, 'verifyOtp']);

Route::post('notif', [SendNotif::class, 'sendMessage']);

Route::get('divisi', [SettingController::class, 'getdivisi']);
Route::get('region', [SettingController::class, 'getregion']);

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World',
        'status' => 200,
    ]);
});
// Route::get('/tes/outlet', [OutletController::class, 'all']);
// Route::post('/outlet/delete', [outlet::class, 'deleteBulk']);
