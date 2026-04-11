<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pakan</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <link rel="stylesheet" type="text/css" href="styles/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:...|Roboto:..." rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="fonts/css/fontawesome-all.min.css">
    <link rel="manifest" href="_manifest.json" data-pwa-version="set_in_manifest_and_pwa_js">
    <link rel="manifest" href="_manifest.json">
     
    <style>
.profile-trigger {
    position: fixed;
    top: 15px;
    right: 20px;
    cursor: pointer;
    z-index: 1000;
}

.profile-trigger img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.profile-panel {
    position: fixed;
    top: 0;
    right: -320px;
    width: 300px;
    height: 100%;
    background: white;
    box-shadow: -5px 0 15px rgba(0,0,0,0.2);
    transition: 0.3s;
    z-index: 999;
}

.profile-panel.active {
    right: 0;
}
</style>
</head>
<body>
<main>
        @yield('content')
    </main>
<div class="footbar" id="footbar">     

  <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
     <span>🏠</span>Home
  </a>

  <a href="{{ url('/monitoring') }}" class="{{ request()->is('monitoring') ? 'active' : '' }}">
     <span>📊</span>Monitoring
  </a>

  <a href="{{ url('/history') }}" class="{{ request()->is('history') ? 'active' : '' }}">
     <span>📜</span>History
  </a>

  <a href="{{ url('/pengaturan') }}" class="{{ request()->is('pengaturan') ? 'active' : '' }}">
     <span>⚙️</span>Setting
  </a>

  <a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}">
     <span>👤</span>Profile
  </a>

</div>
<div id="profilePanel" class="profile-panel">
    <div class="profile-content">
        <span onclick="closeProfile()">×</span>

        <h3>Profile</h3>
        <p>Nama Kamu</p>
    </div>
</div>

<style>
:root {
  --primary: #007bff;
  --bg: rgba(255,255,255,0.9);
  --text: #333;
  --radius: 18px;
  --shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* FOOTBAR */
.footbar {
  position: fixed;
  bottom: 15px;
  left: 50%;
  transform: translateX(-50%);
  width: 95%;
  max-width: 500px;
  display: flex;
  justify-content: space-around;
  background: var(--bg);
  backdrop-filter: blur(12px);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 8px 0;
  z-index: 999;
}

/* MENU */
.footbar a {
  flex: 1;
  text-align: center;
  font-size: 11px;
  color: var(--text);
  text-decoration: none;
  transition: 0.3s;
}

/* ICON */
.footbar a span {
  display: block;
  font-size: 20px;
  transition: 0.3s;
}

/* ACTIVE */
.footbar a.active {
  color: var(--primary);
  font-weight: 600;
}

/* DOT */
.footbar a.active::after {
  content: '';
  display: block;
  margin: 3px auto 0;
  width: 6px;
  height: 6px;
  background: var(--primary);
  border-radius: 50%;
}

/* HOVER */
.footbar a:hover span {
  transform: scale(1.2);
}
</style>


{{-- json --}}
<script>
fetch('/theme.json')
  .then(response => response.json())
  .then(theme => {

    // Ubah warna utama
    document.documentElement.style.setProperty('--primary', theme.primaryColor);

    // Background footbar
    document.documentElement.style.setProperty('--bg', theme.background);

    // Warna teks
    document.documentElement.style.setProperty('--text', theme.textColor);

    // Radius
    document.documentElement.style.setProperty('--radius', theme.radius);

    // Shadow
    document.documentElement.style.setProperty('--shadow', theme.shadow);

    // Animasi icon aktif
    document.querySelectorAll('.footbar a.active span').forEach(el => {
      el.style.transform = "scale(" + theme.activeScale + ")";
    });

  })
  .catch(error => console.log('JSON error:', error));
</script>
@include('component.profile-panel')
    <!-- Konten lain -->
    @yield('content')

    <!-- Profile Panel -->
    @include('component.profile-panel')

    <!-- JAVASCRIPT (TARUH DI SINI) -->
    <script>
    function openProfile() {
        document.getElementById("profilePanel").classList.add("active");
    }

    function closeProfile() {
        document.getElementById("profilePanel").classList.remove("active");
    }
    </script>
</body>
</html>