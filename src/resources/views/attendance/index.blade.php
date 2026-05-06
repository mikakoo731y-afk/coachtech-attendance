@extends('layouts.app')

@section('content')
<div class="attendance">
    {{-- ステータス表示 --}}
    <div class="attendance__status">
        <h2 class="attendance__status-label">{{ $status }}</h2>
    </div>

    {{-- 現在の日時情報 --}}
    <div class="attendance__info">
        <p class="attendance__date">
            {{ now()->format('Y年n月j日') }}({{ ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] }})
        </p>
        <div id="realtime-clock" class="attendance__time">00:00</div>
    </div>

    {{-- 打刻アクション --}}
    <div class="attendance__actions">
        @if($status === '勤務外')
            <form action="{{ route('attendance.check-in') }}" method="POST" class="attendance__form">
                @csrf
                <button type="submit" class="attendance__button">出勤</button>
            </form>
        @elseif($status === '出勤中')
            <div class="attendance__button-row">
                <form action="{{ route('attendance.check-out') }}" method="POST" class="attendance__form">
                    @csrf
                    <button type="submit" class="attendance__button">退勤</button>
                </form>
                <form action="{{ route('attendance.rest-start') }}" method="POST" class="attendance__form">
                    @csrf
                    <button type="submit" class="attendance__button">休憩入</button>
                </form>
            </div>
        @elseif($status === '休憩中')
            <form action="{{ route('attendance.rest-end') }}" method="POST" class="attendance__form">
                @csrf
                <button type="submit" class="attendance__button">休憩戻</button>
            </form>
        @elseif($status === '退勤済')
            <div class="attendance__finish">
                <p class="attendance__finish-text">お疲れ様でした。</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const clockElement = document.getElementById('realtime-clock');
        if (clockElement) {
            clockElement.textContent = `${hours}:${minutes}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
