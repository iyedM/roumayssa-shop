<?php
session_start();
require_once "includes/db.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    exit("Votre panier est vide.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');

    if (!$customer_name || !$customer_phone || !$customer_address) {
        $error = "Veuillez remplir tous les champs.";
    }

    if (!$error) {
        // Calculer le total
        $total = 0;
        foreach ($cart as $pid => $qty) {
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id=:id");
            $stmt->execute(['id'=>$pid]);
            $price = $stmt->fetchColumn();
            $total += $price * $qty;
        }

        // Créer la commande
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_name, customer_phone, customer_address, total_price)
            VALUES (:name, :phone, :address, :total)
        ");
        $stmt->execute([
            'name' => $customer_name,
            'phone' => $customer_phone,
            'address' => $customer_address,
            'total' => $total
        ]);
        $order_id = $pdo->lastInsertId();

        // Ajouter les items
        foreach ($cart as $pid => $qty) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :qty, :price)
            ");
            $stmt->execute([
                'order_id' => $order_id,
                'product_id' => $pid,
                'qty' => $qty,
                'price' => $pdo->query("SELECT price FROM products WHERE id=$pid")->fetchColumn()
            ]);
        }

        // Vider le panier
        unset($_SESSION['cart']);
        $success = "Commande passée avec succès ! Votre numéro de commande : $order_id";
    }
}
?>

<h2>Passer la commande</h2>

<?php if($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post">
    Nom : <input type="text" name="customer_name" required><br><br>
    Téléphone : <input type="text" name="customer_phone" required><br><br>
    Adresse : <textarea name="customer_address" required></textarea><br><br>
    <button type="submit">Confirmer la commande</button>
</form>
