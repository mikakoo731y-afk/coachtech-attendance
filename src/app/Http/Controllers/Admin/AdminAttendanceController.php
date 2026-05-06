<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use App\Models\AttendanceCorrectRequest;
use App\Http\Requests\CorrectionRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    /**
     * PG08: 全スタッフの日次勤怠一覧を表示 (FN034, FN035)
     */
    public function index(Request $request)
    {
        // クエリパラメータから日付を取得、なければ今日
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        // その日の全ユーザーの勤怠を取得
        $attendances = Attendance::with(['user', 'rests'])
            ->where('date', $date)
            ->get();

        // 前日・翌日のリンク用データ
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    /**
     * PG09: スタッフ勤怠詳細の表示 (FN037, FN038)
     */
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        
        // 承認待ちの申請があるか確認（ある場合は編集ロック）
        $isRequested = AttendanceCorrectRequest::where('attendance_id', $attendance->id)
            ->where('status', 1)
            ->exists();

        return view('admin.attendance.detail', compact('attendance', 'isRequested'));
    }

    /**
     * FN040: 管理者による直接修正
     */
    public function update(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            // 1. 勤怠本データを直接更新
            $attendance->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
            ]);

            // 2. 休憩データを物理削除して再作成（洗い替え）
            $attendance->rests()->delete();

            if ($request->has('rests')) {
                foreach ($request->rests as $restData) {
                    if (!empty($restData['start']) && !empty($restData['end'])) {
                        $attendance->rests()->create([
                            'start_time' => $restData['start'],
                            'end_time' => $restData['end'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.attendance.list',['date' => $attendance->date])->with('success', '勤怠情報を修正しました。');
    }

    /**
     * FN045: スタッフ別月次勤怠のCSV出力
     */
    public function export(Request $request, $id)
    {
        $staff = User::where('role', 0)->findOrFail($id);
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::with('rests')
            ->where('user_id', $staff->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        // CSVレスポンスの作成
        return new StreamedResponse(function () use ($attendances, $staff, $month) {
            $stream = fopen('php://output', 'w');
            
            // 文字化け対策（Excel用BOM）
            fwrite($stream, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            // データ行の書き込み
            foreach ($attendances as $attendance) {
                fputcsv($stream, [
                    Carbon::parse($attendance->date)->format('m/d'),
                    $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '',
                    $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '',
                    // 合計時間はModelのアクセサ等で計算している想定
                    $attendance->total_rest_time ?? '',
                    $attendance->total_work_time ?? '',
                ]);
            }
            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"attendance_{$staff->name}_{$month}.csv\"",
        ]);
    }
}
