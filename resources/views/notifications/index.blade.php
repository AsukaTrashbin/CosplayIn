@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="ci-container py-5">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <div class="hero-eyebrow mb-2">Pusat informasi</div>
            <h1 class="catalog-title mb-2">Notifikasi</h1>
            <p class="text-muted-ci mb-0">Pembaruan pesanan, pembayaran, dan tenant kamu.</p>
        </div>

        @if ($notifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-primary rounded-pill px-4">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="catalog-empty-state p-5 text-center">
            <p class="mb-0 text-muted-ci">Belum ada notifikasi.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach ($notifications as $notification)
                @php($data = $notification->data)
                @php($isUnread = is_null($notification->read_at))

                <article class="tenant-card {{ $isUnread ? 'border-0' : '' }}"
                         style="{{ $isUnread ? 'background-color: var(--ci-pink-soft);' : '' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if ($isUnread)
                                    <span class="badge-available">Baru</span>
                                @endif
                                <h2 class="tenant-card-title mb-0 fs-5">{{ $data['title'] ?? 'Notifikasi' }}</h2>
                            </div>

                            <p class="text-muted-ci mb-2">{{ $data['message'] ?? '' }}</p>

                            @if (! empty($data['url']))
                                <a href="{{ url($data['url']) }}" class="tenant-card-link">Buka tautan terkait &rarr;</a>
                            @endif
                        </div>

                        <div class="tenant-card-meta text-nowrap">
                            {{ $notification->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection