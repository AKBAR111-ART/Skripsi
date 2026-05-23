<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Tambak Udang'); ?></title>
    
    <!-- Font & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
    
    <!-- 🔥 BACKGROUND GAMBAR UDANG -->
    <style>
        body {
            background: url('<?php echo e(asset('images/udang.png')); ?>') no-repeat center center fixed !important;
            background-size: cover !important;
        }
        
        /* Overlay gelap agar teks lebih terbaca */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
        
        /* Pastikan konten di atas overlay */
        .app-header, .app-main, .app-footbar {
            position: relative;
            z-index: 1;
        }
        
        /* Card tetap putih transparan */
        .card-premium, .stat-card, .info-card, .gauge-card, .feeding-card, .status-card, .chart-card, .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
          /* Pastikan header dan footbar fixed */
    .app-header {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1000 !important;
    }
    
    .app-footbar {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1000 !important;
    }
    
    /* Konten utama bisa di-scroll */
    .app-main {
        margin-top: 70px;
        margin-bottom: 70px;
        min-height: calc(100vh - 140px);
        overflow-y: auto;
    }
    
    /* Body tidak boleh overflow horizontal */
    body {
        overflow-x: hidden;
        position: relative;
        margin: 0;
        padding: 0;
    }
    </style>
    
    <!-- Page Specific CSS -->
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- HEADER -->
    <div class="app-header">
        <div class="header-left">
            <img src="<?php echo e(asset('images/logo-udang.png')); ?>" class="logo-img" alt="Logo">
            <span class="logo-text">Tambak Mandhala</span>
        </div>
        <div class="header-right">
            <div class="profile-trigger" onclick="toggleProfilePanel()">
                <img src="<?php echo e(asset('images/default-avatar.png')); ?>" alt="Avatar">
            </div>
        </div>
    </div>

    <!-- PROFILE PANEL -->
    <?php echo $__env->make('components.profile-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- MAIN CONTENT -->
    <main class="app-main">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- FOOTBAR -->
    <nav class="app-footbar">
        <a href="<?php echo e(route('home')); ?>" class="<?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo e(route('monitoring')); ?>" class="<?php echo e(request()->routeIs('monitoring') ? 'active' : ''); ?>">
            <i class="fas fa-chart-line"></i>
            <span>Monitoring</span>
        </a>
        <a href="<?php echo e(route('history.index')); ?>" class="<?php echo e(request()->routeIs('history.index') ? 'active' : ''); ?>">
            <i class="fas fa-history"></i>
            <span>History</span>
        </a>
        <a href="<?php echo e(route('pengaturan.index')); ?>" class="<?php echo e(request()->routeIs('pengaturan.index') ? 'active' : ''); ?>">
            <i class="fas fa-sliders-h"></i>
            <span>Setting</span>
        </a>
        <a href="<?php echo e(route('profile.index')); ?>" class="<?php echo e(request()->routeIs('profile.index') ? 'active' : ''); ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Global Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    
    <!-- Page Specific Scripts -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>

<script>
    function toggleProfilePanel() {
        const panel = document.getElementById('profilePanel');
        if (panel) {
            panel.classList.toggle('show');
        }
    }
    
    document.addEventListener('click', function(event) {
        const panel = document.getElementById('profilePanel');
        const trigger = document.querySelector('.profile-trigger');
        if (panel && trigger && !trigger.contains(event.target) && !panel.contains(event.target)) {
            panel.classList.remove('show');
        }
    });
</script><?php /**PATH C:\xampp\htdocs\Skirpsi\tambak_udang\resources\views/footbar/utama.blade.php ENDPATH**/ ?>