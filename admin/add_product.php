<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    
    // Handle both dot and comma as decimal separator
    $price_raw = str_replace(',', '.', $_POST['price'] ?? '0');
    $promo_price_raw = !empty($_POST['promo_price']) ? str_replace(',', '.', $_POST['promo_price']) : null;
    $purchase_price_raw = str_replace(',', '.', $_POST['purchase_price'] ?? '0');
    
    $price = floatval($price_raw);
    $promo_price = $promo_price_raw !== null ? floatval($promo_price_raw) : null;
    $purchase_price = floatval($purchase_price_raw);
    
    $stock = intval($_POST['stock'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;
    $description = trim($_POST['description'] ?? '');

    if (!$name || !$category || $price <= 0) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }

if (!$error) {
    try {
        // Insert product
        $stmt = $pdo->prepare("
            INSERT INTO products 
            (name, category_id, price, promo_price, purchase_price, stock, active, description)
            VALUES (:name, :category, :price, :promo_price, :purchase_price, :stock, :active, :description)
        ");
        $stmt->execute([
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'promo_price' => $promo_price,
            'purchase_price' => $purchase_price,
            'stock' => $stock,
            'active' => $active,
            'description' => $description
        ]);

        $product_id = $pdo->lastInsertId();

        // Handle image upload
        $targetDir = __DIR__ . "/../uploads/";
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                throw new Exception("Impossible de créer le dossier uploads");
            }
            chmod($targetDir, 0777); // Ensure permissions are set correctly
        }

        $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp','image/x-webp'];
        $maxSize = 5 * 1024 * 1024;
        $firstImage = true;

        // Vérifier que des fichiers ont été envoyés
        if (isset($_FILES['images']) && !empty($_FILES['images']['tmp_name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $errorCode = $_FILES['images']['error'][$key];
                if ($errorCode !== 0) continue;

                $fileType = $_FILES['images']['type'][$key];
                $fileSize = $_FILES['images']['size'][$key];

                // Vérifier type et taille
                if (!in_array($fileType, $allowedTypes)) continue;
                if ($fileSize > $maxSize) continue;

                // Nettoyer le nom de fichier
                $originalName = $_FILES['images']['name'][$key];
                $cleanName = preg_replace('/[^A-Za-z0-9\._-]/', '_', $originalName);
                $fileName = uniqid('product_') . '_' . $key . '_' . $cleanName;

                $imagePathServer = $targetDir . $fileName;
                $imagePathWeb = 'uploads/' . $fileName;

                // Déplacer le fichier
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

                    // Mettre la première image comme image principale du produit
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
        
        $success = "Produit ajouté avec succès !";
        $_POST = [];

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
    <title>Ajouter Produit - <?= SITE_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body { background:var(--neutral-beige); }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-lg);
        }
        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: var(--space-sm);
            margin-top: var(--space-sm);
        }
        .image-preview img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 2px solid #E0E0E0;
        }
    </style>
</head>
<body>

<?php include '../templates/admin_navbar.php'; ?>

<div class="container section">
    <div class="mb-4">
        <a href="/admin/products.php" class="text-primary">
            <i class="fas fa-arrow-left"></i> Retour aux produits
        </a>
    </div>
    
    <div class="card" style="max-width:900px; margin:0 auto;">
        <h1 class="mb-3"><i class="fas fa-plus-circle"></i> Ajouter un produit</h1>
        
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
        
        <form method="POST" enctype="multipart/form-data">
            <!-- Product Name -->
            <div class="form-group">
                <label class="form-label" for="name">
                    <i class="fas fa-tag"></i> Nom du produit <span style="color:var(--error-red);">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control" 
                       placeholder="Ex: Robe d'été fleurie"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       required>
            </div>
            
            <!-- Category -->
            <div class="form-group">
                <label class="form-label" for="category">
                    <i class="fas fa-folder"></i> Catégorie <span style="color:var(--error-red);">*</span>
                </label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($_POST['category']) && $_POST['category'] == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Prices Row -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="purchase_price">
                        <i class="fas fa-dollar-sign"></i> Prix d'achat (DT) <span style="color:var(--error-red);">*</span>
                    </label>
                    <input type="number" 
                           id="purchase_price" 
                           name="purchase_price" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           value="<?= htmlspecialchars($_POST['purchase_price'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="price">
                        <i class="fas fa-money-bill-wave"></i> Prix de vente (DT) <span style="color:var(--error-red);">*</span>
                    </label>
                    <input type="number" 
                           id="price" 
                           name="price" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="promo_price">
                        <i class="fas fa-percentage"></i> Prix promo (DT)
                    </label>
                    <input type="number" 
                           id="promo_price" 
                           name="promo_price" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           value="<?= htmlspecialchars($_POST['promo_price'] ?? '') ?>"
                           placeholder="Optionnel">
                    <small class="text-muted">Laissez vide si pas de promotion</small>
                </div>
            </div>
            
            <!-- Stock -->
            <div class="form-group">
                <label class="form-label" for="stock">
                    <i class="fas fa-boxes"></i> Stock <span style="color:var(--error-red);">*</span>
                </label>
                <input type="number" 
                       id="stock" 
                       name="stock" 
                       class="form-control" 
                       min="0"
                       value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>"
                       required>
            </div>
            
            <!-- Images -->
            <div class="form-group">
                <label class="form-label" for="images">
                    <i class="fas fa-images"></i> Images du produit
                </label>
                <input type="file" 
                       id="images" 
                       name="images[]" 
                       class="form-control" 
                       multiple 
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       onchange="previewImages(this)">
                <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP. Max 5MB par image. La première image sera l'image principale.</small>
                <div id="imagePreview" class="image-preview"></div>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="description">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          rows="5"
                          placeholder="Décrivez le produit..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <!-- Active Checkbox -->
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" 
                           name="active" 
                           <?= (!isset($_POST['active']) || $_POST['active']) ? 'checked' : '' ?>
                           style="width:20px; height:20px;">
                    <span><i class="fas fa-eye"></i> Produit actif (visible sur le site)</span>
                </label>
            </div>
            
            <!-- Submit Buttons -->
            <div style="display:flex; gap:var(--space-md); margin-top:var(--space-xl);">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Enregistrer le produit
                </button>
                <a href="/admin/products.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                if (index === 0) {
                    img.style.border = '3px solid var(--primary-pink)';
                    img.title = 'Image principale';
                }
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>
</body>
</html>
