@extends('layouts.admin')

@section('content')
<div class="approval-detail">
    <h2 class="approval-detail__title">勤怠詳細</h2>
    @if (session('success'))
        <div class="approval-detail__success-msg" style="color: green; margin-bottom: 20px; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    <form class="approval-detail__form" action="{{ route('admin.approve.exec', ['attendance_correct_request_id' => $request->id]) }}" method="POST">
        @csrf
        <div class="approval-detail__card">
            <div class="approval-detail__row">
                <p class="approval-detail__label">名前</p>
                <p class="approval-detail__value">{{ $request->user->name }}</p>
            </div>
            <div class="approval-detail__row">
                <p class="approval-detail__label">日付</p>
                <p class="approval-detail__value">{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年n月j日') }}</p>
            </div>
            <div class="approval-detail__row">
                <p class="approval-detail__label">出勤・退勤</p>
                <div class="approval-detail__value">
                    <span>{{ substr($request->proposed_clock_in, 0, 5) }}</span> ～ <span>{{ substr($request->proposed_clock_out, 0, 5) }}</span>
                </div>
            </div>

            {{-- 休憩表示ループ --}}
            @for ($i = 0; $i < 2; $i++)
            @php $rest = $request->restCorrectRequests->values()->get($i); @endphp
            <div class="approval-detail__row">
                <p class="approval-detail__label">休憩{{ $i + 1 }}</p>
                <div class="approval-detail__value">
                    <span>{{ $rest ? substr($rest->proposed_start_time, 0, 5) : '' }}</span> ～ <span>{{ $rest ? substr($rest->proposed_end_time, 0, 5) : '' }}</span>
                </div>
            </div>
            @endfor

            <div class="approval-detail__row approval-detail__row--no-border">
                <p class="approval-detail__label">備考</p>
                <p class="approval-detail__value">{{ $request->reason }}</p>
            </div>
        </div>
        <div class="approval-detail__actions">
            @if($request->status === 2)
                <p class="approval-detail__approved-text">承認済み</p>
            @else
                <button type="submit" class="approval-detail__button">承認</button>
            @endif
        </div>
    </form>
</div>
@endsection
