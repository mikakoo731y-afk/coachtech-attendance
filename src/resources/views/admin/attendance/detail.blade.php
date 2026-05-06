@extends('layouts.admin')

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__heading">勤怠詳細</h2>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" class="attendance-detail__form">
        @csrf
        <div class="attendance-detail__card">
            {{-- 名前 --}}
            <div class="attendance-detail__group">
                <label class="attendance-detail__label">名前</label>
                <div class="attendance-detail__input-area">
                    <span class="attendance-detail__text">{{ $attendance->user->name }}</span>
                </div>
            </div>

            {{-- 日付 --}}
            <div class="attendance-detail__group">
                <label class="attendance-detail__label">日付</label>
                <div class="attendance-detail__input-area">
                    <span class="attendance-detail__text--bold">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}
                    </span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="attendance-detail__group">
                <label class="attendance-detail__label">出勤・退勤</label>
                <div class="attendance-detail__input-area">
                    <input type="text" name="clock_in" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}" {{ $isRequested ? 'readonly' : '' }} class="attendance-detail__input">
                    <span class="attendance-detail__separator">～</span>
                    <input type="text" name="clock_out" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}" {{ $isRequested ? 'readonly' : '' }} class="attendance-detail__input">
                </div>
                @error('clock_in')
                    <p class="attendance-detail__error" style="color: red;">{{ $message }}</p>
                @enderror
            </div>

            {{-- 休憩のループ（常に2つ枠を出す） --}}
            @for ($i = 0; $i < 2; $i++)
            @php $rest = $attendance->rests->values()->get($i); @endphp
            <div class="attendance-detail__group">
                <label class="attendance-detail__label">休憩{{ $i + 1 }}</label>
                <div class="attendance-detail__input-area">
                    <input type="text" name="rests[{{ $i }}][start]"
                            value="{{ old("rests.$i.start", $rest ? substr($rest->start_time, 0, 5) : '') }}"
                            {{ $isRequested ? 'readonly' : '' }} class="attendance-detail__input">
                    <span class="attendance-detail__separator">～</span>
                    <input type="text" name="rests[{{ $i }}][end]"
                            value="{{ old("rests.$i.end", $rest ? substr($rest->end_time, 0, 5) : '') }}"
                            {{ $isRequested ? 'readonly' : '' }} class="attendance-detail__input">
                </div>
                @error("rests.$i.start")
                    <p style="color: red;">{{ $message }}</p>
                @enderror
                @error("rests.$i.end")
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            @endfor

            {{-- 備考 --}}
            <div class="attendance-detail__group">
                <label class="attendance-detail__label">備考</label>
                <div class="attendance-detail__input-area">
                    <textarea name="reason" rows="2" {{ $isRequested ? 'readonly' : '' }} class="attendance-detail__textarea">{{ old('reason', $attendance->remarks) }}</textarea>
                    @error('reason')
                        <p style="color: red;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="attendance-detail__actions">
            @if($isRequested)
                <p class="warning-msg">＊承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="attendance-detail__button">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
