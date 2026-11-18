<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = [
            'late_fee_per_day' => Setting::get('late_fee_per_day', config('cosplayin.late_fee.per_day')),
            'max_late_fee' => Setting::get('max_late_fee', config('cosplayin.late_fee.max')),
            'default_deposit' => Setting::get('default_deposit', config('cosplayin.deposit')),
            'shipping_fee' => Setting::get('shipping_fee', config('cosplayin.shipping_fee')),
            'min_rental_days' => Setting::get('min_rental_days', config('cosplayin.rental.min_days')),
            'max_rental_days' => Setting::get('max_rental_days', config('cosplayin.rental.max_days')),
            'platform_commission_percent' => Setting::get('platform_commission_percent', config('cosplayin.commission_percent')),
        ];

        return view('admin.pengaturan.edit', compact('settings'));
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::put($key, $value);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
