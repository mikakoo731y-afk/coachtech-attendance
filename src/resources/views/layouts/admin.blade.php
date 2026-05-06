<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>管理者パネル | COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <h1 class="header__logo">
                @auth
                    {{-- ログインしている時は管理者の勤怠一覧へ --}}
                    <a href="{{ route('admin.attendance.list') }}">
                @else
                    {{-- ログインしていない時は管理者ログイン画面へ --}}
                    <a href="{{ route('admin.login.view') }}">
                @endauth
                    <img src="{{ asset('storage/logo.png') }}" alt="COACHTECH" class="header__logo-img">
                </a>
            </h1>
            @auth
            <nav class="header__nav">
                <ul class="header__nav-list">
                    <li class="header__nav-item">
                        <a href="{{ route('admin.attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('correction.list') }}" class="header__nav-link">申請一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="header__form">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
            @endauth
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
