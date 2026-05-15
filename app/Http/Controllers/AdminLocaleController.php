<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class AdminLocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['en', 'uz'], true), 404);

        session(['admin_locale' => $locale]);

        return back();
    }
}
