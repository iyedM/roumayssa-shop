<?php
// navbar.php
session_start(); // si tu veux utiliser $_SESSION pour le panier ou admin
require_once "includes/db.php";

// Nombre d'articles dans le panier (session)
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Pour afficher les catégories
$categories = $pdo->query("SELECT * FROM categories WHERE active=1 ORDER BY name")->fetchAll();
?>
<nav style="background:#e91e63; padding:10px; color:white;">
    <a href="index.php" style="color:white; text-decoration:none; font-weight:bold;">Roumayssa Shop</a>
    <span style="margin-left:20px;">
        <?php foreach($categories as $cat): ?>
            <a href="category.php?id=<?= $cat['id'] ?>" style="color:white; margin-right:10px;"><?= htmlspecialchars($cat['name']) ?></a>
        <?php endforeach; ?>
    </span>
    <span style="float:right;">
        <a href="cart.php" style="color:white;">Panier (<?= $cartCount ?>)</a>
        <?php if(isset($_SESSION['admin_id'])): ?>
            | <a href="admin/products.php" style="color:white;">Admin</a>
        <?php endif; ?>
    </span>
</nav>
<hr>
