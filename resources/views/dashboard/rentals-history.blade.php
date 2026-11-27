@extends('layouts.dashboard')

@section('title', 'Riwayat Sewa')

@section('page-title', 'Riwayat penyewaan')
@section('page-subtitle', 'Semua rencana cosplay-mu, dalam satu tempat.')

@section('dashboard')
<div class="mt-4">
    @include('dashboard.partials.history-card', [
        'history' => $history,
        'showLink' => false,
        'tall' => true,
    ])
</div>

@if ($history->hasPages())
    <div class="mt-4">
        {{ $history->links('pagination.catalog') }}
    </div>
@endif
@endsection
