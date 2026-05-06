@extends('layouts.app')

@section('content')
<div class="correction-list">
    {{-- 見出しの統一 --}}
    <h2 class="correction-list__heading">申請一覧</h2>
    {{-- タブナビゲーションの統一 --}}
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
    {{-- テーブル部分（カード構造を適用） --}}
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
                @forelse($requests as $req)
                <tr class="correction-list__row">
                    <td class="correction-list__data">{{ $req->status === 1 ? '承認待ち' : '承認済み' }}</td>
                    <td class="correction-list__data">{{ $req->user->name }}</td>
                    <td class="correction-list__data">
                        {{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}
                    </td>
                    <td class="correction-list__data">{{ Str::limit($req->reason, 20) }}</td>
                    <td class="correction-list__data">{{ $req->created_at->format('Y/m/d') }}</td>
                    <td class="correction-list__data">
                        {{-- ユーザー側は勤怠詳細(attendance.show)へ遷移 --}}
                        <a href="{{ route('attendance.show', $req->attendance_id) }}" class="correction-list__detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr class="correction-list__row">
                    <td colspan="6" class="correction-list__empty">該当する申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
