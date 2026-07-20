<?php

namespace App\Http\Controllers;

use App\Support\AdxonContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(AdxonContent $content): View
    {
        return view('site', [
            'content' => $content->all(),
            'lines' => fn (?string $value) => $content->lines($value),
        ]);
    }

    public function storeEnquiry(Request $request, AdxonContent $content): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'budget' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $content->appendEnquiry([
            ...$validated,
            'budget' => $validated['budget'] ?? '',
            'created_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('home', ['sent' => 1])->withFragment('contact');
    }
}
