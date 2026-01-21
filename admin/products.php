<?php
session_start();
require_once "../includes/db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

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
<title>Produits - Admin</title>
<style>
table { width:100%; border-collapse: collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; vertical-align:top; }
a { text-decoration:none; color:#e91e63; }
img { max-width:80px; height:auto; }
</style>
</head>
<body>
<h2>Produits</h2>
<a href="add_product.php">Ajouter un produit</a>
<table>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Catégorie</th>
        <th>Prix</th>
        <th>Prix d'achat</th>
        <th>Stock</th>
        <th>Actif</th>
        <th>Image</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
    <?php foreach($products as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['category_name']) ?></td>
        <td><?= $p['price'] ?></td>
        <td><?= $p['purchase_price'] ?></td>
        <td><?= $p['stock'] ?></td>
        <td><?= $p['active'] ? 'Oui' : 'Non' ?></td>
        <td>
            <?php if($p['image']): ?>
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="Image produit">
            <?php endif; ?>
        </td>
        <td>
            <?php 
                $words = explode(' ', strip_tags($p['description']));
                echo htmlspecialchars(implode(' ', array_slice($words, 0, 3))) . (count($words) > 3 ? '...' : '');
            ?>
        </td>
        <td>
            <a href="edit_product.php?id=<?= $p['id'] ?>">Modifier</a> |
            <a href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Supprimer ce produit ?')">Supprimer</a>
            <a href="../product_detail.php?id=<?= $p['id'] ?>">Voir détails</a>

        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
