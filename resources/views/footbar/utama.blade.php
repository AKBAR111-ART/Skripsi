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
     <main>
        @yield('content')
    </main>
</head>
<body>

 <div class="footbar">     
    <a href="{{ url('/') }}" class="active">🏠<br>Home</a>
  <a href="{{ url('/monitoring') }}">📊<br>Monitoring</a>
  <a href="{{ url('/history') }}">📜<br>History</a>
  <a href="{{ url('/pengaturan') }}">⚙️<br>Setting</a>
  <a href="{{ url('/Profile') }}">👤<br>Profile</a>
</div>

<style>
.footbar {
  position: fixed;
  bottom: 0;
  width: 100%;
  display: flex;
  background: #fff;
  border-top: 1px solid #ddd;
}

.footbar a {
  flex: 1;
  text-align: center;
  padding: 10px;
  font-size: 12px;
  text-decoration: none;
  color: #555;
}

.footbar a.active {
  color: #007bff;
}
</style>
</body>
</html>