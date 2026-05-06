<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{

    // 保存を許可する項目を指定
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    // --- ここからクラスの中（メソッド定義） ---


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * 休憩データとのリレーション (1対多)
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    /**
     * 休憩時間の合計を計算（分単位）
     */
    public function total_rest_duration()
    {
        $total = 0;
        // $this->rests でリレーション先のデータを取得
        foreach ($this->rests as $rest) {
            if ($rest->end_time) {
                $total += Carbon::parse($rest->start_time)->diffInMinutes(Carbon::parse($rest->end_time));
            }
        }
        return $total;
    }

    /**
     * 休憩時間を「00:00:00」形式で取得するアクセサ
     * Bladeで {{ $attendance->formatted_rest_time }} として呼べるようになります
     */
    public function getFormattedRestTimeAttribute()
    {
        $minutes = $this->total_rest_duration();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d:00', $hours, $remainingMinutes);
    }

    public function getTotalWorkTimeAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) return '00:00';
        
        $workMinutes = \Carbon\Carbon::parse($this->clock_in)->diffInMinutes($this->clock_out);
        $restMinutes = $this->rests->sum(function ($rest) {
            return $rest->end_time ? \Carbon\Carbon::parse($rest->start_time)->diffInMinutes($rest->end_time) : 0;
        });

        $actualMinutes = $workMinutes - $restMinutes;
        return sprintf('%02d:%02d', floor($actualMinutes / 60), $actualMinutes % 60);
    }
    /**
     * 現在の勤怠ステータスを判定
     * テストケース5 (FN019) に対応
     */
    public function getStatusAttribute()
    {
        if ($this->clock_out) {
            return '退勤済';
        }

        // 終わっていない休憩（end_timeが空）があるか
        $is_resting = $this->rests()->whereNull('end_time')->exists();
        if ($is_resting) {
            return '休憩中';
        }

        if ($this->clock_in) {
            return '出勤中';
        }

        return '勤務外';
    }

    public function getTotalRestTimeAttribute()
    {
        return $this->formatted_rest_time;
    }

} // ← クラスの閉じタグは一番最後
