<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka rupiah tanpa styling apa pun.
     */
    function rupiah(int|float|null $amount): string
    {
        return 'Rp'.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('tanggal_indonesia')) {
    function tanggal_indonesia(\Illuminate\Support\Carbon|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
    }
}
