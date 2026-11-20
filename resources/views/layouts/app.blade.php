<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Simple POS' }} | Simple POS</title>
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
</head>
<body>
    <header>
        <h1>Simple POS System</h1>
        <nav aria-label="Main navigation">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
            <a href="{{ route('customers.index') }}" @if(request()->routeIs('customers*')) aria-current="page" @endif>Customers</a>
            <a href="{{ route('users.index') }}" @if(request()->routeIs('users*')) aria-current="page" @endif>Users</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('profile.edit') }}">My profile</a>
                <span>Signed in as {{ auth()->user()->full_name }}</span>
                <form class="logout" method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>
    <main>
        @if(session('status')) <div class="status" role="status">{{ session('status') }}</div> @endif
        @yield('content')
    </main>
</body>
</html>
