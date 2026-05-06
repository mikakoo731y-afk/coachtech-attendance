<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. 認証機能（一般ユーザー）：会員登録バリデーション
     */
    public function test_registration_validation()
    {
        // 名前未入力
        $this->post('/register', ['name' => ''])->assertSessionHasErrors(['name' => 'お名前を入力してください']);

        // メール未入力
        $this->post('/register', ['email' => ''])->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);

        // パスワード8文字未満
        $this->post('/register', ['password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);

        // パスワード不一致
        $this->post('/register', ['password' => 'password123', 'password_confirmation' => 'diff'])->assertSessionHasErrors(['password' => 'パスワードと一致しません']);

        // パスワード未入力
        $this->post('/register', ['password' => ''])->assertSessionHasErrors(['password' => 'パスワードを入力してください']);

        // 正常保存
        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /**
     * 2. ログイン認証機能（一般ユーザー）
     */
    public function test_user_login_validation()
    {
        $user = User::factory()->create(['password' => Hash::make('password123'), 'role' => 0]);

        // メール未入力
        $this->post('/login', ['password' => 'password123'])->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);

        // パスワード未入力
        $this->post('/login', ['email' => $user->email])->assertSessionHasErrors(['password' => 'パスワードを入力してください']);

        // 不一致
        $this->post('/login', ['email' => 'wrong@example.com', 'password' => 'password123'])->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 3. ログイン認証機能（管理者）※一般側で共通テストとして実施
     */
    public function test_admin_login_validation()
    {
        // メール未入力
        $this->post('/admin/login', ['password' => 'password123'])->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);

        // パスワード未入力
        $this->post('/admin/login', ['email' => 'admin@example.com'])->assertSessionHasErrors(['password' => 'パスワードを入力してください']);

        // 不一致
        $this->post('/admin/login', ['email' => 'wrong@example.com', 'password' => 'password123'])->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 4. 日時取得機能
     */
    public function test_current_date_display()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $now = Carbon::now();

        // UIと同じ形式（例: 2026年4月19日）が表示されているか
        // ※Blade側が「Y年n月j日」でない場合は、実際の形式に合わせてください
        $this->actingAs($user)->get('/attendance')->assertSee($now->format('Y年n月j日'));
    }

        /**
     * 5. ステータス確認機能
     */
    public function test_attendance_status_display()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $this->actingAs($user);

        // 勤務外
        $this->get('/attendance')->assertSee('勤務外');

        // 出勤中
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00:00'
        ]);
        $this->get('/attendance')->assertSee('出勤中');

        // 休憩中
        \App\Models\Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00'
        ]);
        $this->get('/attendance')->assertSee('休憩中');

        // 退勤済
        $attendance->update(['clock_out' => '18:00:00']);
        $this->get('/attendance')->assertSee('退勤済');
    }

    /**
     * 6. 出勤機能
     */
    public function test_check_in_function()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $this->actingAs($user);

        // 出勤ボタンの表示確認
        $this->get('/attendance')->assertSee('出勤');
        // 出勤処理後のステータス確認（期待値：「出勤中」）
        $this->post('/attendance/check-in');
        $this->get('/attendance')->assertSee('出勤中');

        // 出勤は一日一回のみ（退勤済みの後にボタンが出ない）
        \App\Models\Attendance::where('user_id', $user->id)->first()->update(['clock_out' => '18:00:00']);
        $this->get('/attendance')->assertDontSee('出勤');

        // 出勤時刻が勤怠一覧画面で確認できる
        $this->get('/attendance/list')->assertSee(Carbon::now()->format('H:i'));
    }

    /**
     * 7. 休憩機能
     */
    public function test_rest_function()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00:00'
        ]);
        $this->actingAs($user);

        // 休憩入ボタンが正しく機能する（期待値：ステータスが「休憩中」）
        $this->get('/attendance')->assertSee('休憩入');
        $this->post('/attendance/rest-start');
        $this->get('/attendance')->assertSee('休憩中');

        // 休憩は一日に何回でもできる（休憩戻後に再度「休憩入」が出るか）
        $this->post('/attendance/rest-end');
        $this->get('/attendance')->assertSee('休憩入');

        // 休憩戻ボタンが正しく機能する（期待値：ステータスが「出勤中」）
        $this->post('/attendance/rest-start');
        $this->get('/attendance')->assertSee('休憩戻');
        $this->post('/attendance/rest-end');
        $this->get('/attendance')->assertSee('出勤中');

        // 休憩時刻が勤怠一覧画面で確認できる
        $this->get('/attendance/list')->assertSee('休憩');
    }

    /**
     * 8. 退勤機能
     */
    public function test_check_out_function()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $this->actingAs($user);

        // 出勤中（出勤後）の状態
        $this->post('/attendance/check-in');
        
        // 退勤ボタン確認と処理（期待値：ステータスが「退勤済」）
        $this->get('/attendance')->assertSee('退勤');
        $this->post('/attendance/check-out');
        $this->get('/attendance')->assertSee('退勤済');

        // 退勤時刻が勤怠一覧画面で確認できる
        $this->get('/attendance/list')->assertSee(Carbon::now()->format('H:i'));
    }

        /**
     * 9. 勤怠一覧情報取得機能（一般ユーザー）
     */
    public function test_user_attendance_list_view()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $this->actingAs($user);

        // 勤怠データの作成（今日、前月、翌月）
        $today = Carbon::today();
        $prevMonth = Carbon::today()->subMonth();
        $nextMonth = Carbon::today()->addMonth();

        $attendanceToday = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        // 1. 勤怠一覧ページを開くと現在の月が表示されている
        $response = $this->get('/attendance/list');
        $response->assertSee($today->format('Y/m'));

        // 2. 自分の勤怠情報が表示されている
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 3. 「前月」を押下した時に前月の情報が表示される
        $responsePrev = $this->get('/attendance/list?month=' . $prevMonth->format('Y-m'));
        $responsePrev->assertSee($prevMonth->format('Y/m'));

        // 4. 「翌月」を押下した時に翌月の情報が表示される
        $responseNext = $this->get('/attendance/list?month=' . $nextMonth->format('Y-m'));
        $responseNext->assertSee($nextMonth->format('Y/m'));

        // 5. 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
        $response->assertSee(route('attendance.show', ['id' => $attendanceToday->id]));
    }

    /**
     * 10. 勤怠詳細情報取得機能（一般ユーザー）
     */
    public function test_user_attendance_detail_view()
    {
        $user = User::factory()->create(['name' => 'テスト 太郎', 'role' => 0, 'email_verified_at' => now()]);
        $this->actingAs($user);

        $date = '2026-04-18';
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => '09:15:00',
            'clock_out' => '18:30:00'
        ]);

        \App\Models\Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00'
        ]);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        // 1. 「名前」がログインユーザーの氏名になっている
        $response->assertSee('テスト 太郎');

        // 2. 「日付」が選択した日付になっている
        // ※Bladeで「2026年4月18日」形式で表示している想定
        $response->assertSee('2026年4月18日');

        // 3. 「出勤・退勤」の時間が打刻と一致している
        $response->assertSee('09:15');
        $response->assertSee('18:30');

        // 4. 「休憩」の時間が打刻と一致している
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

        /**
     * 11. 勤怠詳細情報修正機能（一般ユーザー）
     */
    public function test_user_attendance_correction_request()
    {
        $user = User::factory()->create(['role' => 0, 'email_verified_at' => now()]);
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-18',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $this->actingAs($user);
        $updateUrl = "/attendance/update/{$attendance->id}";

        // 1. 出勤時間が退勤時間より後
        $response = $this->post($updateUrl, [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'reason' => '修正理由'
        ]);
        $response->assertSessionHasErrors(['clock_in' => '出勤時間が不適切な値です']);

        // 2. 休憩開始時間が退勤時間より後
        $response = $this->post($updateUrl, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [['start' => '19:00', 'end' => '19:30']],
            'reason' => '修正理由'
        ]);
        $response->assertSessionHasErrors(['rests.0.start' => '休憩時間が不適切な値です']);

        // 3. 休憩終了時間が退勤時間より後
        $response = $this->post($updateUrl, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [['start' => '12:00', 'end' => '19:00']],
            'reason' => '修正理由'
        ]);
        $response->assertSessionHasErrors(['rests.0.end' => '休憩時間もしくは退勤時間が不適切な値です']);

        // 4. 備考欄が未入力
        $response = $this->post($updateUrl, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => ''
        ]);
        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);

        // 5. 修正申請処理が正常に実行される
        $this->post($updateUrl, [
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'reason' => '時間修正のテスト'
        ]);
        
        // 申請一覧（承認待ち）に自分の申請が表示されていること
        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertSee('時間修正のテスト');
        // 6. 承認待ちの申請がある場合、同じ勤怠に対して再度申請できないこと
        $approvedRequest = \App\Models\AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'proposed_clock_in' => '08:00:00',
            'proposed_clock_out' => '17:00:00',
            'reason' => '承認済みのテスト',
            'status' => 2,
        ]);
        $this->get('/stamp_correction_request/list?tab=approved')
             ->assertSee('承認済みのテスト');

        // 7. 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
        $this->get('/stamp_correction_request/list?tab=pending')
             ->assertSee(route('attendance.show', ['id' => $attendance->id]));
    }


}
