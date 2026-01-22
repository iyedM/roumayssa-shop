<?php
// navbar.php - Modern Navigation
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../config/config.php";

// Calculate cart count
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += $qty;
    }
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<nav class="navbar">
    <div class="container navbar-container">
        <a href="/index.php" class="navbar-brand">
            <i class="fas fa-shopping-bag"></i> <?= SITE_NAME ?>
        </a>
        
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="navbar-menu" id="navbarMenu">
            <a href="/index.php" class="navbar-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="/shop.php" class="navbar-link"><i class="fas fa-store"></i> Boutique</a>
            <a href="/contact.php" class="navbar-link"><i class="fas fa-envelope"></i> Contact</a>
            
            <a href="/cart.php" class="navbar-cart">
                <i class="fas fa-shopping-cart"></i>
                Panier
                <?php if($cartCount > 0): ?>
                    <span class="navbar-cart-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            
            <?php if(isset($_SESSION['admin_id'])): ?>
                <a href="/admin/dashboard.php" class="navbar-link"><i class="fas fa-user-shield"></i> Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
