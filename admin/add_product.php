<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $purchase_price = floatval($_POST['purchase_price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;
    $description = trim($_POST['description'] ?? '');

    if (!$name || !$category) {
        $error = "Veuillez remplir les champs obligatoires.";
    }

    if (!$error) {
        try {
            // Ajouter le produit
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, category_id, price, purchase_price, stock, active, description)
                VALUES (:name, :category, :price, :purchase_price, :stock, :active, :description)
            ");
            $stmt->execute([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'purchase_price' => $purchase_price,
                'stock' => $stock,
                'active' => $active,
                'description' => $description
            ]);

            $product_id = $pdo->lastInsertId();

            // Upload des images
            if (!empty($_FILES['images']['name'][0])) {
                $targetDir = __DIR__ . "/../uploads/"; // chemin serveur
                $firstImage = true;

                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['images']['error'][$key] === 0) {

                        // Nettoyer le nom de fichier
                        $originalName = $_FILES['images']['name'][$key];
                        $cleanName = preg_replace('/[^A-Za-z0-9\._-]/', '_', $originalName);
                        $fileName = time() . '_' . $key . '_' . $cleanName;

                        $imagePathServer = $targetDir . $fileName; // pour move_uploaded_file
                        $imagePathWeb = 'uploads/' . $fileName;    // pour le navigateur

                        if (move_uploaded_file($tmpName, $imagePathServer)) {
                            // Insérer dans product_images
                            $stmtImg = $pdo->prepare("
                                INSERT INTO product_images (product_id, image)
                                VALUES (:product_id, :image)
                            ");
                            $stmtImg->execute([
                                'product_id' => $product_id,
                                'image' => $imagePathWeb
                            ]);

                            // Première image comme principale
                            if ($firstImage) {
                                $stmtUpdate = $pdo->prepare("UPDATE products SET image=:img WHERE id=:id");
                                $stmtUpdate->execute([
                                    'img' => $imagePathWeb,
                                    'id' => $product_id
                                ]);
                                $firstImage = false;
                            }
                        }
                    }
                }
            }

            $success = "Produit ajouté avec succès !";

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
<title>Ajouter Produit</title>
</head>
<body>

<h2>Ajouter un produit</h2>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post" enctype="multipart/form-data">
    Nom : <input type="text" name="name" required><br><br>

    Catégorie :
    <select name="category" required>
        <option value="">-- Choisir --</option>
        <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    Prix d'achat : <input type="number" step="0.01" name="purchase_price" required><br><br>
    Prix de vente : <input type="number" step="0.01" name="price" required><br><br>
    Stock : <input type="number" name="stock" required><br><br>
    Actif : <input type="checkbox" name="active" checked><br><br>

    Images : <input type="file" name="images[]" multiple><br><br>
    Description : <textarea name="description" rows="4"></textarea><br><br>

    <button type="submit">Ajouter</button>
</form>

<br>
<a href="products.php">Retour</a>
</body>
</html>
