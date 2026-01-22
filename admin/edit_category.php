<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute(['id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) exit("Catégorie introuvable.");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if (!$name) {
        $error = "Veuillez saisir un nom.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name=:name WHERE id=:id");
            $stmt->execute([
                'name' => $name,
                'id' => $id
            ]);
            $success = "Catégorie modifiée avec succès !";

            // recharger catégorie
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

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
<title>Modifier Catégorie</title>
</head>
<body>

<h2>Modifier catégorie</h2>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post">
    Nom : <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required><br><br>
    <button type="submit">Modifier</button>
</form>

<br>
<a href="categories.php">← Retour</a>

</body>
</html>
