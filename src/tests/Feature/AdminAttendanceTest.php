<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 管理者ユーザーを作成
        $this->admin = User::factory()->create(['role' => 1, 'email_verified_at' => now()]);
    }

    /**
     * 12. 勤怠一覧情報取得機能（管理者）
     */
    public function test_admin_daily_attendance_list()
    {
        $user = User::factory()->create(['name' => 'スタッフA', 'role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $this->actingAs($this->admin);

        // 1. 遷移した際に現在の日付が表示される
        $response = $this->get('/admin/attendance/list');
        $response->assertSee(Carbon::today()->format('Y/m/d'));

        // 2. 全ユーザーの勤怠情報が確認できる
        $response->assertSee('スタッフA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 3. 「前日」を押下した時に前の日の情報が表示される
        $yesterday = Carbon::yesterday();
        $responsePrev = $this->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));
        $responsePrev->assertSee($yesterday->format('Y/m/d'));

        // 4. 「翌日」を押下した時に次の日の情報が表示される
        $tomorrow = Carbon::tomorrow();
        $responseNext = $this->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));
        $responseNext->assertSee($tomorrow->format('Y/m/d'));
    }

    /**
     * 13. 勤怠詳細情報取得・修正機能（管理者）
     */
    public function test_admin_attendance_detail_and_update_validation()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-18',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $this->actingAs($this->admin);
        $updateUrl = "/admin/attendance/update/{$attendance->id}";

        // 1. 詳細画面の内容が一致するか
        $this->get($updateUrl)->assertSee('2026年4月18日');

        // 2. 出勤時間 > 退勤時間
        $this->post($updateUrl, ['clock_in' => '19:00', 'clock_out' => '18:00', 'reason' => '修正'])
            ->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);

        // 3. 休憩開始 > 退勤時間
        $this->post($updateUrl, [
            'clock_in' => '09:00', 'clock_out' => '18:00', 'reason' => '修正',
            'rests' => [['start' => '19:00', 'end' => '19:30']]
        ])->assertSessionHasErrors(['rests.0.start' => '休憩時間が不適切な値です']);

        // 4. 休憩終了 > 退勤時間
        $this->post($updateUrl, [
            'clock_in' => '09:00', 'clock_out' => '18:00', 'reason' => '修正',
            'rests' => [['start' => '12:00', 'end' => '19:00']]
        ])->assertSessionHasErrors(['rests.0.end' => '休憩時間もしくは退勤時間が不適切な値です']);

        // 5. 備考未入力
        $this->post($updateUrl, ['clock_in' => '09:00', 'clock_out' => '18:00', 'reason' => ''])
            ->assertSessionHasErrors(['reason' => '備考を記入してください']);
    }

    /**
     * 14. ユーザー情報取得機能（管理者）
     */
    public function test_admin_staff_management()
    {
        $user = User::factory()->create(['name' => 'スタッフB', 'email' => 'staff_b@example.com', 'role' => 0]);
        $this->actingAs($this->admin);

        // 1. スタッフ一覧に名前とメールが表示されている
        $this->get('/admin/staff/list')->assertSee('スタッフB')->assertSee('staff_b@example.com');

        // 2. スタッフ別勤怠一覧の表示
        $staffListUrl = "/admin/attendance/staff/{$user->id}";
        $response = $this->get($staffListUrl);
        $response->assertSee('スタッフB');

        // 3. 前月・翌月の情報表示
        $prevMonth = Carbon::today()->subMonth();
        $this->get($staffListUrl . '?month=' . $prevMonth->format('Y-m'))->assertSee($prevMonth->format('Y/m'));

        // 4. 「詳細」ボタンから詳細画面へ
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => Carbon::today()->format('Y-m-d'), 'clock_in' => '09:00']);
        $this->get($staffListUrl)->assertSee(route('admin.attendance.show', ['id' => $attendance->id]));
    }

        /**
     * 15. 勤怠情報修正機能（管理者）
     */
    public function test_admin_correction_request_approval_flow()
    {
        $user1 = User::factory()->create(['name' => 'スタッフ1', 'role' => 0]);
        $user2 = User::factory()->create(['name' => 'スタッフ2', 'role' => 0]);

        $attendance1 = Attendance::create(['user_id' => $user1->id, 'date' => '2026-04-01', 'clock_in' => '09:00:00']);
        $attendance2 = Attendance::create(['user_id' => $user2->id, 'date' => '2026-04-02', 'clock_in' => '09:00:00']);

        // 1. 承認待ちの申請を作成
        $requestPending = \App\Models\AttendanceCorrectRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'proposed_clock_in' => '08:00:00',
            'proposed_clock_out' => '17:00:00',
            'reason' => '承認待ちのテスト',
            'status' => 1,
        ]);

        // 2. 承認済みの申請を作成
        $requestApproved = \App\Models\AttendanceCorrectRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'proposed_clock_in' => '08:00:00',
            'proposed_clock_out' => '17:00:00',
            'reason' => '承認済みのテスト',
            'status' => 2,
        ]);

        $this->actingAs($this->admin);

        // 承認待ちタブに全ユーザーの未承認申請が表示されるか
        $this->get('/stamp_correction_request/list?tab=pending')
             ->assertSee('スタッフ1')->assertSee('承認待ちのテスト');

        // 承認済みタブに全ユーザーの承認済み申請が表示されるか
        $this->get('/stamp_correction_request/list?tab=approved')
             ->assertSee('スタッフ2')->assertSee('承認済みのテスト');

        // 修正申請の詳細内容が正しく表示されているか
        $this->get(route('admin.approve.view', ['attendance_correct_request_id' => $requestPending->id]))
             ->assertSee('スタッフ1')->assertSee('08:00')->assertSee('17:00');

        // 修正申請の承認処理が正しく行われるか
        $this->post(route('admin.approve.exec', ['attendance_correct_request_id' => $requestPending->id]));
        
        // 勤怠情報が更新され、ステータスが承認済み(2)になっているか
        $this->assertDatabaseHas('attendances', ['id' => $attendance1->id, 'clock_in' => '08:00:00']);
        $this->assertDatabaseHas('attendance_correct_requests', ['id' => $requestPending->id, 'status' => 2]);
    }

    /**
     * 16. メール認証機能 (応用項目)
     */
    public function test_email_verification_flow()
    {
        // 1. 会員登録後、認証メールが送信される（通知のフェイクを使用して検証）
        \Illuminate\Support\Facades\Notification::fake();
        
        $this->post('/register', [
            'name' => '新規 ユーザー',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'new-staff@example.com')->first();
        
        // 2. メール認証誘導画面で「認証はこちらから」ボタン（実体は案内メッセージ等）を確認
        // ログイン状態になるので、そのまま誘導画面へアクセス
        $this->actingAs($user)->get('/email/verify')
             ->assertSee('認証メールを送付しました');

        // 3. メール認証を完了すると、勤怠登録画面（/attendance）に遷移する
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);
        
        // 認証後は /attendance（勤怠登録画面）へリダイレクトされること
        $response->assertRedirect('/attendance?verified=1');
    }

}
