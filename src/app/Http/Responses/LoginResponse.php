<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * ログイン後のリダイレクト先を役割に応じて確実に振り分ける
     */
    public function toResponse($request)
    {
        $user = Auth::user();

        // roleが1（管理者）なら管理画面の勤怠一覧へ、0（一般）なら打刻画面へ
        // intended()を使わず、明示的にリダイレクト先を指定する
        if ($user->role === 1) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->route('attendance.index');
    }
}
