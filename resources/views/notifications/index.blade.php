@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
@include('partials.dashboard-nav')

<h1>Notifikasi</h1>

<form method="POST" action="{{ route('notifications.read-all') }}">
    @csrf
    <p><button type="submit">Tandai Semua Dibaca</button></p>
</form>

@if ($notifications->isEmpty())
    <p>Belum ada notifikasi.</p>
@else
    <ul>
        @foreach ($notifications as $notification)
            @php($data = $notification->data)
            <li>
                <article>
                    <h2>{{ $data['title'] ?? 'Notifikasi' }}</h2>
                    <p>{{ $data['message'] ?? '' }}</p>
                    @if (! empty($data['url']))
                        <p><a href="{{ url($data['url']) }}">Buka tautan terkait</a></p>
                    @endif
                    <p><small>{{ $notification->created_at->format('d/m/Y H:i') }} - {{ $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}</small></p>
                </article>
            </li>
        @endforeach
    </ul>

    {{ $notifications->links() }}
@endif
@endsection
