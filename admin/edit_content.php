<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM site_content WHERE id=:id");
$stmt->execute(['id'=>$id]);
$contentData = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contentData) exit("Contenu introuvable.");

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
                UPDATE site_content
                SET section=:section, content=:content, active=:active
                WHERE id=:id
            ");
            $stmt->execute([
                'section' => $section,
                'content' => $content,
                'active' => $active,
                'id' => $id
            ]);
            $success = "Contenu modifié avec succès !";

            // recharger
            $stmt = $pdo->prepare("SELECT * FROM site_content WHERE id=:id");
            $stmt->execute(['id'=>$id]);
            $contentData = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<h2>Modifier un contenu</h2>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post">
    Section : <input type="text" name="section" value="<?= htmlspecialchars($contentData['section']) ?>" required><br><br>
    Contenu : <textarea name="content" rows="6"><?= htmlspecialchars($contentData['content']) ?></textarea><br><br>
    Actif : <input type="checkbox" name="active" <?= $contentData['active'] ? 'checked' : '' ?>><br><br>
    <button type="submit">Modifier</button>
</form>

<a href="site_content.php">← Retour</a>
