<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get statistics
$stmt1 = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt1->fetchColumn();

$stmt2 = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt2->fetchColumn();

$stmt3 = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $stmt3->fetchColumn();

$stmt4 = $pdo->query("SELECT SUM(total_price) FROM orders");
$totalRevenue = $stmt4->fetchColumn() ?? 0;

// Recent orders
$recentOrders = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?= SITE_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        .admin-navbar {
            background: var(--dark-text);
            color: var(--white);
            padding: var(--space-md) 0;
            box-shadow: var(--shadow-md);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-pink), var(--accent-coral));
            color: var(--white);
            padding: var(--space-xl);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            transition: transform var(--transition-fast);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }
        
        .stat-card h3 {
            color: var(--white);
            opacity: 0.9;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0;
        }
    </style>
</head>
<body style="background:var(--neutral-beige);">

<nav class="admin-navbar">
    <div class="container navbar-container">
        <a href="/admin/dashboard.php" style="color:var(--white); font-weight:600; font-size:1.25rem;">
            <i class="fas fa-shield-alt"></i> Administration
        </a>
        <div class="navbar-menu">
            <a href="/" class="navbar-link" style="color:var(--white);"><i class="fas fa-home"></i> Site</a>
            <a href="/admin/products.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-box"></i> Produits</a>
            <a href="/admin/categories.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-tags"></i> Catégories</a>
            <a href="/admin/orders.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="/admin/logout.php" class="navbar-link" style="color:var(--secondary-rose);"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container section">
    <h1 class="mb-4">
        <i class="fas fa-chart-line"></i> Tableau de bord
    </h1>
    <p class="text-muted mb-4">Bienvenue, <?= htmlspecialchars($_SESSION['admin_name']) ?> !</p>
    
    <!-- Statistics Cards -->
    <div class="grid grid-4 mb-4">
        <div class="stat-card" style="position:relative; background: linear-gradient(135deg, #FF6B9D, #FF8A80);">
            <i class="fas fa-box stat-icon"></i>
            <h3>Produits</h3>
            <p class="stat-value"><?= $totalProducts ?></p>
        </div>
        
        <div class="stat-card" style="position:relative; background: linear-gradient(135deg, #FFC1E3, #FF6B9D);">
            <i class="fas fa-shopping-cart stat-icon"></i>
            <h3>Commandes</h3>
            <p class="stat-value"><?= $totalOrders ?></p>
        </div>
        
        <div class="stat-card" style="position:relative; background: linear-gradient(135deg, #FF8A80, #FFC1E3);">
            <i class="fas fa-tags stat-icon"></i>
            <h3>Catégories</h3>
            <p class="stat-value"><?= $totalCategories ?></p>
        </div>
        
        <div class="stat-card" style="position:relative; background: linear-gradient(135deg, #4CAF50, #45a049);">
            <i class="fas fa-coins stat-icon"></i>
            <h3>Revenu Total</h3>
            <p class="stat-value"><?= number_format($totalRevenue, 0) ?> <span style="font-size:1rem;">DT</span></p>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-2">
        <div class="card">
            <h2 class="mb-3"><i class="fas fa-bolt"></i> Actions rapides</h2>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <a href="/admin/add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
                <a href="/admin/products.php" class="btn btn-outline">
                    <i class="fas fa-box"></i> Gérer les produits
                </a>
                <a href="/admin/categories.php" class="btn btn-outline">
                    <i class="fas fa-tags"></i> Gérer les catégories
                </a>
                <a href="/admin/orders.php" class="btn btn-outline">
                    <i class="fas fa-shopping-cart"></i> Voir les commandes
                </a>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="card">
            <h2 class="mb-3"><i class="fas fa-clock"></i> Commandes récentes</h2>
            <?php if (empty($recentOrders)): ?>
                <p class="text-muted">Aucune commande pour le moment.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <?php foreach ($recentOrders as $order): ?>
                        <div style="padding:1rem; background:var(--neutral-beige); border-radius:var(--radius-sm); display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong>Commande #<?= $order['id'] ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($order['customer_name']) ?></small>
                            </div>
                            <div style="text-align:right;">
                                <strong class="text-primary"><?= number_format($order['total_price'], 2) ?> DT</strong><br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($order['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="/admin/orders.php" class="btn btn-secondary btn-block mt-3">
                    Voir toutes les commandes
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
