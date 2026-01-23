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

$stmt4 = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'confirmé'");
$totalRevenue = $stmt4->fetchColumn() ?? 0;

// Purchase price calculation (cost of confirmed orders)
$stmt5 = $pdo->query("
    SELECT SUM(oi.quantity * p.purchase_price) as total_cost
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'confirmé'
");
$totalPurchases = $stmt5->fetchColumn() ?? 0;

// Profit calculation
$totalProfit = $totalRevenue - $totalPurchases;

// New orders count
$stmt6 = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'nouvelle'");
$newOrders = $stmt6->fetchColumn();

// Unread messages count
$stmt7 = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE lu = 0");
$unreadMessages = $stmt7->fetchColumn();

// Recent orders
$recentOrders = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Low stock alert
$lowStockProducts = $pdo->query("
    SELECT name, stock FROM products 
    WHERE stock < 5 AND stock > 0
    ORDER BY stock ASC 
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
            <a href="/admin/messages.php" class="navbar-link" style="color:var(--white); position:relative;">
                <i class="fas fa-envelope"></i> Messages
                <?php if($unreadMessages > 0): ?>
                    <span style="position:absolute; top:-5px; right:-5px; background:var(--error-red); color:white; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:bold;"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </a>
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
            <?php if($newOrders > 0): ?>
                <span class="badge badge-warning" style="position:absolute; top:1rem; right:1rem; background:#FF9800;">
                    <?= $newOrders ?> nouvelle<?= $newOrders > 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="stat-card" style="position:relative; background: linear-gradient(135deg, #4CAF50, #45a049);">
            <i class="fas fa-coins stat-icon"></i>
            <h3>Revenu (Confirmé)</h3>
            <p class="stat-value"><?= number_format($totalRevenue, 0) ?> <span style="font-size:1rem;">DT</span></p>
        </div>
        
        <div class="stat-card" style="position:relative; background: linear-gradient(135deg, #FF8A80, #FFC1E3);">
            <i class="fas fa-chart-line stat-icon"></i>
            <h3>Bénéfice (Estimé)</h3>
            <p class="stat-value" style="<?= $totalProfit < 0 ? 'color:#FFEBEE;' : '' ?>">
                <?= number_format($totalProfit, 0) ?> <span style="font-size:1rem;">DT</span>
            </p>
            <small style="opacity:0.8; font-size:0.75rem;">Coût: <?= number_format($totalPurchases, 0) ?> DT</small>
        </div>
    </div>
    
    <!-- Quick Actions & Recent Orders -->
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
                <a href="/admin/messages.php" class="btn btn-outline" style="position:relative;">
                    <i class="fas fa-envelope"></i> Gérer les messages
                    <?php if($unreadMessages > 0): ?>
                        <span class="badge badge-danger" style="position:absolute; top:-8px; right:-8px;"><?= $unreadMessages ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <!-- Recent Orders & Alerts -->
        <div>
            <!-- Low Stock Alert -->
            <?php if(!empty($lowStockProducts)): ?>
            <div class="card mb-3" style="border-left:4px solid var(--warning-orange);">
                <h3 class="mb-2"><i class="fas fa-exclamation-triangle" style="color:var(--warning-orange);"></i> Stock faible</h3>
                <?php foreach($lowStockProducts as $prod): ?>
                    <div style="padding:0.5rem; background:var(--neutral-beige); border-radius:var(--radius-sm); margin-bottom:0.5rem;">
                        <strong><?= htmlspecialchars($prod['name']) ?></strong>
                        <span class="badge badge-warning" style="float:right;">Stock: <?= $prod['stock'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
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
