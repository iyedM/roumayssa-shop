<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// ===== Supprimer catégorie =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Vérifier combien de produits appartiennent à cette catégorie
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
    $stmtCheck->execute(['id' => $id]);
    $productCount = $stmtCheck->fetchColumn();

    if ($productCount > 0) {
        // Avertissement si produits présents
        $error = "Attention : cette catégorie contient $productCount produit(s). La suppression supprimera également tous les produits liés.";
        // Lien pour confirmer la suppression
        echo "<p style='color:red'>$error</p>";
        echo "<a href='?delete_confirmed=$id' style='color:red;'>Supprimer quand même</a><br><br>";
    } else {
        // Pas de produits : suppression directe
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $success = "Catégorie supprimée avec succès !";
    }
}

// ===== Suppression confirmée =====
if (isset($_GET['delete_confirmed'])) {
    $id = (int)$_GET['delete_confirmed'];

    // Supprimer les produits liés
    $stmtProducts = $pdo->prepare("DELETE FROM products WHERE category_id = :id");
    $stmtProducts->execute(['id' => $id]);

    // Supprimer la catégorie
    $stmtCat = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmtCat->execute(['id' => $id]);

    $success = "Catégorie et tous les produits liés ont été supprimés avec succès !";
}

// ===== Récupérer toutes les catégories =====
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Catégories - Admin</title>
<style>
table { width:100%; border-collapse: collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
a { text-decoration:none; color:#e91e63; }
</style>
</head>
<body>

<h2>Catégories</h2>
<a href="add_category.php">Ajouter une catégorie</a><br><br>

<?php if(!empty($error) && !isset($_GET['delete_confirmed'])): ?>
    <p style="color:red"><?= $error ?></p>
<?php endif; ?>
<?php if(!empty($success)): ?>
    <p style="color:green"><?= $success ?></p>
<?php endif; ?>

<table>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Actions</th>
</tr>
<?php foreach($categories as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= htmlspecialchars($c['name']) ?></td>
    <td>
        <a href="edit_category.php?id=<?= $c['id'] ?>">Modifier</a> |
        <a href="?delete=<?= $c['id'] ?>" 
           onclick="return confirm('Supprimer cette catégorie ?')">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
