<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;

// --------------------------------------------------
// 公開ルート
// --------------------------------------------------
Route::redirect('/', '/attendance');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// PG07: 管理者ログイン関連
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login.view');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.exec'); // ★ログイン実行を追加

// --------------------------------------------------
// 一般ユーザー・管理者 共通認証ルート
// --------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // ログアウト処理を追加しておくと安心です
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // PG06 & PG12: 申請一覧
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('correction.list');

    // PG13: 修正申請承認画面
    Route::prefix('stamp_correction_request')->group(function () {
        Route::get('/approve/{attendance_correct_request_id}', [CorrectionRequestController::class, 'show'])->name('admin.approve.view');
        Route::post('/approve/{attendance_correct_request_id}', [CorrectionRequestController::class, 'approve'])->name('admin.approve.exec');
    });

    // --------------------------------------------------
    // 一般ユーザー専用 (PG03 - PG05)
    // --------------------------------------------------
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::middleware(['verified'])->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart'])->name('attendance.rest-start');
        Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd'])->name('attendance.rest-end');

        Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
        Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/attendance/update/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    });

    // --------------------------------------------------
    // 管理者専用ルート (PG08 - PG11)
    // --------------------------------------------------
    Route::prefix('admin')->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
        Route::post('/attendance/update/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
        Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
        Route::get('/attendance/staff/{id}', [StaffController::class, 'show'])->name('admin.staff.attendance');
        Route::get('/attendance/export/{id}', [AdminAttendanceController::class, 'export'])->name('admin.attendance.export');
    });
});
