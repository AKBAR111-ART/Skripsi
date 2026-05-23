<div id="profilePanel" class="profile-panel">
    <div class="profile-panel-header">
        <img src="<?php echo e(asset('images/default-avatar.png')); ?>" alt="Avatar">
        <h4><?php echo e(Auth::user()->name ?? 'Pengguna'); ?></h4>
        <p><?php echo e(Auth::user()->email ?? 'user@example.com'); ?></p>
    </div>
    <div class="profile-panel-menu">
        <a href="<?php echo e(route('profile.index')); ?>">
            <i class="fas fa-user"></i>
            <span>Profil Tambak</span>
        </a>
        <a href="<?php echo e(route('pengaturan.index')); ?>">
            <i class="fas fa-sliders-h"></i>
            <span>Pengaturan</span>
        </a>
        <a href="<?php echo e(route('home')); ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <div class="divider"></div>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="<?php echo e(url('/logout')); ?>" method="POST" style="display: none;">
            <?php echo csrf_field(); ?>
        </form>
    </div>
</div>

<style>
    .profile-panel {
        position: fixed;
        top: 70px;
        right: 20px;
        width: 280px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 1001;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        overflow: hidden;
    }
    
    .profile-panel.show {
        transform: translateX(0);
    }
    
    .profile-panel-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
        text-align: center;
        color: white;
    }
    
    .profile-panel-header img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid white;
        margin-bottom: 10px;
        object-fit: cover;
    }
    
    .profile-panel-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .profile-panel-header p {
        margin: 5px 0 0;
        font-size: 11px;
        opacity: 0.8;
    }
    
    .profile-panel-menu {
        padding: 8px 0;
    }
    
    .profile-panel-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        text-decoration: none;
        color: #374151;
        transition: background 0.3s ease;
        font-size: 14px;
    }
    
    .profile-panel-menu a:hover {
        background: #f3f4f6;
    }
    
    .profile-panel-menu i {
        width: 20px;
        color: #667eea;
        font-size: 16px;
    }
    
    .profile-panel-menu .divider {
        height: 1px;
        background: #e5e7eb;
        margin: 8px 0;
    }
    
    .profile-panel-menu .logout-btn {
        color: #ef4444;
    }
    
    .profile-panel-menu .logout-btn i {
        color: #ef4444;
    }
    
    .profile-panel-menu .logout-btn:hover {
        background: #fef2f2;
    }
</style>

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
</script><?php /**PATH C:\xampp\htdocs\Skirpsi\tambak_udang\resources\views/components/profile-panel.blade.php ENDPATH**/ ?>