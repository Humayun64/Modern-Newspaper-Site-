<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected const ROLES = ['admin', 'editor', 'author'];

    public function save(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $user = $request->filled('id') ? User::findOrFail($request->input('id')) : null;

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'name_bn'     => ['nullable', 'string', 'max:120'],
            'email'       => ['required', 'email', 'max:160', Rule::unique('users')->ignore($user?->id)],
            'role'        => ['required', Rule::in(self::ROLES)],
            'designation' => ['nullable', 'string', 'max:120'],
            'bio'         => ['nullable', 'string', 'max:1000'],
            'photo'       => ['nullable', 'image', 'max:2048'],
            'facebook'    => ['nullable', 'string', 'max:255'],
            'twitter'     => ['nullable', 'string', 'max:255'],
            'password'    => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:100'],
        ]);

        // An admin must not lock everyone out by demoting the last admin.
        if ($user && $user->isAdmin() && $data['role'] !== 'admin' && $this->adminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'This is the only admin account. Promote someone else first.',
            ]);
        }

        $payload = collect($data)->except(['password', 'photo'])->all();
        $payload['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            $payload['photo'] = $request->file('photo')->store('authors', 'public');
        }

        if ($request->filled('password')) {
            $payload['password'] = $data['password'];   // hashed by the model cast
        }

        if ($user) {
            $user->update($payload);
            ActivityLog::record('user_updated', [
                'subject_type' => 'user', 'subject_id' => $user->id, 'description' => $user->name,
            ]);
        } else {
            $user = User::create($payload);
            ActivityLog::record('user_created', [
                'subject_type' => 'user', 'subject_id' => $user->id, 'description' => $user->name,
            ]);
        }

        return redirect()
            ->to(\App\Filament\Admin\Pages\ManageUsers::getUrl())
            ->with('user_saved', true);
    }

    public function toggle(Request $request, User $user)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot disable your own account.']);
        }

        if ($user->isAdmin() && $user->is_active && $this->adminCount() <= 1) {
            return back()->withErrors(['user' => 'This is the only active admin. Promote someone else first.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            ActivityLog::record('user_disabled', [
                'subject_type' => 'user', 'subject_id' => $user->id, 'description' => $user->name,
            ]);
        }

        return back()->with('user_saved', true);
    }

    protected function adminCount(): int
    {
        return User::where('role', 'admin')->where('is_active', true)->count();
    }
}
