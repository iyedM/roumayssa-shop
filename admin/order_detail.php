<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

$success = '';
$error = '';

// Get order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=:id");
$stmt->execute(['id'=>$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Get items
$stmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name, p.id as product_id, p.stock
    FROM order_items oi
    JOIN products p ON p.id=oi.product_id
    WHERE oi.order_id=:id
");
$stmt->execute(['id'=>$id]);
$items = $stmt->fetchAll();

// Calculate product total (without delivery)
$productTotal = 0;
foreach($items as $item) {
    $productTotal += $item['quantity'] * $item['price'];
}

// Calculate delivery fee based on livraison field
$deliveryFee = ($order['livraison'] == 1) ? 7.00 : 0.00;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?= $order['id'] ?> - <?= SITE_NAME ?></title>
    
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
        .order-header {
            background: linear-gradient(135deg, var(--primary-pink), var(--accent-coral));
            color: var(--white);
            padding: var(--space-xl);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-xl);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-md);
        }
        .info-item {
            background: rgba(255,255,255,0.1);
            padding: var(--space-md);
            border-radius: var(--radius-sm);
        }
        .status-actions {
            display: flex;
            gap: var(--space-md);
            margin-top: var(--space-lg);
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
        <a href="/admin/orders.php" class="text-primary">
            <i class="fas fa-arrow-left"></i> Retour aux commandes
        </a>
    </div>
    
    <!-- Order Header -->
    <div class="order-header">
        <div class="flex-between">
            <div>
                <h1 style="color:var(--white);">Commande #<?= $order['id'] ?></h1>
                <p style="opacity:0.9; margin-bottom:0;">
                    <i class="fas fa-calendar"></i> <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?>
                </p>
            </div>
            <div style="text-align:right;">
                <?php 
                $status = $order['status'] ?? 'nouvelle';
                $statusColors = [
                    'nouvelle' => 'background:#FFF3E0; color:#EF6C00;',
                    'confirmé' => 'background:#E8F5E9; color:#2E7D32;',
                    'rejeté' => 'background:#FFEBEE; color:#C62828;'
                ];
                $statusColor = $statusColors[$status] ?? '';
                ?>
                <div style="<?= $statusColor ?> padding:0.75rem 1.5rem; border-radius:50px; font-weight:600; font-size:1.125rem; display:inline-block;">
                    <i class="fas fa-circle" style="font-size:0.5rem;"></i> <?= ucfirst($status) ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="alertContainer"></div>
    
    <div class="grid grid-2" style="gap:2rem; align-items:flex-start;">
        <!-- Customer Info -->
        <div class="card">
            <h2 class="mb-3"><i class="fas fa-user"></i> Informations client</h2>
            
            <div class="info-grid" style="background:var(--neutral-beige); padding:var(--space-lg); border-radius:var(--radius-md);">
                <div>
                    <strong style="display:block; margin-bottom:0.5rem; color:var(--primary-pink);">
                        <i class="fas fa-user-circle"></i> Nom
                    </strong>
                    <?= htmlspecialchars($order['customer_name']) ?>
                </div>
                <div>
                    <strong style="display:block; margin-bottom:0.5rem; color:var(--primary-pink);">
                        <i class="fas fa-phone"></i> Téléphone
                    </strong>
                    <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" style="color:var(--primary-pink);">
                        <?= htmlspecialchars($order['customer_phone']) ?>
                    </a>
                </div>
            </div>
            
            <div style="margin-top:var(--space-md); background:var(--neutral-beige); padding:var(--space-lg); border-radius:var(--radius-md);">
                <strong style="display:block; margin-bottom:0.5rem; color:var(--primary-pink);">
                    <i class="fas fa-map-marker-alt"></i> Adresse de livraison
                </strong>
                <?= nl2br(htmlspecialchars($order['customer_address'])) ?>
            </div>
            
            <!-- Delivery Method -->
            <div style="margin-top:var(--space-md); background:var(--neutral-beige); padding:var(--space-lg); border-radius:var(--radius-md);">
                <strong style="display:block; margin-bottom:0.5rem; color:var(--primary-pink);">
                    <i class="fas fa-truck"></i> Mode de livraison
                </strong>
                <?php if($order['livraison'] == 1): ?>
                    <span class="badge badge-primary">
                        <i class="fas fa-home"></i> Livraison à domicile (+7 DT)
                    </span>
                <?php else: ?>
                    <span class="badge badge-success">
                        <i class="fas fa-store"></i> Retrait en magasin (Gratuit)
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Status Management -->
            <?php if($status === 'nouvelle'): ?>
                <div class="status-actions">
                    <button onclick="updateOrderStatus(<?= $order['id'] ?>, 'confirmé')" class="btn btn-primary" style="flex:1;">
                        <i class="fas fa-check-circle"></i> Accepter la commande
                    </button>
                    <button onclick="updateOrderStatus(<?= $order['id'] ?>, 'rejeté')" class="btn btn-outline" style="flex:1; color:var(--error-red); border-color:var(--error-red);">
                        <i class="fas fa-times-circle"></i> Refuser
                    </button>
                </div>
                <p class="text-muted" style="font-size:0.875rem; margin-top:var(--space-sm); margin-bottom:0;">
                    <i class="fas fa-info-circle"></i> Accepter la commande déduira automatiquement le stock des produits.
                </p>
            <?php elseif($status === 'confirmé'): ?>
                <div class="alert alert-success" style="margin-top:var(--space-lg);">
                    <i class="fas fa-check-circle"></i> Cette commande a été confirmée. Le stock a été déduit.
                </div>
            <?php elseif($status === 'rejeté'): ?>
                <div class="alert alert-error" style="margin-top:var(--space-lg);">
                    <i class="fas fa-times-circle"></i> Cette commande a été rejetée.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Order Items -->
        <div class="card">
            <h2 class="mb-3"><i class="fas fa-box"></i> Articles commandés</h2>
            
            <?php foreach($items as $item): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:var(--space-md); background:var(--neutral-beige); border-radius:var(--radius-sm); margin-bottom:var(--space-sm);">
                    <div style="flex:1;">
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        <br>
                        <small class="text-muted">
                            <?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?> DT
                            <?php if($status === 'nouvelle'): ?>
                                <br><span style="color:var(--success-green);">Stock disponible: <?= $item['stock'] ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div style="text-align:right;">
                        <strong class="text-primary"><?= number_format($item['quantity'] * $item['price'], 2) ?> DT</strong>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Total Breakdown -->
            <div style="margin-top:var(--space-lg); padding-top:var(--space-lg); border-top:2px solid #E0E0E0;">
                <!-- Product Total -->
                <div class="flex-between" style="margin-bottom:var(--space-sm);">
                    <span>Total produits:</span>
                    <strong><?= number_format($productTotal, 2) ?> DT</strong>
                </div>
                
                <!-- Delivery Fee -->
                <div class="flex-between" style="margin-bottom:var(--space-md); padding-bottom:var(--space-md); border-bottom:1px solid #E0E0E0;">
                    <span>Frais de livraison:</span>
                    <strong style="color:<?= $deliveryFee > 0 ? 'var(--primary-pink)' : 'var(--success-green)' ?>;">
                        <?= $deliveryFee > 0 ? '+' : '' ?><?= number_format($deliveryFee, 2) ?> DT
                    </strong>
                </div>
                
                <!-- Final Total -->
                <div class="flex-between" style="font-size:1.5rem;">
                    <strong>Total payé:</strong>
                    <strong class="text-primary"><?= number_format($order['total_price'], 2) ?> DT</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Êtes-vous sûr de vouloir ${newStatus === 'confirmé' ? 'accepter' : 'refuser'} cette commande ?`)) {
        return;
    }
    
    // Show loading state
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = '<div class="alert" style="background:#e3f2fd; color:#1565c0;"><i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...</div>';
    
    // Send AJAX request
    fetch('/admin/order_status_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alertContainer.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alertContainer.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        alertContainer.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Erreur lors de la mise à jour</div>';
    });
}
</script>
</body>
</html>
