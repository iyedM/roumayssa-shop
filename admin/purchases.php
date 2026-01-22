<?php
session_start();
require_once "../includes/db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// Récupérer tous les achats
$stmt = $pdo->query("
    SELECT pu.*, c.name AS category_name
    FROM purchases pu
    LEFT JOIN categories c ON pu.category_id = c.id
    ORDER BY pu.id DESC
");
$purchases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Achats - Admin</title>
<style>
table { width:100%; border-collapse: collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
a { text-decoration:none; color:#e91e63; }
</style>
</head>
<body>
<h2>Achats</h2>
<a href="add_purchase.php">Ajouter un achat</a><br><br>

<table>
<tr>
    <th>ID</th>
    <th>Produit</th>
    <th>Catégorie</th>
    <th>Quantité</th>
    <th>Image</th>
    <th>Prix d'achat</th>
    <th>Date</th>
    <th>Actions</th>
</tr>
<?php foreach($purchases as $pu): ?>
<tr>
    <td><?= $pu['id'] ?></td>
    <td><?= htmlspecialchars($pu['product_name']) ?></td>
    <td><?= htmlspecialchars($pu['category_name']) ?></td>
    <td><?= $pu['quantity'] ?></td>
    <td>
        <?php
        $stmtImg = $pdo->prepare("
            SELECT image FROM purchase_images WHERE purchase_id = :id LIMIT 1
        ");
        $stmtImg->execute(['id'=>$pu['id']]);
        $img = $stmtImg->fetchColumn();

        if ($img):
        ?>
            <img src="../<?= htmlspecialchars($img) ?>" width="60">
        <?php else: ?>
            <span>—</span>
        <?php endif; ?>
    </td>

    <td><?= number_format($pu['purchase_price'], 2) ?> DT</td>
    <td><?= $pu['created_at'] ?></td>
    <td>
        <a href="edit_purchase.php?id=<?= $pu['id'] ?>">Modifier</a> |
        <a href="?delete=<?= $pu['id'] ?>" onclick="return confirm('Supprimer cet achat ?')">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php
// Supprimer un achat
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM purchases WHERE id=:id")->execute(['id'=>$id]);
    header("Location: purchases.php");
    exit;
}
?>
</body>
</html>
