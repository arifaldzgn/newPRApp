<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PRController;
use App\Http\Controllers\PartListController;
use App\Http\Controllers\BookingRoomController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\TicketDownloadController;


// ------------------------- Public Routes -------------------------

Route::get('/', function () {
    return redirect()->route('login');
});

// Login / Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Printed Ticket (no auth required)
Route::get('/printTicket/{ticketCode}', [PRController::class, 'print'])->name('printTicket');

// ------------------------- Protected Routes -------------------------
Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [AuthController::class, 'data'])->name('dashboard.data');

    // ========================= START OF PR ROUTES =========================
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
    Route::put('/ticket/{id}/reject', [PRController::class, 'rejectTicket']);
    Route::put('/ticket/{id}/approve', [PRController::class, 'approveTicket'])->name('approveTicket');
    Route::put('/ticket/{id}/purchasing_approve', [PRController::class, 'purchasingApprove'])->name('purchasingApprove');

    Route::get('/printPdf/{ticketCode}', [PRController::class, 'printPdf'])->name('printPdf');
    // Delete individual part from request
    Route::delete('/delete-part/{id}', [PRController::class, 'destroyPart']);
    Route::get('/retrieve-part-name/{id}', [PRController::class, 'retrievePartName'])->name('retrieve.part.name');
    // ========================== END OF PR ROUTES ==========================

    // ===================== START OF USER MANAGEMENT =======================
    Route::prefix('users')->middleware(['auth'])->group(function () {
        Route::get('/{id}', [AccountController::class, 'user_details'])->name('users.show');
        Route::post('/{id}/update', [AccountController::class, 'update_account'])->name('users.update');
        Route::delete('/{id}', [AccountController::class, 'delete_account'])->name('users.destroy');
    });

    Route::get('get-user-details/{id}', [AccountController::class, 'user_details'])->name('user_details');
    Route::post('/update-user-details', [AccountController::class, 'update_account'])->name('update_account');
    Route::delete('/account/{id}', [AccountController::class, 'delete_account']);
    // ====================== END OF USER MANAGEMENT ========================

    // ================== START OF DEPARTMENT MANAGEMENT ====================
    Route::prefix('departments')->middleware(['auth'])->group(function () {
        Route::get('/', [AccountController::class, 'department'])->name('departments');
        Route::post('/', [AccountController::class, 'create_department'])->name('departments.store');
        Route::get('/{id}', [AccountController::class, 'department_details'])->name('departments.show');
        Route::post('/{id}/update', [AccountController::class, 'update_department'])->name('departments.update');
        Route::delete('/{id}', [AccountController::class, 'delete_department'])->name('departments.destroy');
        Route::get('/{id}/users', [AccountController::class, 'getDepartmentUsers'])->name('departments.users');
    });
    // =================== END OF DEPARTMENT MANAGEMENT =====================

    Route::get('account', [AccountController::class, 'account'])->name('account');
    Route::get('dept_user_account', [AccountController::class, 'deptUserAccount'])->name('dept_user_account');
    Route::post('account', [AccountController::class, 'create_account'])->name('create_account');

    Route::get('roles', [AccountController::class, 'role'])->name('role');
    Route::get('/get-role-users/{role}', [AccountController::class, 'getRoleUsers'])->name('get_role_users');

    Route::get('user_log', [AccountController::class, 'user_log'])->name('user_log');

    // ======================== START OF PART LIST ==========================
    Route::get('partlist', [PartListController::class, 'index'])->name('partlist');
    Route::get('partlist_log', [PartListController::class, 'log'])->name('partlistLog');
    Route::post('partlist', [PartListController::class, 'create'])->name('create_partlist');
    Route::post('refund_stock', [PartListController::class, 'refundStock'])->name('refund_stock');
    Route::delete('/partlist/{id}', [PartListController::class, 'delete_part']);
    Route::get('/get-part-details/{id}', [PartListController::class, 'getPartDetails']);
    Route::post('/update-part-details', [PartListController::class, 'updatePartList']);
    Route::post('/validate-stock', [PartListController::class, 'validateStock'])->name('validate.stock');
    // ========================= END OF PART LIST ===========================

    // ======================== START OF BOOKING ROOM =======================
    Route::get('booking_room', [BookingRoomController::class, 'index'])->name('booking_room');
    Route::get('/booking_room/list', [BookingRoomController::class, 'getEvents'])->name('booking_room.list');
    Route::post('/booking_room/store', [BookingRoomController::class, 'store'])->name('booking_room.store');
    Route::delete('/booking_room/{id}', [BookingRoomController::class, 'destroy'])->name('booking_room.destroy');
    // ========================= END OF BOOKING ROOM ========================

    // ========================== NOTIFICATION ==============================
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/read', [NotificationController::class, 'read'])->name('notifications.read');
    // Route::get('/notifications', [NotificationController::class, 'all'])->name('notifications.all');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'all'])->name('notifications.all');

    // Test email route
    Route::get('/test-mail', function () {
        Mail::raw('This is a test email from Laravel using Gmail SMTP.', function ($message) {
            $message->to('ariffalkzn@gmail.com')
                    ->subject('Test Mail from Laravel');
        });

        return 'Sent!';
    });
    // ======================== END OF NOTIFICATION =========================
});

// Ticket Downloader
Route::get('/downloadApprovedTicket', [TicketDownloadController::class, 'listApprovedTickets']);



Route::get('/models-first', function () {
    $modelsPath = app_path('Models');
    $models = collect(File::allFiles($modelsPath))
        ->map(function ($file) use ($modelsPath) {
            return 'App\\Models\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );
        });

    $data = [];

    foreach ($models as $model) {
        if (class_exists($model)) {
            try {
                $data[$model] = $model::query()->first();
            } catch (\Throwable $e) {
                $data[$model] = '⚠️ Error: ' . $e->getMessage();
            }
        }
    }

    return response()->json($data);
});
