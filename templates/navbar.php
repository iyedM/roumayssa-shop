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
        <a href="/index.php" class="navbar-brand" style="display:flex; align-items:center; gap:0.5rem;">
            <img src="/assets/images/logo.jpg" alt="<?= SITE_NAME ?>" style="height:50px; width:auto; border-radius:8px;">
            <span style="font-weight:700; font-size:1.25rem; color:var(--primary-pink);"><?= SITE_NAME ?></span>
        </a>
        
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="navbar-menu" id="navbarMenu">
            <a href="/index.php" class="navbar-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="/shop.php" class="navbar-link"><i class="fas fa-store"></i> Boutique</a>
            
            <div class="navbar-dropdown">
                <a href="#" class="navbar-link"><i class="fas fa-th"></i> Catégories <i class="fas fa-chevron-down" style="font-size:0.8em; margin-left:0.2rem;"></i></a>
                <div class="dropdown-content">
                    <a href="/shop.php">Tout voir</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="/shop.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

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
