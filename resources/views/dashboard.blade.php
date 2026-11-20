@extends('layouts.app')
@section('content')
<h2>Dashboard</h2>
<p>Welcome, {{ auth()->user()->full_name }}. You are signed in.</p>
<p>Manage customer records and staff accounts using the links below.</p>
<div class="actions"><a class="button" href="{{ route('customers.index') }}">Customer accounts</a><a class="button" href="{{ route('users.index') }}">User accounts</a></div>
@endsection
