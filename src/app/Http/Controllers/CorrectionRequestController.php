<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\DB;

class CorrectionRequestController extends Controller
{
    /**
     * 申請一覧画面の表示 (PG06 / PG12)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'pending');
        $status = ($tab === 'approved') ? 2 : 1;

        $query = AttendanceCorrectRequest::with(['attendance', 'user']);

        // 管理者(role=1)以外は自分のデータのみ
        if ($user->role !== 1) {
            $query->where('user_id', $user->id);
        }

        $requests = $query->where('status', $status)
                        ->orderBy('created_at', 'desc')
                        ->get();

        // 管理者と一般ユーザーでViewを切り替え
        if ($user->role === 1) {
            return view('admin.correction_request.list', compact('requests', 'tab'));
        }

        return view('correction_request.list', compact('requests', 'tab'));
    }

    /**
     * 修正申請承認画面の表示 (PG13)
     * 引数名を routes/web.php の定義 {attendance_correct_request_id} と合わせる
     */
    public function show($attendance_correct_request_id)
    {
        $request = AttendanceCorrectRequest::with(['user', 'attendance', 'restCorrectRequests'])
                    ->findOrFail($attendance_correct_request_id);

        return view('admin.correction_request.approve', compact('request'));
    }

    /**
     * 修正申請の承認処理
     */
    public function approve(Request $request, $attendance_correct_request_id)
    {
        $correctRequest = AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

        DB::transaction(function () use ($correctRequest) {
            $attendance = Attendance::findOrFail($correctRequest->attendance_id);

            // 1. 勤怠データの更新
            $attendance->update([
                'clock_in' => $correctRequest->proposed_clock_in,
                'clock_out' => $correctRequest->proposed_clock_out,
            ]);

            // 2. 休憩データの差し替え
            Rest::where('attendance_id', $attendance->id)->delete();
            foreach ($correctRequest->restCorrectRequests as $proposedRest) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $proposedRest->proposed_start_time,
                    'end_time' => $proposedRest->proposed_end_time,
                ]);
            }

            // 3. 承認ステータスを更新 (2: 承認済み)
            $correctRequest->update(['status' => 2]);
        });

        // 定義書のパス /stamp_correction_request/list にリダイレクト
        return redirect()->back()->with('success', '承認が完了しました');
    }
}
