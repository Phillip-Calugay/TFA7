@extends('layouts.app')

@section('content')
    <h2>User Accounts</h2>
    <p>List of system users and staff.</p>
    <p><a class="button" href="{{ route('users.create') }}">Create user</a></p>
    <div class="table-wrap">
        <table aria-label="User accounts">
            <thead><tr><th scope="col">Avatar</th><th scope="col">Username</th><th scope="col">Full Name</th><th scope="col">Role</th><th scope="col">Actions</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><img class="avatar" src="{{ $user->avatar ? Storage::disk('public')->url($user->avatar) : asset('images/avatar-placeholder.svg') }}" alt="Profile picture for {{ $user->full_name }}"></td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->full_name }}</td>
                        <td>{{ $user->role }}</td>
                        <td><a href="{{ route('users.show', $user) }}" aria-label="View {{ $user->full_name }}">View</a> · <a href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->full_name }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
