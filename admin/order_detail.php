<?php
session_start();
require_once "../includes/db.php";

$id = intval($_GET['id'] ?? 0);

// Commande
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=:id");
$stmt->execute(['id'=>$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Items
$stmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON p.id=oi.product_id
    WHERE oi.order_id=:id
");
$stmt->execute(['id'=>$id]);
$items = $stmt->fetchAll();
?>

<h2>Détails commande #<?= $order['id'] ?></h2>
Client: <?= htmlspecialchars($order['customer_name']) ?><br>
Téléphone: <?= htmlspecialchars($order['customer_phone']) ?><br>
Adresse: <?= htmlspecialchars($order['customer_address']) ?><br>
Total: <?= $order['total_price'] ?> DT<br>
Status: <?= $order['status'] ?><br><br>

<table border="1">
<tr><th>Produit</th><th>Qté</th><th>Prix</th></tr>
<?php foreach($items as $i): ?>
<tr>
    <td><?= htmlspecialchars($i['name']) ?></td>
    <td><?= $i['quantity'] ?></td>
    <td><?= $i['price'] ?> DT</td>
</tr>
<?php endforeach; ?>
</table>

<br><a href="orders.php">← Retour</a>
