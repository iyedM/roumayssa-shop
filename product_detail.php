<?php
session_start();
require_once "includes/db.php";
require_once "config/config.php";

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: /shop.php');
    exit;
}

// Get product
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = :id
");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: /shop.php');
    exit;
}

// SEO Meta
$pageTitle = $product['name'];
$pageDescription = substr(strip_tags($product['description']), 0, 160);

// Get product images
$stmtImg = $pdo->prepare("SELECT image FROM product_images WHERE product_id = :id ORDER BY id");
$stmtImg->execute(['id' => $id]);
$imagesDb = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

$images = [];
if (!empty($imagesDb)) {
    foreach ($imagesDb as $img) {
        $images[] = $img['image'];
    }
} elseif (!empty($product['image'])) {
    $images[] = $product['image'];
} else {
    $images[] = 'assets/images/no-image.png';
}
?>
<?php include 'includes/header.php'; ?>

<?php include 'templates/navbar.php'; ?>

<div class="container section">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/index.php">Accueil</a>
        <span class="breadcrumb-separator">/</span>
        <a href="/shop.php">Boutique</a>
        <span class="breadcrumb-separator">/</span>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </div>
    
    <div class="grid grid-2" style="gap:3rem; align-items:flex-start;">
        <!-- Product Images -->
        <div>
            <div style="background:white; padding:1.5rem; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); position:relative;">
                <div style="margin-bottom:1rem; position:relative;">
                    <img id="mainImg" 
                         src="/<?= htmlspecialchars($images[0]) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         style="width:100%; height:400px; object-fit:contain; border-radius:var(--radius-md); transition: opacity 0.3s;">
                    
                    <?php if (count($images) > 1): ?>
                        <!-- Navigation Arrows -->
                        <button onclick="prevImage()" 
                                style="position:absolute; left:10px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.9); border:none; border-radius:50%; width:50px; height:50px; cursor:pointer; box-shadow:var(--shadow-md); display:flex; align-items:center; justify-content:center; transition:all 0.2s;"
                                onmouseover="this.style.background='var(--primary-pink)'; this.style.color='white';"
                                onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.color='#333';">
                            <i class="fas fa-chevron-left" style="font-size:1.5rem;"></i>
                        </button>
                        
                        <button onclick="nextImage()" 
                                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.9); border:none; border-radius:50%; width:50px; height:50px; cursor:pointer; box-shadow:var(--shadow-md); display:flex; align-items:center; justify-content:center; transition:all 0.2s;"
                                onmouseover="this.style.background='var(--primary-pink)'; this.style.color='white';"
                                onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.color='#333';">
                            <i class="fas fa-chevron-right" style="font-size:1.5rem;"></i>
                        </button>
                        
                        <!-- Image Counter -->
                        <div style="position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,0.7); color:white; padding:0.5rem 1rem; border-radius:var(--radius-sm); font-size:0.875rem;">
                            <span id="currentImageIndex">1</span> / <?= count($images) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($images) > 1): ?>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(80px, 1fr)); gap:0.75rem;">
                        <?php foreach ($images as $index => $img): ?>
                            <img src="/<?= htmlspecialchars($img) ?>" 
                                 onclick="changeImage('/<?= htmlspecialchars($img) ?>', <?= $index ?>)" 
                                 id="thumb-<?= $index ?>"
                                 alt="Image produit"
                                 class="thumbnail-img"
                                 style="width:100%; height:80px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer; border:2px solid <?= $index === 0 ? 'var(--primary-pink)' : 'transparent' ?>; transition:all 0.2s;"
                                 onmouseover="this.style.borderColor='var(--primary-pink)'"
                                 onmouseout="if(this.id !== 'thumb-' + currentImageIdx) this.style.borderColor='transparent'">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Information -->
        <div class="card">
            <h1 class="mb-2"><?= htmlspecialchars($product['name']) ?></h1>
            
            <?php if($product['category_name']): ?>
                <p class="text-muted mb-3">
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                </p>
            <?php endif; ?>
            
            <!-- Price -->
            <div class="mb-3" style="border-top:1px solid #E0E0E0; border-bottom:1px solid #E0E0E0; padding:1rem 0;">
                <?php if($product['promo_price'] && $product['promo_price'] < $product['price']): ?>
                    <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                        <span class="product-price" style="font-size:2rem;"><?= number_format($product['promo_price'], 2) ?> DT</span>
                        <span class="product-old-price"><?= number_format($product['price'], 2) ?> DT</span>
                        <span class="badge badge-danger">
                            <i class="fas fa-percent"></i> 
                            <?= round((($product['price'] - $product['promo_price']) / $product['price']) * 100) ?>% OFF
                        </span>
                    </div>
                <?php else: ?>
                    <span class="product-price" style="font-size:2rem;"><?= number_format($product['price'], 2) ?> DT</span>
                <?php endif; ?>
            </div>
            
            <!-- Stock Status -->
            <div class="mb-3">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge badge-success" style="font-size:1rem; padding:0.5rem 1rem;">
                        <i class="fas fa-check-circle"></i> En stock (<?= $product['stock'] ?> disponible<?= $product['stock'] > 1 ? 's' : '' ?>)
                    </span>
                <?php else: ?>
                    <span class="badge badge-danger" style="font-size:1rem; padding:0.5rem 1rem;">
                        <i class="fas fa-times-circle"></i> Rupture de stock
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <?php if ($product['description']): ?>
                <div class="mb-3">
                    <h3><i class="fas fa-info-circle"></i> Description</h3>
                    <p style="white-space:pre-wrap; color:#555; line-height:1.8;">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Add to Cart Form -->
            <?php if ($product['stock'] > 0): ?>
                <form method="post" action="/cart_add.php" style="border-top:1px solid #E0E0E0; padding-top:1.5rem;">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="quantity">Quantité</label>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <button type="button" 
                                    class="quantity-btn" 
                                    onclick="updateQuantity(document.getElementById('quantity'), -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   id="quantity"
                                   name="quantity" 
                                   value="1" 
                                   min="1" 
                                   max="<?= $product['stock'] ?>" 
                                   class="quantity-input"
                                   required>
                            <button type="button" 
                                    class="quantity-btn" 
                                    onclick="updateQuantity(document.getElementById('quantity'), 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Ce produit est actuellement en rupture de stock.
                </div>
            <?php endif; ?>
            
            <!-- Additional Info -->
            <div style="margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid #E0E0E0;">
                <p class="text-muted" style="font-size:0.875rem; margin-bottom:0.5rem;">
                    <i class="fas fa-truck"></i> Livraison disponible dans toute la Tunisie
                </p>
                <p class="text-muted" style="font-size:0.875rem; margin-bottom:0;">
                    <i class="fab fa-whatsapp"></i> Des questions ? <a href="https://wa.me/<?= str_replace(['+', ' '], '', WHATSAPP_NUMBER) ?>" target="_blank" style="color:var(--primary-pink);">Contactez-nous sur WhatsApp</a>
                </p>
            </div>
        </div>
    </div>
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
// Image gallery with arrow navigation
const images = <?= json_encode($images) ?>;
let currentImageIdx = 0;

function changeImage(src, index) {
    const mainImg = document.getElementById('mainImg');
    mainImg.style.opacity = '0';
    
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
        currentImageIdx = index;
        
        // Update counter if exists
        const counter = document.getElementById('currentImageIndex');
        if(counter) counter.textContent = index + 1;
        
        // Update thumbnail borders
        document.querySelectorAll('.thumbnail-img').forEach((thumb, i) => {
            if (thumb.id === 'thumb-' + index) {
                thumb.style.borderColor = 'var(--primary-pink)';
            } else {
                thumb.style.borderColor = 'transparent';
            }
        });
    }, 150);
}

function nextImage() {
    if (images.length <= 1) return;
    currentImageIdx = (currentImageIdx + 1) % images.length;
    changeImage('/' + images[currentImageIdx], currentImageIdx);
}

function prevImage() {
    if (images.length <= 1) return;
    currentImageIdx = (currentImageIdx - 1 + images.length) % images.length;
    changeImage('/' + images[currentImageIdx], currentImageIdx);
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft') prevImage();
});
</script>
</body>
</html>
