<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * PG07: 管理者ログイン画面を表示
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(Request $request)
    {
        // 1. バリデーションメッセージを要件通りに修正
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        // role=1 (管理者) であることも条件に含めて認証
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'role' => 1])) {
            $request->session()->regenerate();

            return redirect()->route('admin.attendance.list');
        }

        // 2. 失敗時のメッセージを要件通りに修正
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    /**
     * 管理者ログアウト処理
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user && $user->role === 1) {
            return redirect()->route('admin.login.view');
        }

        return redirect('/login');
    }
}
