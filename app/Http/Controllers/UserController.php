<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AvatarStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['title' => 'Users', 'users' => User::orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('users.form', ['title' => 'Create User', 'user' => new User(['role' => 'Staff'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return to_route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['title' => 'Edit User', 'user' => $user]);
    }

    public function update(Request $request, User $user, AvatarStorage $avatars): RedirectResponse
    {
        $data = $this->validated($request, $user);
        unset($data['avatar']);
        $oldAvatar = $user->avatar;
        $newAvatar = $request->hasFile('avatar') ? $avatars->store($request->file('avatar')) : null;
        if ($newAvatar) {
            $data['avatar'] = $newAvatar;
        }
        try {
            $user->update($data);
        } catch (\Throwable $exception) {
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }
            throw $exception;
        }
        if ($newAvatar && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return to_route('users.index')->with('status', 'User updated successfully.');
    }

    public function show(User $user): View
    {
        return view('users.show', ['title' => 'User details', 'user' => $user]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Use your profile page to delete your own account.']);
        }
        $avatar = $user->avatar;
        $user->delete();
        if ($avatar) {
            Storage::disk('public')->delete($avatar);
        }

        return to_route('users.index')->with('status', 'User deleted successfully.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $rules = [
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user)],
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['Administrator', 'Manager', 'Cashier', 'Inventory Staff', 'Staff'])],
        ];
        if ($user) {
            $rules['avatar'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'];
        }

        return $request->validate($rules, [
            'avatar.max' => 'The profile picture must not be larger than 2 MB.',
            'avatar.mimes' => 'Choose a JPEG, PNG, or WebP image.',
        ]);
    }
}
