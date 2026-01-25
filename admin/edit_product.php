<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// Get product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header("Location: products.php");
    exit;
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get product images
$stmtImg = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY id");
$stmtImg->execute(['id' => $id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

// Delete image
if (isset($_GET['delete_image'])) {
    $imgId = (int)$_GET['delete_image'];
    
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE id = :id AND product_id = :product_id");
    $stmt->execute(['id' => $imgId, 'product_id' => $id]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($img) {
        // Delete physical file
        if (file_exists("../" . $img['image'])) {
            unlink("../" . $img['image']);
        }
        
        // Delete from database
        $pdo->prepare("DELETE FROM product_images WHERE id = :id")->execute(['id' => $imgId]);
        
        // If deleted image was main image, update product
        if ($product['image'] === $img['image']) {
            $stmtFirst = $pdo->prepare("SELECT image FROM product_images WHERE product_id = :id LIMIT 1");
            $stmtFirst->execute(['id' => $id]);
            $firstImg = $stmtFirst->fetchColumn();
            
            $pdo->prepare("UPDATE products SET image = :img WHERE id = :id")
                ->execute(['img' => $firstImg ?: '', 'id' => $id]);
        }
    }
    
    header("Location: edit_product.php?id=" . $id);
    exit;
}

// Update product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    
    // Handle both dot and comma as decimal separator
    $price_raw = str_replace(',', '.', $_POST['price'] ?? '0');
    $promo_price_raw = !empty($_POST['promo_price']) ? str_replace(',', '.', $_POST['promo_price']) : null;
    $purchase_price_raw = str_replace(',', '.', $_POST['purchase_price'] ?? '0');
    
    $price = floatval($price_raw);
    $promo_price = $promo_price_raw !== null ? floatval($promo_price_raw) : null;
    $purchase_price = floatval($purchase_price_raw);
    
    $stock = intval($_POST['stock']);
    $active = isset($_POST['active']) ? 1 : 0;
    $description = trim($_POST['description']);
    
    try {
        // Update product
        $stmt = $pdo->prepare("
            UPDATE products SET
                name = :name,
                category_id = :category,
                price = :price,
                promo_price = :promo_price,
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
            'promo_price' => $promo_price,
            'purchase_price' => $purchase_price,
            'stock' => $stock,
            'active' => $active,
            'description' => $description,
            'id' => $id
        ]);
        
        // Handle new images
        if (!empty($_FILES['images']['name'][0])) {
            $targetDir = __DIR__ . "/../uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            
            $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
            $maxSize = 5 * 1024 * 1024;
            $firstImage = empty($product['image']);
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === 0) {
                    $fileType = $_FILES['images']['type'][$key];
                    $fileSize = $_FILES['images']['size'][$key];
                    
                    if (!in_array($fileType, $allowedTypes) || $fileSize > $maxSize) continue;
                    
                    $originalName = $_FILES['images']['name'][$key];
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $fileName = uniqid('product_') . '_' . time() . '.' . $extension;
                    
                    $imagePathServer = $targetDir . $fileName;
                    $imagePathWeb = 'uploads/' . $fileName;
                    
                    if (move_uploaded_file($tmpName, $imagePathServer)) {
                        $pdo->prepare("INSERT INTO product_images (product_id, image) VALUES (:product_id, :image)")
                            ->execute(['product_id' => $id, 'image' => $imagePathWeb]);
                        
                        if ($firstImage) {
                            $pdo->prepare("UPDATE products SET image = :img WHERE id = :id")
                                ->execute(['img' => $imagePathWeb, 'id' => $id]);
                            $firstImage = false;
                        }
                    }
                }
            }
        }
        
        $success = "Produit modifié avec succès !";
        
        // Reload product and images
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmtImg->execute(['id' => $id]);
        $images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit - <?= SITE_NAME ?></title>
    
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
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: var(--space-md);
            margin-top: var(--space-md);
        }
        .image-item {
            position: relative;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 2px solid #E0E0E0;
        }
        .image-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .image-item.main {
            border-color: var(--primary-pink);
            border-width: 3px;
        }
        .delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--error-red);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-badge {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: var(--primary-pink);
            color: white;
            padding: 2px 8px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
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
        <h1 class="mb-3"><i class="fas fa-edit"></i> Modifier le produit</h1>
        
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
                       value="<?= htmlspecialchars($product['name']) ?>"
                       required>
            </div>
            
            <!-- Category -->
            <div class="form-group">
                <label class="form-label" for="category">
                    <i class="fas fa-folder"></i> Catégorie <span style="color:var(--error-red);">*</span>
                </label>
                <select id="category" name="category" class="form-control" required>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
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
                           value="<?= $product['purchase_price'] ?>"
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
                           value="<?= $product['price'] ?>"
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
                           value="<?= $product['promo_price'] ?? '' ?>"
                           placeholder="Optionnel">
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
                       value="<?= $product['stock'] ?>"
                       required>
            </div>
            
            <!-- Current Images -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-images"></i> Images actuelles
                </label>
                <?php if(!empty($images)): ?>
                    <div class="image-gallery">
                        <?php foreach($images as $img): ?>
                            <div class="image-item <?= $img['image'] === $product['image'] ? 'main' : '' ?>">
                                <img src="/<?= htmlspecialchars($img['image']) ?>" alt="Product image">
                                <?php if($img['image'] === $product['image']): ?>
                                    <span class="main-badge">Principale</span>
                                <?php endif; ?>
                                <button type="button" 
                                        class="delete-image" 
                                        onclick="if(confirm('Supprimer cette image ?')) window.location.href='?id=<?= $id ?>&delete_image=<?= $img['id'] ?>'">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Aucune image</p>
                <?php endif; ?>
            </div>
            
            <!-- Add New Images -->
            <div class="form-group">
                <label class="form-label" for="images">
                    <i class="fas fa-plus-circle"></i> Ajouter de nouvelles images
                </label>
                <input type="file" 
                       id="images" 
                       name="images[]" 
                       class="form-control" 
                       multiple 
                       accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="text-muted">Formats: JPG, PNG, GIF, WEBP. Max 5MB par image.</small>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="description">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <!-- Active Checkbox -->
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" 
                           name="active" 
                           <?= $product['active'] ? 'checked' : '' ?>
                           style="width:20px; height:20px;">
                    <span><i class="fas fa-eye"></i> Produit actif (visible sur le site)</span>
                </label>
            </div>
            
            <!-- Submit Buttons -->
            <div style="display:flex; gap:var(--space-md); margin-top:var(--space-xl);">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="/admin/products.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <a href="/product_detail.php?id=<?= $id ?>" class="btn btn-outline" target="_blank">
                    <i class="fas fa-eye"></i> Voir
                </a>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
