<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestListController extends Controller
{
    /**
     * 申請一覧を表示
     * 認証ミドルウェアで区別：管理者は全件、一般ユーザーは自分の申請のみ
     */
    public function index(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return app(AdminStampCorrectionRequestController::class)->index($request);
        }

        return app(StampCorrectionRequestController::class)->index($request);
    }
}
