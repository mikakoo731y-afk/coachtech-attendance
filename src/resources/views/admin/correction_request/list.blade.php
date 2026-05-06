@extends('layouts.admin')

@section('content')
<div class="correction-list">
    <h2 class="correction-list__heading">管理申請一覧</h2>

    {{-- タブナビゲーション --}}
    <nav class="correction-list__nav">
        <ul class="correction-list__tab">
            <li class="correction-list__tab-item {{ $tab === 'pending' ? 'is-active' : '' }}">
                <a href="{{ route('correction.list', ['tab' => 'pending']) }}" class="correction-list__tab-link">承認待ち</a>
            </li>
            <li class="correction-list__tab-item {{ $tab === 'approved' ? 'is-active' : '' }}">
                <a href="{{ route('correction.list', ['tab' => 'approved']) }}" class="correction-list__tab-link">承認済み</a>
            </li>
        </ul>
    </nav>

    <div class="correction-list__card">
        <table class="correction-list__table">
            <thead class="correction-list__thead">
                <tr>
                    <th class="correction-list__label">状態</th>
                    <th class="correction-list__label">名前</th>
                    <th class="correction-list__label">対象日時</th>
                    <th class="correction-list__label">申請理由</th>
                    <th class="correction-list__label">申請日時</th>
                    <th class="correction-list__label">詳細</th>
                </tr>
            </thead>
            <tbody class="correction-list__tbody">
                @forelse($requests as $request)
                <tr class="correction-list__row">
                    <td class="correction-list__data">
                        @if($request->status === 1) 承認待ち
                        @elseif($request->status === 2) 承認済み
                        @endif
                    </td>
                    <td class="correction-list__data">{{ $request->user->name }}</td>
                    <td class="correction-list__data">
                        {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                    </td>
                    <td class="correction-list__data">{{ Str::limit($request->reason, 20) }}</td>
                    <td class="correction-list__data">{{ $request->created_at->format('Y/m/d') }}</td>
                    <td class="correction-list__data">
                        {{-- パラメータ名をweb.phpの定義に合わせました --}}
                        <a href="{{ route('admin.approve.view', ['attendance_correct_request_id' => $request->id]) }}" class="correction-list__detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr class="correction-list__row">
                    <td colspan="6" class="correction-list__empty">申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
