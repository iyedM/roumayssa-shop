<?php
session_start();
require_once "includes/db.php";
require_once "config/config.php";

// SEO Meta
$pageTitle = "Mon Panier";

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$cartItems = [];

// Fetch product details for cart items
if (!empty($cart)) {
    foreach ($cart as $pid => $qty) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id=:id");
        $stmt->execute(['id' => $pid]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prod) {
            $price = $prod['promo_price'] && $prod['promo_price'] < $prod['price'] ? $prod['promo_price'] : $prod['price'];
            $subtotal = $price * $qty;
            $total += $subtotal;
            
            $cartItems[] = [
                'product' => $prod,
                'quantity' => $qty,
                'price' => $price,
                'subtotal' => $subtotal
            ];
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<?php include 'templates/navbar.php'; ?>

<div class="container section">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/index.php">Accueil</a>
        <span class="breadcrumb-separator">/</span>
        <span>Panier</span>
    </div>
    
    <h1 class="mb-4"><i class="fas fa-shopping-cart"></i> Mon Panier</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="card text-center p-4">
            <i class="fas fa-shopping-cart" style="font-size:5rem; color:var(--primary-pink); opacity:0.3; margin-bottom:1.5rem;"></i>
            <h2>Votre panier est vide</h2>
            <p class="text-muted mb-3">Découvrez nos produits et ajoutez-les à votre panier !</p>
            <a href="/shop.php" class="btn btn-primary btn-lg">
                <i class="fas fa-store"></i> Découvrir la boutique
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-3" style="gap:2rem; align-items:flex-start;">
            <!-- Cart Items (2/3 width) -->
            <div style="grid-column: span 2;">
                <?php foreach ($cartItems as $item): ?>
                    <div class="card mb-3">
                        <div style="display:grid; grid-template-columns:120px 1fr auto; gap:1.5rem; align-items:center;">
                            <!-- Product Image -->
                            <a href="/product_detail.php?id=<?= $item['product']['id'] ?>">
                                <?php 
                                $imagePath = !empty($item['product']['image']) ? $item['product']['image'] : 'assets/images/no-image.png';
                                ?>
                                <img src="/<?= htmlspecialchars($imagePath) ?>" 
                                     alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                     class="cart-item-img">
                            </a>
                            
                            <!-- Product Info -->
                            <div>
                                <h3 class="mb-2">
                                    <a href="/product_detail.php?id=<?= $item['product']['id'] ?>" style="color:inherit;">
                                        <?= htmlspecialchars($item['product']['name']) ?>
                                    </a>
                                </h3>
                                
                                <p class="text-primary" style="font-size:1.25rem; font-weight:600; margin-bottom:0.5rem;">
                                    <?= number_format($item['price'], 2) ?> DT
                                </p>
                                
                                <div class="quantity-control">
                                    <span style="color:#666;">Quantité:</span>
                                    <span style="font-weight:600; color:var(--dark-text);"><?= $item['quantity'] ?></span>
                                </div>
                                
                                <p class="text-muted" style="font-size:0.875rem; margin-top:0.5rem; margin-bottom:0;">
                                    Sous-total: <strong><?= number_format($item['subtotal'], 2) ?> DT</strong>
                                </p>
                            </div>
                            
                            <!-- Remove Button -->
                            <div style="text-align:right;">
                                <a href="/cart_remove.php?id=<?= $item['product']['id'] ?>" 
                                   class="btn btn-outline btn-sm"
                                   onclick="return confirm('Voulez-vous vraiment retirer ce produit du panier ?')">
                                    <i class="fas fa-trash"></i> Retirer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="flex-between">
                    <a href="/shop.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Continuer mes achats
                    </a>
                </div>
            </div>
            
            <!-- Order Summary (1/3 width) -->
            <div>
                <div class="card" style="background: linear-gradient(135deg, var(--secondary-rose), var(--primary-pink)); color:white; position:sticky; top:100px;">
                    <h3 style="color:white; margin-bottom:1.5rem;">
                        <i class="fas fa-calculator"></i> Récapitulatif
                    </h3>
                    
                    <div style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.3);">
                        <div class="flex-between" style="margin-bottom:0.5rem;">
                            <span>Sous-total:</span>
                            <strong><?= number_format($total, 2) ?> DT</strong>
                        </div>
                        <div class="flex-between" style="font-size:0.875rem; opacity:0.9;">
                            <span><?= count($cartItems) ?> article<?= count($cartItems) > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom:1.5rem; padding-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.3);">
                        <div class="flex-between" style="font-size:1.25rem;">
                            <strong>Total:</strong>
                            <strong><?= number_format($total, 2) ?> DT</strong>
                        </div>
                        <p style="font-size:0.75rem; opacity:0.8; margin-top:0.5rem; margin-bottom:0;">
                            * Frais de livraison calculés à la commande
                        </p>
                    </div>
                    
                    <a href="/checkout.php" class="btn btn-secondary btn-block btn-lg">
                        <i class="fas fa-check-circle"></i> Passer la commande
                    </a>
                    
                    <p style="font-size:0.75rem; opacity:0.8; margin-top:1rem; margin-bottom:0; text-align:center;">
                        <i class="fas fa-lock"></i> Paiement sécurisé
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

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
