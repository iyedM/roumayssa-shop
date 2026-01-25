<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="admin-navbar">
    <div class="container navbar-container">
        <a href="/admin/dashboard.php" class="admin-logo">
            <i class="fas fa-shield-alt"></i> Administration
        </a>
        
        <button class="admin-navbar-toggle" id="adminNavbarToggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="navbar-menu" id="adminNavbarMenu">
            <a href="/" class="navbar-link"><i class="fas fa-home"></i> Site</a>
            <a href="/admin/dashboard.php" class="navbar-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/products.php" class="navbar-link <?= in_array($currentPage, ['products.php', 'add_product.php', 'edit_product.php']) ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Produits
            </a>
            <a href="/admin/categories.php" class="navbar-link <?= in_array($currentPage, ['categories.php', 'add_category.php', 'edit_category.php']) ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Catégories
            </a>
            <a href="/admin/orders.php" class="navbar-link <?= in_array($currentPage, ['orders.php', 'order_detail.php']) ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Commandes
            </a>
            <a href="/admin/messages.php" class="navbar-link <?= in_array($currentPage, ['messages.php', 'message_detail.php']) ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Messages
                <?php if(isset($unreadMessages) && $unreadMessages > 0): ?>
                    <span class="navbar-badge"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/logout.php" class="navbar-link logout-link">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>

<style>
/* Base Admin Navbar Styles */
.admin-navbar {
    background: var(--dark-text);
    color: var(--white);
    padding: var(--space-md) 0;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-logo {
    color: var(--white);
    font-weight: 600;
    font-size: 1.25rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.navbar-menu {
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.navbar-link {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.navbar-link:hover, .navbar-link.active {
    color: var(--white);
    background: rgba(255, 255, 255, 0.1);
}

.navbar-link.active {
    background: var(--primary-pink);
    color: var(--white);
}

.logout-link {
    color: var(--secondary-rose);
}

.logout-link:hover {
    background: rgba(255, 71, 126, 0.1);
    color: #ff4d8d;
}

.navbar-badge {
    background: var(--error-red);
    color: white;
    font-size: 0.7rem;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: -5px;
}

.admin-navbar-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--white);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .admin-navbar-toggle {
        display: block;
    }

    .navbar-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--dark-text);
        flex-direction: column;
        align-items: flex-start;
        padding: 0; /* Reset padding when closed */
        gap: 0;
        max-height: 0;
        opacity: 0; /* Add opacity for smoother transition */
        visibility: hidden; /* Prevent interaction when closed */
        overflow: hidden;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }

    .navbar-menu.active {
        max-height: 600px;
        padding: var(--space-md);
        opacity: 1;
        visibility: visible;
    }

    .navbar-link {
        width: 100%;
        padding: var(--space-md);
        border-radius: 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .navbar-link:last-child {
        border-bottom: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('adminNavbarToggle');
    const menu = document.getElementById('adminNavbarMenu');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('active');
            
            // Change icon
            const icon = toggle.querySelector('i');
            if (menu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (menu.classList.contains('active') && !menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.classList.remove('active');
                const icon = toggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
});
</script>
