<?php
session_start();
require_once "../includes/db.php";

$stmt = $pdo->query("
    SELECT * FROM orders ORDER BY id DESC
");
$orders = $stmt->fetchAll();
?>

<h2>Commandes</h2>
<a href="cart.php">🛒 Voir le panier</a><br><br>

<table border="1">
<tr><th>ID</th><th>Client</th><th>Tel</th><th>Adresse</th><th>Total</th><th>Status</th><th>Actions</th></tr>
<?php foreach($orders as $o): ?>
<tr>
    <td><?= $o['id'] ?></td>
    <td><?= htmlspecialchars($o['customer_name']) ?></td>
    <td><?= htmlspecialchars($o['customer_phone']) ?></td>
    <td><?= htmlspecialchars($o['customer_address']) ?></td>
    <td><?= $o['total_price'] ?> DT</td>
    <td><?= $o['status'] ?></td>
    <td><a href="order_detail.php?id=<?= $o['id'] ?>">Voir</a></td>
</tr>
<?php endforeach; ?>
</table>
