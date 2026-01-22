<?php
session_start();
require_once "includes/db.php";

$product_id = (int)($_POST['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    header("Location: product_detail.php?id=$product_id");
    exit;
}

// Initialiser le panier si inexistant
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ajouter ou mettre à jour quantité
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

header("Location: cart.php");
exit;
