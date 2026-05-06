<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 管理者ユーザーの作成 (role: 1)
        User::create([
            'name' => '管理者 太郎',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1,
            'email_verified_at' => now(),
        ]);

        // 2. 一般ユーザーの作成 (role: 0)
        $user = User::create([
            'name' => 'テスト ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        // 3. 昨日の勤怠ダミーデータ作成 (テストケース9, 10用)
        $yesterday = Carbon::yesterday();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 4. 休憩ダミーデータ作成 (1日2回休憩の例: テストケース7用)
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '15:00:00',
            'end_time' => '15:15:00',
        ]);
    }
}
