<?php

namespace App\Http\Controllers;

use App\Support\AdxonContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function loginForm(Request $request): RedirectResponse|View
    {
        if ($this->isLoggedIn($request)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = env('ADXON_ADMIN_USER', 'admin');
        $password = env('ADXON_ADMIN_PASSWORD', 'adxon@123');

        if (hash_equals($user, $credentials['username']) && hash_equals($password, $credentials['password'])) {
            $request->session()->regenerate();
            $request->session()->put('adxon_admin', true);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid login details.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('adxon_admin');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(Request $request, AdxonContent $content): RedirectResponse|View
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        return $this->view('dashboard', $content);
    }

    public function collection(Request $request, AdxonContent $content, string $collection): RedirectResponse|View
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        if (! isset($content->collections()[$collection])) {
            abort(404);
        }

        return $this->view($collection, $content);
    }

    public function saveCollection(Request $request, AdxonContent $content, string $collection): RedirectResponse
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        $content->saveCollection($collection, $request->input('rows', []));

        return redirect()->route('admin.collection', $collection)->with('status', $content->collections()[$collection]['title'].' saved.');
    }

    public function enquiries(Request $request, AdxonContent $content): RedirectResponse|View
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        return $this->view('enquiries', $content);
    }

    public function deleteEnquiry(Request $request, AdxonContent $content, int $index): RedirectResponse
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        $content->deleteEnquiry($index);

        return redirect()->route('admin.enquiries')->with('status', 'Enquiry removed.');
    }

    public function settings(Request $request, AdxonContent $content): RedirectResponse|View
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        return $this->view('settings', $content);
    }

    public function saveSettings(Request $request, AdxonContent $content): RedirectResponse
    {
        if (! $this->isLoggedIn($request)) {
            return redirect()->route('admin.login');
        }

        $content->saveSettings($request->all());

        return redirect()->route('admin.settings')->with('status', 'Settings saved.');
    }

    private function view(string $active, AdxonContent $content): View
    {
        return view('admin.cms', [
            'active' => $active,
            'content' => $content->withAdminAliases(),
            'collections' => $content->collections(),
        ]);
    }

    private function isLoggedIn(Request $request): bool
    {
        return $request->session()->get('adxon_admin') === true;
    }
}
