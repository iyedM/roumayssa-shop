<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Delete category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check product count
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
    $stmtCheck->execute(['id' => $id]);
    $productCount = $stmtCheck->fetchColumn();
    
    if ($productCount > 0) {
        $error = "Cette catégorie contient $productCount produit(s). Veuillez d'abord supprimer ou déplacer les produits.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $success = "Catégorie supprimée avec succès!";
    }
}

// Get all categories
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     GROUP BY c.id 
                     ORDER BY c.name");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - <?= SITE_NAME ?></title>
    
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
        .action-btns {
            display: flex;
            gap: var(--space-sm);
        }
        .btn-icon {
            padding: var(--space-xs) var(--space-sm);
            font-size: 0.875rem;
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
            <a href="/admin/categories.php" class="navbar-link" style="color:var(--secondary-rose);"><i class="fas fa-tags"></i> Catégories</a>
            <a href="/admin/orders.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="/admin/logout.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container section">
    <div class="flex-between mb-4">
        <div>
            <h1><i class="fas fa-tags"></i> Gestion des catégories</h1>
            <p class="text-muted"><?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?></p>
        </div>
        <a href="/admin/add_category.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une catégorie
        </a>
    </div>
    
    <?php if($error): ?>
        <div class="alert alert-error mb-3">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>
    
    <div class="table-container">
        <?php if(empty($categories)): ?>
            <div class="text-center p-4">
                <i class="fas fa-tags" style="font-size:4rem; color:var(--primary-pink); opacity:0.3; margin-bottom:1rem;"></i>
                <h3>Aucune catégorie</h3>
                <p class="text-muted">Créez votre première catégorie pour organiser vos produits.</p>
                <a href="/admin/add_category.php" class="btn btn-primary mt-2">
                    <i class="fas fa-plus"></i> Créer une catégorie
                </a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Nombre de produits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $c): ?>
                    <tr>
                        <td><strong>#<?= $c['id'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($c['name']) ?></strong>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= $c['product_count'] ?> produit<?= $c['product_count'] > 1 ? 's' : '' ?></span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="/admin/edit_category.php?id=<?= $c['id'] ?>" class="btn btn-secondary btn-icon">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="?delete=<?= $c['id'] ?>" 
                                   class="btn btn-outline btn-icon"
                                   onclick="return confirm('Supprimer cette catégorie ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
