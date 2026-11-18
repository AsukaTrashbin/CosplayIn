<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function terms(): View
    {
        return view('pages.syarat-ketentuan');
    }

    public function privacy(): View
    {
        return view('pages.kebijakan-privasi');
    }
}
