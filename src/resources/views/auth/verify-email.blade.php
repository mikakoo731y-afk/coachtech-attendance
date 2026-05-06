@extends('layouts.app')

@section('content')
<div class="login-form">
    <div class="login-form__inner">
        <h2 class="login-form__text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。
        </h2>
            {{-- ダミーボタン風表示 --}}
            <div class="login-form__dummy-btn">
                認証はこちらから
            </div>
            {{-- 認証メール再送機能 --}}
            <form action="{{ route('verification.send') }}" method="POST" class="login-form__resend-form">
                @csrf
                <button type="submit" class="login-form__resend-link">認証メールを再送する</button>
            </form>
        @if (session('status') == 'verification-link-sent')
            <p class="login-form__success">新しい認証メールを送信しました。</p>
        @endif
    </div>
</div>
@endsection
