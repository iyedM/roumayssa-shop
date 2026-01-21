<?php
require_once "includes/db.php";

// Récupérer l'ID depuis l'URL
$id = $_GET['id'] ?? 0;
if (!$id) exit("Produit invalide");

// ===== Produit =====
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = :id
");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) exit("Produit introuvable");

// ===== Images =====
// Récupérer toutes les images liées au produit
$stmtImg = $pdo->prepare("
    SELECT image FROM product_images WHERE product_id = :id
");
$stmtImg->execute(['id' => $id]);
$imagesDb = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

// Si aucune image dans product_images, utiliser l'image principale du produit
$images = [];
if (!empty($imagesDb)) {
    foreach ($imagesDb as $img) {
        // Rendre le chemin accessible depuis le navigateur
        $images[] = 'uploads/' . basename($img['image']);
    }
} elseif (!empty($product['image'])) {
    $images[] = 'uploads/' . basename($product['image']);
} else {
    $images[] = 'assets/images/no-image.png';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?> - Roumayssa Shop</title>
<style>
body { font-family: Arial; background:#fafafa; margin:0; padding:0; }
.container { max-width:900px; margin:20px auto; background:white; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
.gallery { display:flex; gap:10px; margin-top:10px; flex-wrap: wrap; }
.gallery img { width:100px; cursor:pointer; border:1px solid #ddd; transition:0.2s; }
.gallery img:hover { border-color:#e91e63; }
.main-image img { width:100%; max-height:400px; object-fit:contain; border:1px solid #ddd; }
.price { color:#e91e63; font-size:22px; margin-top:10px; }
.stock { color:green; font-weight:bold; }
.out { color:red; font-weight:bold; }
.description { margin-top:20px; white-space:pre-wrap; }
.back-link { display:inline-block; margin-top:20px; text-decoration:none; color:#e91e63; }
</style>

<script>
function changeImage(src){
    document.getElementById("mainImg").src = src;
}
</script>

</head>
<body>

<div class="container">

<h2><?= htmlspecialchars($product['name']) ?></h2>
<p>Catégorie : <?= htmlspecialchars($product['category_name']) ?></p>

<div class="main-image">
    <img id="mainImg" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
</div>

<?php if (count($images) > 1): ?>
<div class="gallery">
    <?php foreach ($images as $img): ?>
        <img src="<?= htmlspecialchars($img) ?>" onclick="changeImage(this.src)" alt="Image produit">
    <?php endforeach; ?>
</div>
<?php endif; ?>

<p class="price">Prix : <?= number_format($product['price'], 2) ?> DT</p>

<p>
    <?php if ($product['stock'] > 0): ?>
        <span class="stock">En stock</span>
    <?php else: ?>
        <span class="out">Rupture de stock</span>
    <?php endif; ?>
</p>

<?php if ($product['description']): ?>
<h3>Description</h3>
<p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
<?php endif; ?>

<a class="back-link" href="admin/products.php">← Retour boutique</a>

</div>

</body>
</html>
