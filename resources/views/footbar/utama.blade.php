<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pakan')</title>

    <!-- GLOBAL CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel-profile.css') }}">

    <!-- PAGE CSS -->
    @stack('styles')
</head>

<body>

<!-- HEADER -->
<div class="header-top">
    <div class="logo-area">
        <img src="{{ asset('images/logo-udang.png') }}" class="logo-img">
        <span class="logo-text">Tambak Mandhala</span>
    </div>

    <!-- AVATAR -->
    <div class="profile-trigger" onclick="openProfile()">
        <img src="https://i.pravatar.cc/40">
    </div>
</div>

<!-- PROFILE PANEL -->
@include('components.profile-panel')

<!-- CONTENT -->
<main class="fade-in">
    @yield('content')
</main>

<!-- FOOTBAR MODERN -->
<div class="footbar">

 

    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
        <span>🏠</span>
        <small>Home</small>
    </a>

    <a href="{{ url('/monitoring') }}" class="{{ request()->is('monitoring') ? 'active' : '' }}">
        <span>📊</span>
        <small>Monitoring</small>
    </a>

    <a href="{{ url('/history') }}" class="{{ request()->is('history') ? 'active' : '' }}">
        <span>📜</span>
        <small>History</small>
    </a>

    <a href="{{ url('/pengaturan') }}" class="{{ request()->is('pengaturan') ? 'active' : '' }}">
        <span>⚙️</span>
        <small>Setting</small>
    </a>

    <a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}">
        <span>👤</span>
        <small>Profile</small>
    </a>
       <!-- 🔥 PINDAH KE SINI -->
    <div class="indicator"></div>

</div>

<!-- GLOBAL JS -->
<script src="{{ asset('js/panel-profile.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>

<!-- PAGE JS -->
@stack('scripts')

</body>
</html>