<?php
session_start();
require_once "../includes/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $active = intval($_POST['active'] ?? 0);
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET active = :active WHERE id = :id");
            $stmt->execute(['active' => $active, 'id' => $id]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
