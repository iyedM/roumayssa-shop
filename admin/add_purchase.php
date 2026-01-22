<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name   = trim($_POST['product_name'] ?? '');
    $category_id    = intval($_POST['category_id'] ?? 0);
    $quantity       = intval($_POST['quantity'] ?? 0);
    $purchase_price = floatval($_POST['purchase_price'] ?? 0);

    if (!$product_name || !$category_id || !$quantity || !$purchase_price) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }

    if (!$error) {
        try {
            /** 1️⃣ Ajouter l'achat **/
            $stmt = $pdo->prepare("
                INSERT INTO purchases (product_name, category_id, quantity, purchase_price)
                VALUES (:product_name, :category_id, :quantity, :purchase_price)
            ");
            $stmt->execute([
                'product_name'   => $product_name,
                'category_id'    => $category_id,
                'quantity'       => $quantity,
                'purchase_price' => $purchase_price
            ]);

            $purchase_id = $pdo->lastInsertId();

            /** 2️⃣ Upload images **/
            if (!empty($_FILES['images']['name'][0])) {

                $targetDir = __DIR__ . "/../uploads/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                $allowedTypes = ['image/jpeg','image/png','image/gif'];
                $maxSize = 5 * 1024 * 1024;

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {

                    if ($_FILES['images']['error'][$key] === 0) {

                        $fileType = $_FILES['images']['type'][$key];
                        $fileSize = $_FILES['images']['size'][$key];

                        if (!in_array($fileType, $allowedTypes)) continue;
                        if ($fileSize > $maxSize) continue;

                        $originalName = $_FILES['images']['name'][$key];
                        $cleanName = preg_replace('/[^A-Za-z0-9\._-]/', '_', $originalName);
                        $fileName = time().'_'.$key.'_'.$cleanName;

                        $imagePathServer = $targetDir.$fileName;
                        $imagePathWeb    = 'uploads/'.$fileName;

                        if (move_uploaded_file($tmpName, $imagePathServer)) {
                            $pdo->prepare("
                                INSERT INTO purchase_images (purchase_id, image)
                                VALUES (:purchase_id, :image)
                            ")->execute([
                                'purchase_id' => $purchase_id,
                                'image' => $imagePathWeb
                            ]);
                        }
                    }
                }
            }

            $success = "Achat ajouté avec succès !";

        } catch (Exception $e) {
            $error = "Erreur : ".$e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter Achat</title>
</head>
<body>

<h2>Ajouter un achat</h2>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post" enctype="multipart/form-data">

Produit :
<input type="text" name="product_name" required><br><br>

Catégorie :
<select name="category_id" required>
    <option value="">-- Choisir --</option>
    <?php foreach($categories as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
    <?php endforeach; ?>
</select><br><br>

Quantité :
<input type="number" name="quantity" min="1" required><br><br>

Prix d'achat :
<input type="number" step="0.01" name="purchase_price" required><br><br>

Images (factures / photos) :
<input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.gif"><br><br>

<button type="submit">Ajouter</button>
</form>

<br>
<a href="purchases.php">← Retour</a>

</body>
</html>
