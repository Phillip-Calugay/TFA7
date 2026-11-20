@extends('layouts.app')

@section('content')
    <h2>{{ $title }}</h2>
    <p>Enter the customer's contact information. Fields marked * are required.</p>
    <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" novalidate>
        @csrf
        @if($customer->exists) @method('PUT') @endif
        @include('partials.form-errors')
        <label for="full_name">Full name *</label>
        <input id="full_name" name="full_name" value="{{ old('full_name', $customer->full_name) }}" required maxlength="255" autocomplete="name" @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>
        @error('full_name') <p class="field-error" id="full_name-error">{{ $message }}</p> @enderror
        <label for="email">Email *</label>
        <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required maxlength="255" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" maxlength="30" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
        @error('phone') <p class="field-error" id="phone-error">{{ $message }}</p> @enderror
        <div class="actions"><button type="submit">{{ $customer->exists ? 'Save changes' : 'Create customer' }}</button><a href="{{ route('customers.index') }}">Cancel</a></div>
    </form>
@endsection
