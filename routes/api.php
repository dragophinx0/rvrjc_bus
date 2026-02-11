<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\BusTripController;
use App\Http\Controllers\Api\SeatReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Verification (Global check for verified users except for status check)
    Route::get('/verification/status', [VerificationController::class, 'status']);

    // VERIFIED USERS ONLY
    Route::middleware('verified')->group(function () {

        // Hierarchical Verification
        Route::get('/verification/pending', [VerificationController::class, 'pending'])->middleware('role:admin,bus_coordinator,faculty');
        Route::post('/verification/approve/{user}', [VerificationController::class, 'approve'])->middleware('role:admin,bus_coordinator,faculty');
        Route::post('/verification/reject/{user}', [VerificationController::class, 'reject'])->middleware('role:admin,bus_coordinator,faculty');

        // Polls (Daily Voting)
        Route::get('/polls', [PollController::class, 'index']);
        Route::post('/polls/{poll}/vote', [PollController::class, 'vote']);
        Route::get('/polls/stats', [PollController::class, 'stats'])->middleware('role:admin,bus_coordinator');

        // Buses
        Route::get('/buses', [BusController::class, 'index']);
        Route::get('/buses/{bus}', [BusController::class, 'show']);
        Route::post('/buses', [BusController::class, 'store'])->middleware('role:admin,bus_coordinator');
        Route::put('/buses/{bus}', [BusController::class, 'update'])->middleware('role:admin,bus_coordinator');
        Route::delete('/buses/{bus}', [BusController::class, 'destroy'])->middleware('role:admin,bus_coordinator');

        Route::post('/buses/{bus}/select', [BusController::class, 'selectBus'])->middleware('role:driver');
        Route::post('/buses/location', [BusController::class, 'updateLocation'])->middleware('role:driver');

        // Bus Layout & Seating
        Route::get('/buses/{bus}/layout', [SeatController::class, 'getBusLayout']);
        Route::post('/buses/{bus}/generate-layout', [SeatController::class, 'generateLayout'])->middleware('role:admin,bus_coordinator');

        // Trips & Boarding
        Route::post('/buses/{bus}/start-boarding', [BusTripController::class, 'startBoarding'])->middleware('role:driver');
        Route::post('/trips/{trip}/start-journey', [BusTripController::class, 'startJourney'])->middleware('role:driver');
        Route::post('/trips/{trip}/complete', [BusTripController::class, 'completeTrip'])->middleware('role:driver');

        // Reservations
        Route::post('/trips/{trip}/seats/{seat}/reserve', [SeatReservationController::class, 'reserve']);
        Route::post('/reservations/{reservation}/confirm', [SeatReservationController::class, 'confirm']);
        Route::post('/reservations/{reservation}/extend', [SeatReservationController::class, 'extend']);

        // Routes
        Route::get('/routes', [RouteController::class, 'index']);
        Route::get('/routes/{route}', [RouteController::class, 'show']);
        Route::post('/routes', [RouteController::class, 'store'])->middleware('role:admin,bus_coordinator');
        Route::delete('/routes/{route}', [RouteController::class, 'destroy'])->middleware('role:admin,bus_coordinator');

        // Timetables
        Route::get('/timetables', [TimetableController::class, 'index']);
        Route::post('/timetables', [TimetableController::class, 'store'])->middleware('role:admin,bus_coordinator');
        Route::put('/timetables/{timetable}', [TimetableController::class, 'update'])->middleware('role:admin,bus_coordinator');
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])->middleware('role:admin,bus_coordinator');

        // Users
        Route::get('/users', [UserController::class, 'index'])->middleware('role:admin,bus_coordinator');
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('role:admin');
    });

    // Reference Data
    Route::get('/reference-data', [UserController::class, 'referenceData']);
});
