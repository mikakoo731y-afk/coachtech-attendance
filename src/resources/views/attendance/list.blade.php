@extends('layouts.app')

@section('content')
<div class="attendance-list">
    {{-- 見出し --}}
    <h2 class="attendance-list__heading">勤怠一覧</h2>

    {{-- 月移動ナビゲーション --}}
    <nav class="attendance-list__nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="attendance-list__nav-link">
            <span class="attendance-list__nav-arrow">&lt;</span> 前月
        </a>

        <div class="attendance-list__month-picker">
            <form action="{{ route('attendance.list') }}" method="GET">
                <input type="month" id="month" name="month" value="{{ $currentMonth->format('Y-m') }}" onchange="this.form.submit()" class="attendance-list__month-input" style="display:none;">
                <div class="attendance-list__month-display" onclick="document.getElementById('month').showPicker()">
                    {{-- 管理者側と共通のSVGアイコン --}}
                    <svg class="attendance-list__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="attendance-list__month-text">{{ $currentMonth->format('Y/m') }}</span>
                </div>
            </form>
        </div>

        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="attendance-list__nav-link">
            次月 <span class="attendance-list__nav-arrow">&gt;</span>
        </a>
    </nav>

    {{-- テーブル部分（カード構造を適用） --}}
    <div class="attendance-list__card">
        <table class="attendance-list__table">
            <thead class="attendance-list__thead">
                <tr>
                    <th class="attendance-list__label">日付</th>
                    <th class="attendance-list__label">出勤</th>
                    <th class="attendance-list__label">退勤</th>
                    <th class="attendance-list__label">休憩</th>
                    <th class="attendance-list__label">合計</th>
                    <th class="attendance-list__label">詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-list__tbody">
                @foreach($attendances as $attendance)
                <tr class="attendance-list__row">
                    <td class="attendance-list__data">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}({{ ['日','月','火','水','木','金','土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }})
                    </td>
                    <td class="attendance-list__data">{{ $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '' }}</td>
                    <td class="attendance-list__data">{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</td>
                    <td class="attendance-list__data">{{ $attendance->formatted_rest_time ?? '0:00' }}</td>
                    <td class="attendance-list__data">{{ $attendance->total_work_time ?? '0:00' }}</td>
                    <td class="attendance-list__data">
                        <a href="{{ route('attendance.show', $attendance->id) }}" class="attendance-list__detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
