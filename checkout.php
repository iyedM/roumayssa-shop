<?php
session_start();
require_once "includes/db.php";
require_once "config/config.php";

// SEO Meta
$pageTitle = "Passer la commande";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: /cart.php');
    exit;
}

$error = '';
$success = '';

// Calculate total
$total = 0;
$cartItems = [];
foreach ($cart as $pid => $qty) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=:id");
    $stmt->execute(['id' => $pid]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prod) {
        $price = $prod['promo_price'] && $prod['promo_price'] < $prod['price'] ? $prod['promo_price'] : $prod['price'];
        $subtotal = $price * $qty;
        $total += $subtotal;
        $cartItems[] = ['product' => $prod, 'quantity' => $qty, 'price' => $price, 'subtotal' => $subtotal];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $delivery_method = $_POST['delivery_method'] ?? 'home';
    
    // Add delivery cost
    $deliveryCost = $delivery_method === 'home' ? 7 : 0;
    $finalTotal = $total + $deliveryCost;
    
    if (!$customer_name || !$customer_phone || !$customer_address) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
    
    if (!$error) {
        try {
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_name, customer_phone, customer_address, total_price, created_at)
                VALUES (:name, :phone, :address, :total, NOW())
            ");
            $stmt->execute([
                'name' => $customer_name,
                'phone' => $customer_phone,
                'address' => $customer_address,
                'total' => $finalTotal
            ]);
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cart as $pid => $qty) {
                $stmt = $pdo->prepare("SELECT price, promo_price FROM products WHERE id=:id");
                $stmt->execute(['id' => $pid]);
                $prod = $stmt->fetch();
                $price = $prod['promo_price'] && $prod['promo_price'] < $prod['price'] ? $prod['promo_price'] : $prod['price'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (:order_id, :product_id, :qty, :price)
                ");
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $pid,
                    'qty' => $qty,
                    'price' => $price
                ]);
            }
            
            // Clear cart
            unset($_SESSION['cart']);
            $success = "Votre commande a été passée avec succès ! Numéro de commande : #$order_id";
        } catch (Exception $e) {
            $error = "Une erreur est survenue. Veuillez réessayer.";
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
        <a href="/cart.php">Panier</a>
        <span class="breadcrumb-separator">/</span>
        <span>Commande</span>
    </div>
    
    <h1 class="mb-4"><i class="fas fa-shopping-bag"></i> Passer la commande</h1>
    
    <?php if($success): ?>
        <div class="card text-center p-4">
            <i class="fas fa-check-circle" style="font-size:5rem; color:var(--success-green); margin-bottom:1.5rem;"></i>
            <h2>Commande confirmée !</h2>
            <div class="alert alert-success" style="margin:1.5rem auto; max-width:600px;">
                <?= $success ?>
            </div>
            <p class="text-muted mb-3">Nous vous contacterons bientôt pour confirmer les détails de livraison.</p>
            <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
                <a href="/shop.php" class="btn btn-primary">
                    <i class="fas fa-store"></i> Continuer vos achats
                </a>
                <a href="https://wa.me/<?= str_replace(['+', ' '], '', WHATSAPP_NUMBER) ?>" class="btn btn-secondary" target="_blank">
                    <i class="fab fa-whatsapp"></i> Nous contacter
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-3" style="gap:2rem; align-items:flex-start;">
            <!-- Checkout Form (2/3 width) -->
            <div style="grid-column: span 2;">
                <div class="card">
                    <h2 class="mb-3"><i class="fas fa-user"></i> Informations de livraison</h2>
                    
                    <?php if($error): ?>
                        <div class="alert alert-error mb-3">
                            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/checkout.php" data-validate>
                        <div class="form-group">
                            <label class="form-label" for="customer_name">
                                Nom et Prénom <span style="color:var(--error-red);">*</span>
                            </label>
                            <input type="text" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   class="form-control" 
                                   placeholder="Ex: Fatma Ben Ali"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="customer_phone">
                                Téléphone <span style="color:var(--error-red);">*</span>
                            </label>
                            <input type="tel" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   class="form-control" 
                                   placeholder="Ex: +216 XX XXX XXX"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="customer_address">
                                Adresse complète <span style="color:var(--error-red);">*</span>
                            </label>
                            <textarea id="customer_address" 
                                      name="customer_address" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Rue, ville, code postal..."
                                      required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-truck"></i> Mode de livraison <span style="color:var(--error-red);">*</span>
                            </label>
                            
                            <label style="display:block; padding:1rem; border:2px solid #E0E0E0; border-radius:var(--radius-md); margin-bottom:0.75rem; cursor:pointer; transition:all 0.2s;"
                                   onmouseover="this.style.borderColor='var(--primary-pink)'"
                                   onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#E0E0E0'"
                                   onclick="this.querySelector('input').checked=true; this.style.borderColor='var(--primary-pink)'; document.querySelectorAll('label[for^=delivery]').forEach(l => {if(l!==this) l.style.borderColor='#E0E0E0'})">
                                <input type="radio" 
                                       id="delivery_home" 
                                       name="delivery_method" 
                                       value="home" 
                                       checked
                                       style="margin-right:0.5rem;">
                                <strong>Livraison à domicile</strong> - 7 DT
                                <p style="margin:0.5rem 0 0 1.5rem; color:#666; font-size:0.875rem;">
                                    Livraison dans toute la Tunisie sous 2-5 jours ouvrables
                                </p>
                            </label>
                            
                            <label style="display:block; padding:1rem; border:2px solid #E0E0E0; border-radius:var(--radius-md); cursor:pointer; transition:all 0.2s;"
                                   onmouseover="this.style.borderColor='var(--primary-pink)'"
                                   onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#E0E0E0'"
                                   onclick="this.querySelector('input').checked=true; this.style.borderColor='var(--primary-pink)'; document.querySelectorAll('label[for^=delivery]').forEach(l => {if(l!==this) l.style.borderColor='#E0E0E0'})">
                                <input type="radio" 
                                       id="delivery_pickup" 
                                       name="delivery_method" 
                                       value="pickup"
                                       style="margin-right:0.5rem;">
                                <strong>Retrait en magasin</strong> - Gratuit
                                <p style="margin:0.5rem 0 0 1.5rem; color:#666; font-size:0.875rem;">
                                    Disponible sous 24-48h
                                </p>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-check-circle"></i> Confirmer la commande
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary (1/3 width) -->
            <div>
                <div class="card" style="position:sticky; top:100px;">
                    <h3 class="mb-3"><i class="fas fa-receipt"></i> Récapitulatif</h3>
                    
                    <!-- Cart Items -->
                    <div style="margin-bottom:1.5rem; max-height:300px; overflow-y:auto; padding-right:0.5rem;">
                        <?php foreach ($cartItems as $item): ?>
                            <div style="display:flex; gap:0.75rem; margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid #E0E0E0;">
                                <?php 
                                $imagePath = !empty($item['product']['image']) ? $item['product']['image'] : 'assets/images/no-image.png';
                                ?>
                                <img src="/<?= htmlspecialchars($imagePath) ?>" 
                                     alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                     style="width:60px; height:60px; object-fit:cover; border-radius:var(--radius-sm);">
                                <div style="flex:1;">
                                    <p style="font-weight:600; margin-bottom:0.25rem; font-size:0.875rem;">
                                        <?= htmlspecialchars($item['product']['name']) ?>
                                    </p>
                                    <p style="color:#666; font-size:0.75rem; margin-bottom:0;">
                                        <?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?> DT
                                    </p>
                                </div>
                                <div style="text-align:right;">
                                    <span style="font-weight:600; color:var(--primary-pink);">
                                        <?= number_format($item['subtotal'], 2) ?> DT
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Totals -->
                    <div style="padding-top:1rem; border-top:2px solid #E0E0E0;">
                        <div class="flex-between mb-2">
                            <span>Sous-total:</span>
                            <strong><?= number_format($total, 2) ?> DT</strong>
                        </div>
                        <div class="flex-between mb-2" id="deliveryCostDisplay">
                            <span>Livraison:</span>
                            <strong>7.00 DT</strong>
                        </div>
                        <div class="flex-between" style="font-size:1.25rem; color:var(--primary-pink); padding-top:1rem; border-top:1px solid #E0E0E0;">
                            <strong>Total:</strong>
                            <strong id="finalTotal"><?= number_format($total + 7, 2) ?> DT</strong>
                        </div>
                    </div>
                    
                    <p style="font-size:0.75rem; color:#999; margin-top:1rem; margin-bottom:0; text-align:center;">
                        <i class="fas fa-lock"></i> Paiement à la livraison
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
<script>
// Update delivery cost dynamically
document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const deliveryCost = this.value === 'home' ? 7.00 : 0.00;
        const subtotal = <?= $total ?>;
        const finalTotal = subtotal + deliveryCost;
        
        document.getElementById('deliveryCostDisplay').innerHTML = 
            '<span>Livraison:</span><strong>' + deliveryCost.toFixed(2) + ' DT</strong>';
        document.getElementById('finalTotal').textContent = finalTotal.toFixed(2) + ' DT';
    });
});
</script>
</body>
</html>
