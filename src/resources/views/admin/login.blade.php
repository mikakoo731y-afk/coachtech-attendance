@extends('layouts.admin')

@section('content')
<div class="login">
    <div class="login__inner">
        <h2 class="login__heading">管理者ログイン</h2>

        <form class="login__form" action="{{ route('admin.login.exec') }}" method="POST" novalidate>
            @csrf

            <div class="login__group">
                <div class="login__group-row">
                    <label class="login__label" for="email">メールアドレス</label>
                    <input class="login__input" type="email" name="email" id="email" value="{{ old('email') }}">
                </div>
                @error('email')
                    <p class="login__error-msg">{{ $message }}</p>
                @enderror
            </div>
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
                <button type="submit" class="login__button">管理者ログインする</button>
            </div>
        </form>
    </div>
</div>
@endsection
