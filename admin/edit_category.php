<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute(['id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) {
    header("Location: categories.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if (!$name) {
        $error = "Veuillez saisir un nom.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name=:name WHERE id=:id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            $success = "Catégorie modifiée avec succès !";
            
            // Reload category
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Catégorie - <?= SITE_NAME ?></title>
    
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
    <div class="mb-4">
        <a href="/admin/categories.php" class="text-primary">
            <i class="fas fa-arrow-left"></i> Retour aux catégories
        </a>
    </div>
    
    <div class="card" style="max-width:600px;">
        <h1 class="mb-3"><i class="fas fa-edit"></i> Modifier la catégorie</h1>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="name">
                    <i class="fas fa-tag"></i> Nom de la catégorie <span style="color:var(--error-red);">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control" 
                       value="<?= htmlspecialchars($category['name']) ?>"
                       required
                       autofocus>
            </div>
            
            <div style="display:flex; gap:var(--space-md);">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="/admin/categories.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
