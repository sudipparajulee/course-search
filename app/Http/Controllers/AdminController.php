<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function requireAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Admin access required.');
        }
    }

    public function dashboard()
    {
        $this->requireAdmin();
        return view('dashboard');
    }

    public function users()
    {
        $this->requireAdmin();

        $users = User::query()
            ->where('role', '!=', 'admin')
            ->where('id', '!=', auth()->id())
            ->latest()
            ->get();

        return view('admin.user.index', compact('users'));
    }

    public function editUser(User $user)
    {
        $this->requireAdmin();
        $this->authorizeUserAction($user);

        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->requireAdmin();
        $this->authorizeUserAction($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    private function authorizeUserAction(User $user)
    {
        if ($user->role === 'admin' || $user->id === auth()->id()) {
            abort(404);
        }
    }

    public function contact()
    {
        $this->requireAdmin();

        $contacts = Contact::latest()->get();

        return view('admin.contact.index', compact('contacts'));
    }

    public function destroyUser(User $user)
    {
        $this->requireAdmin();
        $this->authorizeUserAction($user);

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function destroy(Contact $contact)
    {
        $this->requireAdmin();

        $contact->delete();

        return redirect()->route('admin.contact')->with('success', 'The enquiry was deleted successfully.');
    }
}
