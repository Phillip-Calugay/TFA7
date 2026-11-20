@extends('layouts.app')
@section('content')
<h2>My profile</h2>
@include('partials.form-errors')
@foreach (['default', 'updatePassword', 'userDeletion'] as $bag)
    @foreach ($errors->getBag($bag)->all() as $message)
        <p class="field-error" role="alert">{{ $message }}</p>
    @endforeach
@endforeach
<form method="POST" action="{{ route('profile.update') }}">
@csrf @method('PATCH')
<label for="full_name">Full name</label><input id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
<label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
<div class="actions"><button>Save profile</button></div>
</form>
<h3>Update password</h3>
<form method="POST" action="{{ route('password.update') }}">
@csrf @method('PUT')
<label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password">
<label for="password">New password</label><input id="password" name="password" type="password" required autocomplete="new-password">
<label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
<div class="actions"><button>Update password</button></div>
</form>
<h3>Delete my account</h3>
<form method="POST" action="{{ route('profile.destroy') }}">
@csrf @method('DELETE')
<p>Deleting your account is permanent. Enter your password to confirm.</p>
<label for="delete_password">Password</label><input id="delete_password" name="password" type="password" required autocomplete="current-password">
<div class="actions"><button class="danger">Delete account</button></div>
</form>
@endsection
