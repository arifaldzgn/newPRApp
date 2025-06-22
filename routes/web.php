<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PRController;
use App\Http\Controllers\PartListController;
use App\Http\Controllers\BookingRoomController;
use App\Http\Controllers\AccountController;


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard', function () {
        return view('dashboard.dashboard'); 
    })->name('dashboard');

    // Start Of PR
    Route::get('pr_create', [PRController::class, 'index'])->name('pr_create');
            Route::post('ticket', [PRController::class, 'create'])->name('pr_request_create');
    Route::get('/retrieve-part-details', [PRController::class, 'retrievePartDetails'])->name('retrieve.part.details');
        Route::get('/retrieve-part-name/{id}', [PRController::class, 'retrievePartName'])->name('retrieve.part.name');
        // Pending Ticket
        Route::get('/pending', [PRController::class, 'pending'])->name('pending');
        // Approved Ticket
        Route::get('/approved', [PRController::class, 'approved'])->name('approved');
        // Rejected Ticket
        Route::get('/rejected', [PRController::class, 'rejected'])->name('rejected');
        Route::get('/ticketDetails/{id}', [PRController::class, 'show'])->name('ticketDetails');
        Route::post('/updateTicket', [PRController::class, 'update']);
        Route::post('/updateTicketR', [PRController::class, 'updateR']);
        Route::delete('/ticket/{id}', [PRController::class, 'destroy']);
        Route::PUT('/ticket/{id}/reject', [PRController::class, 'rejectTicket']);
        Route::PUT('/ticket/{id}/approve', [PRController::class, 'approveTicket'])->name('approveTicket');
        Route::get('/printPdf/{ticketCode}', [PRController::class, 'printPdf'])->name('printPdf');
        // Delete individual part from request
        Route::delete('/delete-part/{id}', [PRController::class, 'destroyPart']);
    // End Of PR

    // Account 
    Route::get('account', [AccountController::class, 'account'])->name('account');
    Route::post('account', [AccountController::class, 'create_account'])->name('create_account');
    Route::get('get-user-details/{id}', [AccountController::class, 'user_details'])->name('user_details');
    Route::post('/update-user-details', [AccountController::class, 'update_account'])->name('update_account');
    Route::delete('/account/{id}', [AccountController::class, 'delete_account']);
        // Dept
        Route::get('department', [AccountController::class, 'department'])->name('department');
        Route::post('department', [AccountController::class, 'create_department'])->name('create_department');
        Route::get('get-department-details/{id}', [AccountController::class, 'department_details'])->name('department_details');
        Route::post('/update-department-details', [AccountController::class, 'update_department'])->name('update_department');
        Route::delete('/department/{id}', [AccountController::class, 'delete_department']);
        Route::get('/get-department-users/{id}', [AccountController::class, 'getDepartmentUsers'])->name('get_department_users');
        // End Of Dept

        // Role
        Route::get('role', [AccountController::class, 'role'])->name('role');
        Route::get('/get-role-users/{role}', [AccountController::class, 'getRoleUsers'])->name('get_role_users');


    // End Of Account

    //Start Of Part List
    Route::get('partlist', [PartListController::class, 'index'])->name('partlist');
    Route::post('partlist', [PartListController::class, 'create'])->name('create_partlist');
    Route::post('refund_stock', [PartListController::class, 'refundStock'])->name('refund_stock');
    Route::delete('/partlist/{id}', [PartListController::class, 'delete_part']);
    Route::get('/get-part-details/{id}', [PartListController::class, 'getPartDetails']);
    Route::post('/update-part-details', [PartListController::class, 'updatePartList']);
    // End Of Part List


    
    // Booking Room
    Route::get('booking_room', [BookingRoomController::class, 'index'])->name('booking_room');
    Route::get('/booking_room/list', [BookingRoomController::class, 'getEvents'])->name('booking_room.list');
    Route::post('/booking_room/store', [BookingRoomController::class, 'store'])->name('booking_room.store');
    Route::delete('/booking_room/{id}', [BookingRoomController::class, 'destroy'])->name('booking_room.destroy');
    // End Of Booking Room
    
});

