<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = trim($_POST['section'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;

    if (!$section) {
        $error = "Le nom de la section est obligatoire.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO site_content (section, content, active)
                VALUES (:section, :content, :active)
            ");
            $stmt->execute([
                'section' => $section,
                'content' => $content,
                'active' => $active
            ]);
            $success = "Contenu ajouté avec succès !";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<h2>Ajouter un contenu</h2>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post">
    Section : <input type="text" name="section" required><br><br>
    Contenu : <textarea name="content" rows="6"></textarea><br><br>
    Actif : <input type="checkbox" name="active" checked><br><br>
    <button type="submit">Ajouter</button>
</form>

<a href="site_content.php">← Retour</a>
