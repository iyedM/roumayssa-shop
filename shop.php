<?php
session_start();
require_once "includes/db.php";
require_once "config/config.php";

// SEO Meta
$pageTitle = "Boutique";
$pageDescription = "Parcourez notre collection complète de produits pour femmes et bébés.";

// Filters
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build SQL query
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];

if ($categoryFilter > 0) {
    $sql .= " AND p.category_id = :category";
    $params['category'] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND p.name LIKE :search";
    $params['search'] = '%' . $searchQuery . '%';
}

// Sorting
switch($sortBy) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<?php include 'templates/navbar.php'; ?>

<div class="container section">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/index.php">Accueil</a>
        <span class="breadcrumb-separator">/</span>
        <span>Boutique</span>
    </div>
    
    <h1 class="mb-4">Notre Boutique</h1>
    
    <!-- Filters Section -->
    <div class="card mb-4">
        <form method="GET" action="/shop.php" class="flex flex-wrap" style="gap:1rem; align-items:flex-end;">
            <!-- Search -->
            <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
                <label class="form-label" for="search">Rechercher</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       class="form-control" 
                       placeholder="Nom du produit..." 
                       value="<?= htmlspecialchars($searchQuery) ?>">
            </div>
            
            <!-- Category Filter -->
            <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
                <label class="form-label" for="category">Catégorie</label>
                <select id="category" name="category" class="form-control">
                    <option value="0">Toutes les catégories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Sort -->
            <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
                <label class="form-label" for="sort">Trier par</label>
                <select id="sort" name="sort" class="form-control">
                    <option value="newest" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Plus récent</option>
                    <option value="price_asc" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="price_desc" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    <option value="name" <?= $sortBy == 'name' ? 'selected' : '' ?>>Nom (A-Z)</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-bottom:0;">
                <i class="fas fa-search"></i> Filtrer
            </button>
            
            <?php if($categoryFilter || $searchQuery): ?>
                <a href="/shop.php" class="btn btn-secondary" style="margin-bottom:0;">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Products Count -->
    <p class="text-muted mb-3">
        <i class="fas fa-shopping-bag"></i> <?= count($products) ?> produit<?= count($products) > 1 ? 's' : '' ?> trouvé<?= count($products) > 1 ? 's' : '' ?>
    </p>
    
    <!-- Products Grid -->
    <?php if(empty($products)): ?>
        <div class="card text-center p-4">
            <i class="fas fa-box-open" style="font-size:4rem; color:var(--primary-pink); margin-bottom:1rem;"></i>
            <h3>Aucun produit trouvé</h3>
            <p class="text-muted">Essayez de modifier vos critères de recherche.</p>
            <a href="/shop.php" class="btn btn-primary">Voir tous les produits</a>
        </div>
    <?php else: ?>
        <div class="grid grid-4">
            <?php foreach($products as $product): ?>
                <div class="card product-card">
                    <?php if($product['promo_price'] && $product['promo_price'] < $product['price']): ?>
                        <span class="product-badge">PROMO</span>
                    <?php endif; ?>
                    
                    <a href="/product_detail.php?id=<?= $product['id'] ?>">
                        <?php 
                        $imagePath = !empty($product['image']) ? $product['image'] : 'assets/images/no-image.png';
                        ?>
                        <img src="/<?= htmlspecialchars($imagePath) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="product-card-img">
                    </a>
                    
                    <h3 class="card-title">
                        <a href="/product_detail.php?id=<?= $product['id'] ?>" style="color:inherit;">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h3>
                    
                    <?php if($product['category_name']): ?>
                        <p class="text-muted" style="font-size:0.875rem; margin-bottom:0.5rem;">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <div>
                        <?php if($product['promo_price'] && $product['promo_price'] < $product['price']): ?>
                            <span class="product-price"><?= number_format($product['promo_price'], 2) ?> DT</span>
                            <span class="product-old-price"><?= number_format($product['price'], 2) ?> DT</span>
                        <?php else: ?>
                            <span class="product-price"><?= number_format($product['price'], 2) ?> DT</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($product['stock'] > 0): ?>
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> En stock</span>
                    <?php else: ?>
                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rupture</span>
                    <?php endif; ?>
                    
                    <a href="/product_detail.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-block mt-2">
                        <i class="fas fa-eye"></i> Voir le produit
                    </a>
                </div>
            <?php endforeach; ?>
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
