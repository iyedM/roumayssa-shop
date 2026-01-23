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
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Count total
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Get all orders with status
$stmt = $pdo->query("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    ORDER BY 
        CASE 
            WHEN o.status = 'nouvelle' THEN 1
            WHEN o.status = 'confirmé' THEN 2
            ELSE 3
        END,
        o.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$orders = $stmt->fetchAll();

// Calculate statistics
$stats = [
    'nouvelle' => 0,
    'confirmé' => 0,
    'rejeté' => 0,
    'total' => count($orders)
];

foreach($orders as $order) {
    $status = $order['status'] ?? 'nouvelle';
    if(isset($stats[$status])) {
        $stats[$status]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes - <?= SITE_NAME ?></title>
    
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
        .table-container {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: var(--neutral-beige);
            padding: var(--space-md);
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #E0E0E0;
        }
        td {
            padding: var(--space-md);
            border-bottom: 1px solid #F0F0F0;
        }
        tr:hover {
            background: var(--neutral-beige);
        }
        .status-nouvelle {
            background: #FFF3E0;
            color: #EF6C00;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-weight: 600;
        }
        .status-confirmé {
            background: #E8F5E9;
            color: #2E7D32;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-weight: 600;
        }
        .status-rejeté {
            background: #FFEBEE;
            color: #C62828;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-weight: 600;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }
        .stat-card {
            background: var(--white);
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-pink);
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
            <a href="/admin/products.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-box"></i> Produits</a>
            <a href="/admin/categories.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-tags"></i> Catégories</a>
            <a href="/admin/orders.php" class="navbar-link" style="color:var(--secondary-rose);"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="/admin/logout.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container section">
    <div class="mb-4">
        <h1><i class="fas fa-shopping-cart"></i> Gestion des commandes</h1>
        <p class="text-muted"><?= $stats['total'] ?> commande<?= $stats['total'] > 1 ? 's' : '' ?> au total</p>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left:4px solid #EF6C00;">
            <div class="stat-value"><?= $stats['nouvelle'] ?></div>
            <div class="text-muted">Nouvelles</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #2E7D32;">
            <div class="stat-value"><?= $stats['confirmé'] ?></div>
            <div class="text-muted">Confirmées</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #C62828;">
            <div class="stat-value"><?= $stats['rejeté'] ?></div>
            <div class="text-muted">Rejetées</div>
        </div>
    </div>
    
    <div class="table-container">
        <?php if(empty($orders)): ?>
            <div class="text-center p-4">
                <i class="fas fa-shopping-cart" style="font-size:4rem; color:var(--primary-pink); opacity:0.3; margin-bottom:1rem;"></i>
                <h3>Aucune commande</h3>
                <p class="text-muted">Les commandes des clients apparaîtront ici.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Articles</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td><strong>#<?= $o['id'] ?></strong></td>
                        <td><?= htmlspecialchars($o['customer_name']) ?></td>
                        <td>
                            <a href="tel:<?= htmlspecialchars($o['customer_phone']) ?>" style="color:var(--primary-pink);">
                                <i class="fas fa-phone"></i> <?= htmlspecialchars($o['customer_phone']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars(substr($o['customer_address'], 0, 30)) . (strlen($o['customer_address']) > 30 ? '...' : '') ?></td>
                        <td>
                            <span class="badge badge-primary"><?= $o['item_count'] ?> article<?= $o['item_count'] > 1 ? 's' : '' ?></span>
                        </td>
                        <td><strong><?= number_format($o['total_price'], 2) ?> DT</strong></td>
                        <td>
                            <?php 
                            $status = $o['status'] ?? 'nouvelle';
                            $statusClass = 'status-' . $status;
                            $statusLabel = ucfirst($status);
                            ?>
                            <span class="<?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                        <td>
                            <a href="/admin/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                        <span>...</span>
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
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
