@extends('layouts.app')
@section('content')
<h2>{{ $title }}</h2>
@include('partials.form-errors')
<dl><dt>Full Name</dt><dd>{{ $customer->full_name }}</dd>
<dt>Email</dt><dd>{{ $customer->email }}</dd>
<dt>Phone</dt><dd>{{ $customer->phone }}</dd></dl>
<div class="actions"><a href="{{ route('customers.edit', $customer) }}">Edit</a><a href="{{ route('customers.index') }}">Back to customers</a></div>
<form method="POST" action="{{ route('customers.destroy', $customer) }}">
@csrf @method('DELETE')
<p>Deleting this record is permanent.</p><button class="danger" type="submit">Delete customer</button>
</form>
@endsection
