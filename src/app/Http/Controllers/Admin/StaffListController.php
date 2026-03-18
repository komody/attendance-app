<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffListController extends Controller
{
    /**
     * スタッフ一覧を表示（FN041, FN042）
     */
    public function index(Request $request)
    {
        $users = User::orderBy('name')->get();

        return view('admin.staff.list', [
            'headerType' => 'admin',
            'users' => $users,
        ]);
    }
}
