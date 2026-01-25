<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Count total active products
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE active = 1")->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name,
    (SELECT image FROM product_images WHERE product_id = p.id ORDER BY id LIMIT 1) as product_first_image
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
    LIMIT $perPage OFFSET $offset
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits - <?= SITE_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body { background:var(--neutral-beige); }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--space-lg);
        }
        .product-card {
            background: var(--white);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-normal);
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: var(--space-md);
        }
        .stock-badge {
            position: absolute;
            top: var(--space-sm);
            right: var(--space-sm);
            z-index: 2;
        }
    </style>
</head>
<body>

<?php include '../templates/admin_navbar.php'; ?>

<div class="container section">
    <div class="flex-between mb-4">
        <div>
            <h1><i class="fas fa-box"></i> Gestion des produits</h1>
            <p class="text-muted"><?= count($products) ?> produit<?= count($products) > 1 ? 's' : '' ?></p>
        </div>
        <a href="/admin/add_product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un produit
        </a>
    </div>
    
    <?php if(empty($products)): ?>
        <div class="card text-center p-4">
            <i class="fas fa-box-open" style="font-size:4rem; color:var(--primary-pink); opacity:0.3; margin-bottom:1rem;"></i>
            <h3>Aucun produit</h3>
            <p class="text-muted">Créez votre premier produit pour commencer à vendre.</p>
            <a href="/admin/add_product.php" class="btn btn-primary mt-2">
                <i class="fas fa-plus"></i> Créer un produit
            </a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach($products as $p): ?>
                <div class="product-card" style="position:relative;">
                    <?php if($p['stock'] > 0): ?>
                        <span class="stock-badge badge badge-success">
                            <i class="fas fa-check-circle"></i> En stock (<?= $p['stock'] ?>)
                        </span>
                    <?php else: ?>
                        <span class="stock-badge badge badge-danger">
                            <i class="fas fa-times-circle"></i> Rupture
                        </span>
                    <?php endif; ?>
                    
                    <?php 
                    $imgSrc = !empty($p['product_first_image']) ? '/' . $p['product_first_image'] : '/assets/images/no-image.png';
                    ?>
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
                    
                    <div class="product-info">
                        <h3 style="font-size:1rem; margin-bottom:0.5rem;">
                            <?= htmlspecialchars($p['name']) ?>
                        </h3>
                        
                        <?php if($p['category_name']): ?>
                            <p class="text-muted" style="font-size:0.875rem; margin-bottom:0.5rem;">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($p['category_name']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                            <div>
                                <strong class="text-primary" style="font-size:1.25rem;"><?= number_format($p['price'], 2) ?> DT</strong>
                                <?php if($p['purchase_price']): ?>
                                    <br><small class="text-muted">Achat: <?= number_format($p['purchase_price'], 2) ?> DT</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($p['description']): ?>
                            <p class="text-muted" style="font-size:0.875rem; margin-bottom:0.75rem;">
                                <?php 
                                $words = explode(' ', strip_tags($p['description']));
                                echo htmlspecialchars(implode(' ', array_slice($words, 0, 10))) . (count($words) > 10 ? '...' : '');
                                ?>
                            </p>
                        <?php endif; ?>
                        
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                            <button 
                                onclick="toggleActive(<?= $p['id'] ?>, <?= $p['active'] ?>)" 
                                class="btn btn-sm" 
                                id="toggle-btn-<?= $p['id'] ?>"
                                style="flex:1; <?= $p['active'] ? 'background:var(--success-green); color:white;' : 'background:#ccc; color:#666;' ?>">
                                <i class="fas <?= $p['active'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i> 
                                <?= $p['active'] ? 'Actif' : 'Inactif' ?>
                            </button>
                        </div>
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                            <a href="/admin/edit_product.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" style="flex:1;">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;" target="_blank">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <a href="/admin/delete_product.php?id=<?= $p['id'] ?>" 
                               class="btn btn-outline btn-sm"
                               onclick="return confirm('Supprimer ce produit ?')"
                               style="color:var(--error-red); border-color:var(--error-red);">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
        <div style="display:flex; justify-content:center; gap:0.5rem; margin-top:var(--space-xl); flex-wrap:wrap;">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-chevron-left"></i> Précédent
                </a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="btn btn-primary btn-sm"><?= $i ?></span>
                <?php elseif($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                    <a href="?page=<?= $i ?>" class="btn btn-outline btn-sm"><?= $i ?></a>
                <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                    <span class="btn btn-outline btn-sm" disabled>...</span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">
                    Suivant <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="/assets/js/main.js"></script>
<script>
function toggleActive(productId, currentStatus) {
    const btn = document.getElementById('toggle-btn-' + productId);
    const newStatus = currentStatus ? 0 : 1;
    
    // Disable button during request
    btn.disabled = true;
    
    fetch('/admin/toggle_product_active.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + productId + '&active=' + newStatus
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (newStatus === 1) {
                btn.style.background = 'var(--success-green)';
                btn.style.color = 'white';
                btn.innerHTML = '<i class="fas fa-eye"></i> Actif';
            } else {
                btn.style.background = '#ccc';
                btn.style.color = '#666';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i> Inactif';
            }
            btn.onclick = function() { toggleActive(productId, newStatus); };
        } else {
            alert('Erreur lors de la mise à jour');
        }
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur de connexion');
        btn.disabled = false;
    });
}
</script>
</body>
</html>
