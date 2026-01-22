<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Supprimer un contenu
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM site_content WHERE id=:id");
    $stmt->execute(['id'=>$id]);
    $success = "Contenu supprimé avec succès !";
}

// Récupérer tous les contenus
$contents = $pdo->query("SELECT * FROM site_content ORDER BY id DESC")->fetchAll();
?>

<h2>Gestion des contenus du site</h2>
<a href="add_content.php">Ajouter un contenu</a><br><br>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Section</th>
    <th>Détails</th>
    <th>Actif</th>
    <th>Actions</th>
</tr>
<?php foreach($contents as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= htmlspecialchars($c['section']) ?></td>
    <td><?= nl2br(htmlspecialchars($c['content'])) ?></td>
    <td><?= $c['active'] ? 'Oui' : 'Non' ?></td>
    <td>
        <a href="edit_content.php?id=<?= $c['id'] ?>">Modifier</a> |
        <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Supprimer ce contenu ?')">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
