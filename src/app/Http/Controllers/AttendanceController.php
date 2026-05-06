<?php

namespace App\Http\Controllers;

use App\Models\Rest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest; // 追加
use App\Models\RestCorrectRequest;       // 追加
use App\Http\Requests\CorrectionRequest; // 追加（バリデーション用）
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;       // 追加（トランザクション用）
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 打刻画面を表示
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        // Attendance.phpに定義した getStatusAttribute() を活用
        // $attendanceがなければ「勤務外」、あればモデルの判定結果を表示
        $status = $attendance ? $attendance->status : '勤務外';

        return view('attendance.index', compact('status'));
    }
    // 出勤処理
    public function checkIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $exists = Attendance::where('user_id', $user->id)
                            ->where('date', $today)
                            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'すでに出勤済みです');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $now,
        ]);

        return redirect()->back()->with('success', '出勤しました！');
    }

    // 退勤処理
    public function checkOut()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if ($attendance && !$attendance->clock_out) {
            $attendance->update([
                'clock_out' => $now,
            ]);
            return redirect()->back()->with('success', 'お疲れ様でした！');
        }

        return redirect()->back()->with('error', '退勤処理に失敗しました');
    }

    // 休憩入
    public function restStart()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', Carbon::today()->toDateString())
                                ->first();

        // 出勤していないのに休憩URLを叩かれた場合のガード（安全策）
        if (!$attendance) {
            return redirect()->back()->with('error', '出勤打刻がされていません');
        }

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->toTimeString(),
        ]);

        return redirect()->back()->with('success', '休憩に入りました');
    }
    // 休憩戻
    public function restEnd()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', Carbon::today()->toDateString())
                                ->first();

        $rest = Rest::where('attendance_id', $attendance->id)
                    ->whereNull('end_time')
                    ->latest()
                    ->first();

        if ($rest) {
            $rest->update([
                'end_time' => Carbon::now()->toTimeString(),
            ]);
            return redirect()->back()->with('success', '休憩から戻りました');
        }

        return redirect()->back()->with('error', '休憩データが見つかりません');
    }

    // 勤怠一覧
    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    // 詳細画面
    public function show($id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        // すでに申請中（ステータス1: 承認待ち）のデータがあるか確認
        $isRequested = AttendanceCorrectRequest::where('attendance_id', $attendance->id)
            ->where('status', 1)
            ->exists();

        return view('attendance.detail', compact('attendance', 'isRequested'));
    }

    /**
     * 修正申請の保存 (FN030) - 追加分
     */
    public function update(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $exists = AttendanceCorrectRequest::where('attendance_id', $id)->where('status', 1)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'この勤怠はすでに修正申請中です。');
        }

        DB::transaction(function () use ($request, $attendance) {
            // 1. 勤怠の修正申請を保存
            $correctRequest = AttendanceCorrectRequest::create([
                'attendance_id'     => $attendance->id,
                'user_id'           => Auth::id(),
                'proposed_clock_in'  => $request->clock_in,
                'proposed_clock_out' => $request->clock_out,
                'reason'             => $request->reason,
                'status'             => 1, // 承認待ち
            ]);

            // 2. 休憩の修正申請を保存
            if ($request->has('rests')) {
                foreach ($request->rests as $times) {
                    // 開始時間と終了時間の両方が入力されている場合のみ保存
                    if (!empty($times['start']) && !empty($times['end'])) {
                        RestCorrectRequest::create([
                            'attendance_correct_request_id' => $correctRequest->id,
                            'proposed_start_time' => $times['start'],
                            'proposed_end_time'   => $times['end'],
                        ]);
                    }
                }
            }
        });

        // 申請一覧画面へリダイレクト
        return redirect()->route('correction.list')->with('success', '修正申請を送信しました');
    }
}
