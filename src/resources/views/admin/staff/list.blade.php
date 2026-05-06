@extends('layouts.admin')

@section('content')
<div class="staff-list">
    {{-- ページタイトルを他と統一 --}}
    <h2 class="staff-list__heading">スタッフ一覧</h2>

    <div class="staff-list__card">
        <table class="staff-list__table">
            <thead class="staff-list__thead">
                <tr>
                    <th class="staff-list__label">名前</th>
                    <th class="staff-list__label">メールアドレス</th>
                    <th class="staff-list__label">詳細</th>
                </tr>
            </thead>
            <tbody class="staff-list__tbody">
                @foreach($users as $user)
                <tr class="staff-list__row">
                    <td class="staff-list__data">{{ $user->name }}</td>
                    <td class="staff-list__data">{{ $user->email }}</td>
                    <td class="staff-list__data">
                        <a href="{{ route('admin.staff.attendance', $user->id) }}" class="staff-list__detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
