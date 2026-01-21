<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

/* ===== Produit ===== */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) exit("Produit introuvable");

/* ===== Catégories ===== */
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

/* ===== Images du produit ===== */
$stmtImg = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :id");
$stmtImg->execute(['id' => $id]);
$images = $stmtImg->fetchAll();

$error = '';
$success = '';

/* ===== Suppression image ===== */
if (isset($_GET['delete_image'])) {
    $imgId = (int)$_GET['delete_image'];

    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE id = :id");
    $stmt->execute(['id' => $imgId]);
    $img = $stmt->fetch();

    if ($img) {
        if (file_exists($img['image'])) {
            unlink($img['image']);
        }
        $pdo->prepare("DELETE FROM product_images WHERE id = :id")
            ->execute(['id' => $imgId]);
    }

    header("Location: edit_product.php?id=" . $id);
    exit;
}

/* ===== Mise à jour produit ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $purchase_price = floatval($_POST['purchase_price']);
    $stock = intval($_POST['stock']);
    $active = isset($_POST['active']) ? 1 : 0;
    $description = trim($_POST['description']);

    try {
        // UPDATE produit
        $stmt = $pdo->prepare("
            UPDATE products SET
                name = :name,
                category_id = :category,
                price = :price,
                purchase_price = :purchase_price,
                stock = :stock,
                active = :active,
                description = :description
            WHERE id = :id
        ");

        $stmt->execute([
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'purchase_price' => $purchase_price,
            'stock' => $stock,
            'active' => $active,
            'description' => $description,
            'id' => $id
        ]);

        /* ===== Ajouter nouvelles images ===== */
        if (!empty($_FILES['images']['name'][0])) {
            $targetDir = "../uploads/";

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {

                if ($_FILES['images']['error'][$key] === 0) {
                    $fileName = time() . '_' . $key . '_' . basename($_FILES['images']['name'][$key]);
                    $imagePath = $targetDir . $fileName;

                    if (move_uploaded_file($tmpName, $imagePath)) {
                        $pdo->prepare("
                            INSERT INTO product_images (product_id, image)
                            VALUES (:product_id, :image)
                        ")->execute([
                            'product_id' => $id,
                            'image' => $imagePath
                        ]);
                    }
                }
            }
        }

        $success = "Produit modifié avec succès !";

        // recharger images
        $stmtImg->execute(['id' => $id]);
        $images = $stmtImg->fetchAll();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Produit</title>
<style>
img { border:1px solid #ccc; padding:3px; }
</style>
</head>
<body>

<h2>Modifier produit</h2>

<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post" enctype="multipart/form-data">

Nom:
<input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br><br>

Catégorie:
<select name="category" required>
<?php foreach ($categories as $c): ?>
    <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['name']) ?>
    </option>
<?php endforeach; ?>
</select><br><br>

Prix:
<input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br><br>

Prix d'achat:
<input type="number" step="0.01" name="purchase_price" value="<?= $product['purchase_price'] ?>" required><br><br>

Stock:
<input type="number" name="stock" value="<?= $product['stock'] ?>" required><br><br>

Actif:
<input type="checkbox" name="active" <?= $product['active'] ? 'checked' : '' ?>><br><br>

Description:<br>
<textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea><br><br>

<h3>Images actuelles</h3>
<?php if ($images): ?>
    <?php foreach ($images as $img): ?>
        <div style="display:inline-block;margin:5px">
            <img src="<?= htmlspecialchars($img['image']) ?>" width="100"><br>
            <a href="?id=<?= $id ?>&delete_image=<?= $img['id'] ?>"
               onclick="return confirm('Supprimer cette image ?')">
               Supprimer
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Aucune image</p>
<?php endif; ?>

<br><br>
Ajouter nouvelles images:
<input type="file" name="images[]" multiple><br><br>

<button type="submit">Modifier</button>
</form>

<br>
<a href="products.php">Retour</a>

</body>
</html>
