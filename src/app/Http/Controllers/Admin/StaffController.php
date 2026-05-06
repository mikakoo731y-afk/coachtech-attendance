<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;


class StaffController extends Controller
{
    /**
     * 全スタッフ（一般ユーザー）の一覧表示 (PG10)
     */
    public function index()
    {
        // 管理者を除いた一般ユーザー(role: 0)のみを取得 (FN041)
        $users = User::where('role', 0)->get();

        return view('admin.staff.list', compact('users'));
    }

    public function show(Request $request, $id)
    {
        $staff = User::where('role', 0)->findOrFail($id);

        // クエリパラメータから月を取得、なければ現在の月 (FN044)
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        // そのユーザーの、指定月のデータを取得 (FN043)
        $attendances = Attendance::with('rests')
            ->where('user_id', $staff->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        // 前月と翌月のリンク用
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('admin.staff.attendance', compact('staff', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }
}
