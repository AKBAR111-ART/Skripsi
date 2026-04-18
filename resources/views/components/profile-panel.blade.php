<!-- OVERLAY -->
<div id="overlay" class="overlay" onclick="closeProfile()"></div>

<!-- PANEL -->
<div id="profilePanel" class="profile-panel">
    <div class="profile-content">

        <!-- CLOSE -->
        <span class="close-btn" onclick="closeProfile()">×</span>

        <h3>👤 Profile</h3>
        <p>Nama Kamu</p>

        <hr>

        <!-- THEME -->
        <label><b>Pilih Warna:</b></label><br>
        <input type="color" onchange="changeTheme(this.value)">

        <br><br>

        <!-- DARK MODE -->
        <button class="btn-theme" onclick="toggleDarkMode()">
            🌙 Dark Mode
        </button>

    </div>
</div>