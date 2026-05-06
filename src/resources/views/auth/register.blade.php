@extends('layouts.app')

@section('content')
<div class="login">
    <div class="login__inner">
        <h2 class="login__heading">会員登録</h2>
        <form class="login__form" action="{{ route('register') }}" method="POST" novalidate>
            @csrf
            {{-- 名前 --}}
            <div class="login__group">
                <div class="login__group-row">
                    <label class="login__label" for="name">名前</label>
                    <input class="login__input" type="text" name="name" id="name" value="{{ old('name') }}" autofocus>
                </div>
                @error('name')
                    <p class="login__error-msg">{{ $message }}</p>
                @enderror
            </div>
            {{-- メールアドレス --}}
            <div class="login__group">
                <div class="login__group-row">
                    <label class="login__label" for="email">メールアドレス</label>
                    <input class="login__input" type="email" name="email" id="email" value="{{ old('email') }}">
                </div>
                @error('email')
                    <p class="login__error-msg">{{ $message }}</p>
                @enderror
            </div>
            {{-- パスワード --}}
            <div class="login__group">
                <div class="login__group-row">
                    <label class="login__label" for="password">パスワード</label>
                    <input class="login__input" type="password" name="password" id="password">
                </div>
                @error('password')
                    <p class="login__error-msg">{{ $message }}</p>
                @enderror
            </div>
            {{-- パスワード確認 --}}
            <div class="login__group">
                <div class="login__group-row">
                    <label class="login__label" for="password_confirmation">パスワード確認</label>
                    <input class="login__input" type="password" name="password_confirmation" id="password_confirmation">
                </div>
            </div>
            <div class="login__actions">
                <button type="submit" class="login__button">登録する</button>
            </div>
        </form>

        <div class="login__link">
            <a href="/login" class="login__link-text">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection
