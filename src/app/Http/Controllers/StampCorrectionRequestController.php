<?php

namespace App\Http\Controllers;

use App\Models\CorrectionApplication;
use App\Models\CorrectionStatus;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    /**
     * 申請一覧を表示（一般ユーザー：自分の申請のみ）
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tab = $request->query('tab', 'pending');

        // タブに応じたステータスを取得
        $statusName = $tab === 'approved' ? '承認済み' : '承認待ち';
        $status = CorrectionStatus::where('name', $statusName)->first();

        $applications = CorrectionApplication::where('user_id', $user->id)
            ->when($status, fn ($q) => $q->where('correction_status_id', $status->id))
            ->with(['attendance', 'correctionStatus', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_request.list', [
            'headerType' => 'user',
            'applications' => $applications,
            'activeTab' => $tab === 'approved' ? 'approved' : 'pending',
        ]);
    }
}
