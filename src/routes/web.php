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
    Route::get('/attendance/{id}', [App\Http\Controllers\AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/correction', [App\Http\Controllers\AttendanceDetailController::class, 'storeCorrection'])->name('attendance.correction.store');
    Route::post('/attendance/clock-in', [App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/break-start', [App\Http\Controllers\AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/attendance/break-end', [App\Http\Controllers\AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    Route::get('/stamp-correction-requests', fn () => view('stamp_correction_request.list', ['headerType' => 'user']))->name('stamp_correction_request.list');
});

// 管理者
Route::get('/admin/login', fn () => view('admin.login'))->name('admin.login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance/list', fn () => view('admin.attendance.list', ['headerType' => 'admin']))->name('attendance.list');
    Route::get('/staff/list', fn () => view('admin.staff.list', ['headerType' => 'admin']))->name('staff.list');
    Route::get('/stamp-correction-requests', fn () => view('stamp_correction_request.list', ['headerType' => 'admin']))->name('stamp_correction_request.list');
    Route::post('/logout', function (Request $request) {
        // 管理者ログアウト処理（管理者認証実装時に更新）
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('logout');
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
