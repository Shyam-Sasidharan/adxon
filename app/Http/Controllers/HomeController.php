<?php

namespace App\Http\Controllers;

use App\Support\AdxonContent;
use App\Support\AdxonAdminData;
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

    public function storeEnquiry(Request $request, AdxonContent $content, AdxonAdminData $adminData): RedirectResponse
    {
        abort_unless(AdxonContent::sectionActive($content->all(), 'contact'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'budget' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $content->appendEnquiry([
            ...$validated,
            'budget' => $validated['budget'] ?? '',
            'phone' => $validated['phone'] ?? '',
            'created_at' => now()->toIso8601String(),
        ]);

        $adminData->addLead([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '',
            'source' => 'Website',
            'stage' => 'New Lead',
            'website_unread' => true,
            'budget' => $validated['budget'] ?? '',
            'notes' => $validated['message'],
        ]);

        return redirect()->route('home', ['sent' => 1])->withFragment('contact');
    }

    public function detail(AdxonContent $content, string $kind, int $index): View
    {
        abort_unless(in_array($kind, ['portfolio', 'blogs'], true), 404);
        $data = $content->all();
        abort_unless(AdxonContent::sectionActive($data, $kind === 'portfolio' ? 'work' : 'blog'), 404);
        abort_unless(isset($data[$kind][$index]), 404);

        return view('marketing.detail', ['content' => $data, 'item' => $data[$kind][$index], 'kind' => $kind]);
    }
}
