<?php
session_start();
require_once "includes/db.php";

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<h2>Votre Panier</h2>

<?php if(empty($cart)): ?>
    <p>Votre panier est vide.</p>
<?php else: ?>
<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Produit</th>
    <th>Quantité</th>
    <th>Prix unitaire</th>
    <th>Sous-total</th>
    <th>Action</th>
</tr>
<?php foreach($cart as $pid => $qty):
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=:id");
    $stmt->execute(['id'=>$pid]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    $subtotal = $prod['price'] * $qty;
    $total += $subtotal;
?>
<tr>
    <td><?= htmlspecialchars($prod['name']) ?></td>
    <td><?= $qty ?></td>
    <td><?= number_format($prod['price'],2) ?> DT</td>
    <td><?= number_format($subtotal,2) ?> DT</td>
    <td><a href="cart_remove.php?id=<?= $pid ?>">Supprimer</a></td>
</tr>
<?php endforeach; ?>
<tr>
    <td colspan="3">Total</td>
    <td><?= number_format($total,2) ?> DT</td>
    <td></td>
</tr>
</table>
<?php endif; ?>
<br>
<a href="admin/products.php">Continuer vos achats</a>
<a href="checkout.php"><button>Passer la commande</button></a>
