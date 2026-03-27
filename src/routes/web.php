<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 勤怠（認証必須）
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/list', fn () => redirect()->route('attendance.list', ['year' => now()->year, 'month' => now()->month]));
    Route::get('/attendance/list/{year}/{month}', [App\Http\Controllers\AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{year}/{month}/{day}', [App\Http\Controllers\AttendanceDetailController::class, 'showByDate'])->name('attendance.detail.date');
    Route::get('/attendance/detail/{id}', [App\Http\Controllers\AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/correction', [App\Http\Controllers\AttendanceDetailController::class, 'storeCorrection'])->name('attendance.correction.store');
    Route::post('/attendance/clock-in', [App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/break-start', [App\Http\Controllers\AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [App\Http\Controllers\AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
});

// 申請一覧（一般ユーザー・管理者共通パス、認証ミドルウェアで区別）
Route::get('/stamp_correction_request/list', [App\Http\Controllers\StampCorrectionRequestListController::class, 'index'])
    ->middleware(['auth.user.or.admin'])
    ->name('stamp_correction_request.list');

// 修正申請承認画面（管理者のみ）
Route::middleware('auth.admin')->group(function () {
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [App\Http\Controllers\Admin\StampCorrectionRequestController::class, 'showApprove'])
        ->name('stamp_correction_request.approve');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [App\Http\Controllers\Admin\StampCorrectionRequestController::class, 'approveCorrection']);
});

// 管理者（/admin/login, /admin/logout は Fortify が担当）
Route::prefix('admin')->name('admin.')->middleware('auth.admin')->group(function () {
    Route::get('/attendance/list', [App\Http\Controllers\Admin\AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/list/{year}/{month}/{day}', [App\Http\Controllers\Admin\AttendanceListController::class, 'index'])->name('attendance.list.date');
    Route::post('/attendance/correction/approve/{id}', [App\Http\Controllers\Admin\AttendanceDetailController::class, 'approveCorrection'])->name('attendance.correction.approve');
    Route::get('/attendance/staff/{id}/csv/{year}/{month}', [App\Http\Controllers\Admin\StaffAttendanceController::class, 'csv'])->name('attendance.staff.csv.date');
    Route::get('/attendance/staff/{id}/csv', [App\Http\Controllers\Admin\StaffAttendanceController::class, 'csv'])->name('attendance.staff.csv');
    Route::get('/attendance/staff/{id}/{year}/{month}', [App\Http\Controllers\Admin\StaffAttendanceController::class, 'index'])->name('attendance.staff.date');
    Route::get('/attendance/staff/{id}', [App\Http\Controllers\Admin\StaffAttendanceController::class, 'index'])->name('attendance.staff');
    Route::get('/attendance/{id}', [App\Http\Controllers\Admin\AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/{id}', [App\Http\Controllers\Admin\AttendanceDetailController::class, 'update'])->name('attendance.detail.update');
    Route::get('/staff/list', [App\Http\Controllers\Admin\StaffListController::class, 'index'])->name('staff.list');
});

// ログアウト
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// メール認証誘導画面
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール認証処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $user = $request->user();

    // 会員登録時のメール認証の場合
    if (is_null($user->email_verified_at)) {
        $request->fulfill();
    }
    // 初回ログイン時のメール認証の場合
    elseif (is_null($user->first_login_email_verified_at) && session('first_login')) {
        $user->first_login_email_verified_at = now();
        $user->save();
    }

    return redirect()->route('attendance.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();

    if (is_null($user->first_login_email_verified_at) && session('first_login')) {
        $user->notify(new App\Notifications\VerifyEmail);
    } else {
        $user->sendEmailVerificationNotification();
    }

    return back()->with('message', '認証メールを送信しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
