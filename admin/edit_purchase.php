<?php
session_start();
require_once "../includes/db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM purchases WHERE id=:id");
$stmt->execute(['id'=>$id]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$purchase) exit("Achat introuvable.");

$error = '';
$success = '';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $purchase_price = floatval($_POST['purchase_price'] ?? 0);

    if (!$product_name || !$category_id || !$quantity || !$purchase_price) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE purchases SET
                product_name=:product_name,
                category_id=:category_id,
                quantity=:quantity,
                purchase_price=:purchase_price
            WHERE id=:id
        ");
        $stmt->execute([
            'product_name'=>$product_name,
            'category_id'=>$category_id,
            'quantity'=>$quantity,
            'purchase_price'=>$purchase_price,
            'id'=>$id
        ]);
        $success = "Achat modifié avec succès !";

        // recharger
        $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Achat</title>
</head>
<body>
<h2>Modifier un achat</h2>
<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post">
Produit : <input type="text" name="product_name" value="<?= htmlspecialchars($purchase['product_name']) ?>" required><br><br>

Catégorie :
<select name="category_id" required>
    <option value="">-- Choisir --</option>
    <?php foreach($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $c['id']==$purchase['category_id']?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
        </option>
    <?php endforeach; ?>
</select><br><br>

Quantité : <input type="number" name="quantity" min="1" value="<?= $purchase['quantity'] ?>" required><br><br>
Prix d'achat : <input type="number" step="0.01" name="purchase_price" min="0" value="<?= $purchase['purchase_price'] ?>" required><br><br>

<button type="submit">Modifier</button>
</form>

<br><a href="purchases.php">← Retour</a>
</body>
</html>
