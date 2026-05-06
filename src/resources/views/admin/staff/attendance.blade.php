@extends('layouts.admin')

@section('content')
<div class="staff-attendance">
    {{-- ページタイトル --}}
    <h2 class="staff-attendance__heading">{{ $staff->name }}さんの勤怠一覧</h2>

    {{-- 月移動ナビゲーション --}}
    <nav class="staff-attendance__nav">
        <a href="{{ route('admin.staff.attendance', ['id' => $staff->id, 'month' => $prevMonth]) }}" class="staff-attendance__nav-link">
            <span class="staff-attendance__nav-arrow">&lt;</span> 前月
        </a>

        <div class="staff-attendance__month-picker">
            <form action="{{ route('admin.staff.attendance', $staff->id) }}" method="GET">
                <input type="month" id="month_input" name="month" value="{{ $currentMonth->format('Y-m') }}" onchange="this.form.submit()" class="staff-attendance__month-input" style="display:none;">
                <div class="staff-attendance__month-display" onclick="document.getElementById('month_input').showPicker()">
                    <svg class="staff-attendance__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="staff-attendance__month-text">{{ $currentMonth->format('Y/m') }}</span>
                </div>
            </form>
        </div>

        <a href="{{ route('admin.staff.attendance', ['id' => $staff->id, 'month' => $nextMonth]) }}" class="staff-attendance__nav-link">
            次月 <span class="staff-attendance__nav-arrow">&gt;</span>
        </a>
    </nav>

    {{-- 勤怠一覧カード --}}
    <div class="staff-attendance__card">
        <table class="staff-attendance__table">
            <thead class="staff-attendance__thead">
                <tr>
                    <th class="staff-attendance__label">日付</th>
                    <th class="staff-attendance__label">出勤</th>
                    <th class="staff-attendance__label">退勤</th>
                    <th class="staff-attendance__label">休憩</th>
                    <th class="staff-attendance__label">合計</th>
                    <th class="staff-attendance__label">詳細</th>
                </tr>
            </thead>
            <tbody class="staff-attendance__tbody">
                @foreach($attendances as $attendance)
                <tr class="staff-attendance__row">
                    <td class="staff-attendance__data">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}({{ ['日','月','火','水','木','金','土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }})
                    </td>
                    <td class="staff-attendance__data">{{ $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '' }}</td>
                    <td class="staff-attendance__data">{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                    <td class="staff-attendance__data">{{ $attendance->total_rest_time ?? '0:00' }}</td>
                    <td class="staff-attendance__data">{{ $attendance->total_work_time ?? '0:00' }}</td>
                    <td class="staff-attendance__data">
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="staff-attendance__detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- CSV出力アクション --}}
    <div class="staff-attendance__actions">
        <a href="{{ route('admin.attendance.export', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]) }}" class="staff-attendance__csv-link">CSV出力</a>
    </div>
</div>
@endsection
