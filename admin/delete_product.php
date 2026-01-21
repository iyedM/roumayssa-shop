<?php
session_start();
require_once "../includes/db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("DELETE FROM products WHERE id=:id");
$stmt->execute(['id'=>$id]);

header("Location: products.php");
exit;
