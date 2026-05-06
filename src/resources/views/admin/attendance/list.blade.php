@extends('layouts.admin')

@section('content')
<div class="attendance">
    <h2 class="attendance__heading">{{ $currentDate->format('Y年m月d日') }}の勤怠一覧</h2>

    <nav class="attendance__nav">
        {{-- 前日リンク --}}
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="attendance__nav-link">
            <span class="attendance__nav-arrow">&lt;</span> 前日
        </a>

        {{-- 日付選択（カレンダー） --}}
        <div class="attendance__date-picker">
            <form action="{{ route('admin.attendance.list') }}" method="GET" id="date-form">
                <input type="date" name="date" value="{{ $currentDate->format('Y-m-d') }}" onchange="this.form.submit()" class="attendance__date-input">
                <div class="attendance__date-display" onclick="document.querySelector('.attendance__date-input').showPicker()">
                    <svg class="attendance__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="attendance__date-text">{{ $currentDate->format('Y/m/d') }}</span>
                </div>
            </form>
        </div>

        {{-- 翌日リンク --}}
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="attendance__nav-link">
            翌日 <span class="attendance__nav-arrow">&gt;</span>
        </a>
    </nav>

    <div class="attendance__card">
        <table class="attendance__table">
            <thead class="attendance__thead">
                <tr>
                    <th class="attendance__label">名前</th>
                    <th class="attendance__label">出勤</th>
                    <th class="attendance__label">退勤</th>
                    <th class="attendance__label">休憩</th>
                    <th class="attendance__label">合計</th>
                    <th class="attendance__label">詳細</th>
                </tr>
            </thead>
            <tbody class="attendance__tbody">
                @forelse($attendances as $attendance)
                <tr class="attendance__row">
                    <td class="attendance__data">{{ $attendance->user->name }}</td>
                    <td class="attendance__data">{{ $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '' }}</td>
                    <td class="attendance__data">{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                    <td class="attendance__data">{{ $attendance->total_rest_time ?? '0:00' }}</td>
                    <td class="attendance__data">{{ $attendance->total_work_time ?? '0:00' }}</td>
                    <td class="attendance__data">
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="attendance__detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr class="attendance__row">
                    <td colspan="6" class="attendance__empty">本日の勤怠データはありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
