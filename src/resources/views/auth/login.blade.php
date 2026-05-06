@extends('layouts.app')

@section('content')
<div class="login">
    <div class="login__inner">
        <h2 class="login__heading">ログイン</h2>
        <form class="login__form" action="{{ route('login') }}" method="POST" novalidate>
            @csrf
            {{-- メールアドレス --}}
            <div class="login__group">
                <div class="login__group-row">
                    <label class="login__label" for="email">メールアドレス</label>
                    <input class="login__input" type="email" name="email" id="email" value="{{ old('email') }}" autofocus>
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
            <div class="login__actions">
                <button type="submit" class="login__button">ログインする</button>
            </div>
        </form>

        <div class="login__link">
            <a href="/register" class="login__link-text">会員登録はこちら</a>
        </div>
    </div>
</div>
@endsection
