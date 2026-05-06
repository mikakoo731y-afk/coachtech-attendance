<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    // 保存を許可する項目
    protected $fillable = [
        'attendance_id',
        'user_id',
        'proposed_clock_in',
        'proposed_clock_out',
        'reason',
        'status',
    ];

    /**
     * 修正を申請したユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 修正対象の勤怠データとのリレーション
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 休憩の修正申請とのリレーション (1対多)
     */
    public function restCorrectRequests()
    {
        return $this->hasMany(RestCorrectRequest::class);
    }
}
