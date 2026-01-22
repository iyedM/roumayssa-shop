<?php
session_start();
require_once "../includes/db.php";

// Vérifier connexion admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer stats simples
$stmt1 = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt1->fetchColumn();

$stmt2 = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt2->fetchColumn();

$stmt3 = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $stmt3->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Roumayssa Shop</title>
    <style>
        body { font-family: Arial; margin:20px; background:#f4f4f4; }
        .stats { display:flex; gap:20px; margin-top:20px; }
        .card { background:#fff; padding:20px; border-radius:8px; flex:1; text-align:center; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
        ul { list-style:none; padding:0; }
        li { margin:5px 0; }
        a { text-decoration:none; color:#e91e63; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<?php include 'templates/admin_navbar.php'; ?>

<div style="padding:20px;">
    <h2>Bonjour, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>

    <div class="stats">
        <div class="card">
            <h3>Produits</h3>
            <p><?php echo $totalProducts; ?></p>
        </div>
        <div class="card">
            <h3>Commandes</h3>
            <p><?php echo $totalOrders; ?></p>
        </div>
        <div class="card">
            <h3>Catégories</h3>
            <p><?php echo $totalCategories; ?></p>
        </div>
    </div>

    <h3>Actions rapides</h3>
    <ul>
        <li><a href="products.php">Gérer les produits</a></li>
        <li><a href="categories.php">Gérer les catégories</a></li>
        <li><a href="orders.php">Voir les commandes</a></li>
        <li><a href="purchases.php">Gérer les achats</a></li>
        <li><a href="site_content.php">Gérer le contenu site</a></li>
    </ul>
</div>

<?php include 'templates/admin_footer.php'; ?>

</body>
</html>
