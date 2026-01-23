<?php
session_start();
require_once "includes/db.php";
require_once "config/config.php";

// SEO Meta
$pageTitle = "Accueil";
$pageDescription = "Découvrez notre collection de vêtements pour femmes, accessoires et produits pour bébé. " . SITE_TAGLINE;

// Get featured/promoted products
$stmt = $pdo->query("
    SELECT p.*,
    (SELECT image FROM product_images WHERE product_id = p.id ORDER BY id LIMIT 1) as product_first_image
    FROM products p
    WHERE p.stock > 0 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$featuredProducts = $stmt->fetchAll();

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<?php include 'templates/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">✨ Bienvenue chez <?= SITE_NAME ?> ✨</h1>
        <p class="hero-subtitle">Votre boutique en ligne de vêtements féminins, accessoires et produits pour bébé</p>
        <a href="/shop.php" class="btn btn-primary btn-lg">Découvrir la Boutique</a>
    </div>
</section>

<!-- Categories Section -->
<section class="section">
    <div class="container">
        <h2 class="text-center mb-4">Nos Catégories</h2>
        <div class="grid grid-4">
            <?php 
            $categoryIcons = [
                'fas fa-tshirt',
                'fas fa-gem',
                'fas fa-baby',
                'fas fa-shopping-bag'
            ];
            
            foreach($categories as $index => $cat): 
                $icon = $categoryIcons[$index % 4];
            ?>
                <a href="/shop.php?category=<?= $cat['id'] ?>" style="text-decoration:none;">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="<?= $icon ?>"></i>
                        </div>
                        <h3><?= htmlspecialchars($cat['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section bg-white">
    <div class="container">
        <h2 class="text-center mb-4">Nos Produits</h2>
        
        <?php if(empty($featuredProducts)): ?>
            <p class="text-center text-muted">Aucun produit disponible pour le moment.</p>
        <?php else: ?>
            <div class="grid grid-4">
                <?php foreach($featuredProducts as $product): ?>
                    <div class="card product-card">
                        <?php if($product['promo_price'] && $product['promo_price'] < $product['price']): ?>
                            <span class="product-badge">PROMO</span>
                        <?php endif; ?>
                        
                        <a href="/product_detail.php?id=<?= $product['id'] ?>">
                            <?php 
                            $imagePath = !empty($product['product_first_image']) ? $product['product_first_image'] : 'assets/images/no-image.png';
                            ?>
                            <img src="/<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-card-img">
                        </a>
                        
                        <h3 class="card-title">
                            <a href="/product_detail.php?id=<?= $product['id'] ?>" style="color:inherit;">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>
                        
                        <div>
                            <?php if($product['promo_price'] && $product['promo_price'] < $product['price']): ?>
                                <span class="product-price"><?= number_format($product['promo_price'], 2) ?> DT</span>
                                <span class="product-old-price"><?= number_format($product['price'], 2) ?> DT</span>
                            <?php else: ?>
                                <span class="product-price"><?= number_format($product['price'], 2) ?> DT</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($product['stock'] > 0): ?>
                            <span class="badge badge-success">En stock</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Rupture</span>
                        <?php endif; ?>
                        
                        <a href="/product_detail.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-block mt-2">
                            Voir le produit
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="/shop.php" class="btn btn-outline btn-lg">Voir tous les produits</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action Section -->
<section class="section" style="background: linear-gradient(135deg, var(--secondary-rose), var(--primary-pink)); color:white; border-radius: var(--radius-lg);">
    <div class="container text-center">
        <h2 style="color:white;">Besoin d'aide ?</h2>
        <p style="font-size:1.125rem; opacity:0.95; margin-bottom:2rem;">
            Notre équipe est à votre écoute via WhatsApp
        </p>
        <a href="https://wa.me/<?= str_replace(['+', ' '], '', WHATSAPP_NUMBER) ?>" 
           class="btn btn-secondary btn-lg" 
           target="_blank">
            <i class="fab fa-whatsapp"></i> Contactez-nous sur WhatsApp
        </a>
    </div>
</section>

<?php include 'templates/footer.php'; ?>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/<?= str_replace(['+', ' '], '', WHATSAPP_NUMBER) ?>" 
   class="whatsapp-float" 
   target="_blank"
   aria-label="Contact WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- JavaScript -->
<script src="/assets/js/main.js"></script>
</body>
</html>
