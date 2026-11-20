@extends('layouts.app')
@section('content')
<h2>{{ $title }}</h2>
@include('partials.form-errors')
<dl><dt>Username</dt><dd>{{ $user->username }}</dd>
<dt>Full Name</dt><dd>{{ $user->full_name }}</dd>
<dt>Role</dt><dd>{{ $user->role }}</dd></dl>
<div class="actions"><a href="{{ route('users.edit', $user) }}">Edit</a><a href="{{ route('users.index') }}">Back to users</a></div>
<form method="POST" action="{{ route('users.destroy', $user) }}">
@csrf @method('DELETE')
<p>Deleting this record is permanent.</p><button class="danger" type="submit">Delete user</button>
</form>
@endsection
