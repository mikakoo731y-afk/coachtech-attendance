<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff-style.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <h1 class="header__logo">
                {{-- 認証済みなら勤怠、それ以外はトップへ --}}
                @if(Auth::check() && Auth::user()->hasVerifiedEmail())
                    <a href="{{ route('attendance.index') }}">
                @else
                    <a href="{{ route('login') }}">
                @endif
                    <img src="{{ asset('storage/logo.png') }}" alt="COACHTECH" class="header__logo-img">
                </a>
            </h1>
            <nav class="header__nav">
                <ul class="header__nav-list">
                    {{-- メール認証まで完了している時だけ表示する --}}
                    @if(Auth::check() && Auth::user()->hasVerifiedEmail())
                        <li class="header__nav-item">
                            <a href="{{ route('attendance.index') }}" class="header__nav-link">勤怠</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('correction.list') }}" class="header__nav-link">申請</a>
                        </li>
                        <li class="header__nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="header__form">
                                @csrf
                                <button type="submit" class="header__nav-button">ログアウト</button>
                            </form>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="main__inner">
            @yield('content')
        </div>
    </main>
    @yield('script')
</body>
</html>