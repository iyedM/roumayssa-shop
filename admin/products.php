<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
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
        .admin-navbar {
            background: var(--dark-text);
            color: var(--white);
            padding: var(--space-md) 0;
            box-shadow: var(--shadow-md);
        }
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

<nav class="admin-navbar">
    <div class="container navbar-container">
        <a href="/admin/dashboard.php" style="color:var(--white); font-weight:600; font-size:1.25rem;">
            <i class="fas fa-shield-alt"></i> Administration
        </a>
        <div class="navbar-menu">
            <a href="/" class="navbar-link" style="color:var(--white);"><i class="fas fa-home"></i> Site</a>
            <a href="/admin/products.php" class="navbar-link" style="color:var(--secondary-rose);"><i class="fas fa-box"></i> Produits</a>
            <a href="/admin/categories.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-tags"></i> Catégories</a>
            <a href="/admin/orders.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="/admin/logout.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

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
                    $imgSrc = !empty($p['image']) ? '../' . htmlspecialchars($p['image']) : '../assets/images/no-image.png';
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
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
