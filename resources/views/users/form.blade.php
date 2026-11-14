@extends('layouts.app')

@section('content')
    <h2>{{ $title }}</h2>
    <p>Manage a staff account. Fields marked * are required.</p>
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if($user->exists) @method('PUT') @endif
        @include('partials.form-errors')
        <label for="username">Username *</label>
        <input id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="255" autocomplete="username" @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
        @error('username') <p class="field-error" id="username-error">{{ $message }}</p> @enderror
        <label for="full_name">Full name *</label>
        <input id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required maxlength="255" autocomplete="name" @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>
        @error('full_name') <p class="field-error" id="full_name-error">{{ $message }}</p> @enderror
        <label for="role">Role *</label>
        <select id="role" name="role" required @error('role') aria-invalid="true" aria-describedby="role-error" @enderror>
            @foreach(['Administrator', 'Manager', 'Cashier', 'Inventory Staff', 'Staff'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role') <p class="field-error" id="role-error">{{ $message }}</p> @enderror
        @if($user->exists)
            <label for="avatar">Profile picture</label>
            <img class="avatar preview" src="{{ $user->avatar ? Storage::disk('public')->url($user->avatar) : asset('images/avatar-placeholder.svg') }}" alt="Current profile picture for {{ $user->full_name }}">
            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="avatar-help{{ $errors->has('avatar') ? ' avatar-error' : '' }}" @error('avatar') aria-invalid="true" @enderror>
            <p class="help" id="avatar-help">JPEG, PNG or WebP, up to 2 MB and 6000 × 6000 pixels. Saved as a 256 × 256 thumbnail. Leave empty to keep the current picture.</p>
            @error('avatar') <p class="field-error" id="avatar-error">{{ $message }} Please select the file again.</p> @enderror
        @endif
        <div class="actions"><button type="submit">{{ $user->exists ? 'Save changes' : 'Create user' }}</button><a href="{{ route('users') }}">Cancel</a></div>
    </form>
@endsection
