@extends('layouts.app')

@section('bare', true)
@section('body-class', 'dashboard-page')

@section('content')
@php
    $sidebarItems = [
        ['label' => 'Ringkasan', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Pesanan aktif', 'route' => 'customer.orders.active', 'active' => request()->routeIs('customer.orders.active')],
        ['label' => 'Status Pembayaran', 'route' => 'customer.payments.index', 'active' => request()->routeIs('customer.payments.index')],
        ['label' => 'Riwayat Sewa', 'route' => 'customer.rentals.history', 'active' => request()->routeIs('customer.rentals.history')],
    ];
@endphp

<div class="dashboard-shell">
    <aside class="dashboard-sidebar">
        <nav class="dashboard-sidebar-nav" aria-label="Menu dashboard pengguna">
            <p class="dashboard-sidebar-heading mb-0">Dashboard</p>

            @foreach ($sidebarItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="dashboard-sidebar-link {{ $item['active'] ? 'active' : '' }}"
                   @if ($item['active']) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="dashboard-sidebar-footer">
            <hr class="dashboard-sidebar-divider">
            <p class="dashboard-sidebar-account mb-0">Akun pengguna</p>
        </div>
    </aside>

    <div class="dashboard-main">
        <nav class="dashboard-tabs d-lg-none" aria-label="Menu dashboard pengguna">
            @foreach ($sidebarItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="dashboard-tab {{ $item['active'] ? 'active' : '' }}"
                   @if ($item['active']) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <header class="dashboard-header">
            <div>
                <p class="dashboard-eyebrow mb-2">DASHBOARD PENGGUNA</p>
                <h1 class="dashboard-title mb-2">@yield('page-title')</h1>
                <p class="dashboard-subtitle mb-0">@yield('page-subtitle')</p>
            </div>

            <a href="{{ route('catalog.index') }}" class="btn btn-primary dashboard-cta">
                Jelajahi kostum &rarr;
            </a>
        </header>

        @yield('dashboard')
    </div>
</div>
@endsection
