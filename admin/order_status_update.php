<?php
// order_status_update.php - AJAX endpoint for updating order status
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Unauthorized');
}

$orderId = (int)$_POST['order_id'] ?? 0;
$newStatus = $_POST['status'] ?? '';

if (!$orderId || !in_array($newStatus, ['nouvelle', 'confirmé', 'rejeté'])) {
    http_response_code(400);
    exit('Invalid parameters');
}

try {
    // Get order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        exit('Order not found');
    }
    
    // If confirming order, deduct stock
    if ($newStatus === 'confirmé' && $order['status'] !== 'confirmé') {
        // Get order items
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :id");
        $stmt->execute(['id' => $orderId]);
        $items = $stmt->fetchAll();
        
        // Deduct stock for each item
        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET stock = stock - :quantity 
                WHERE id = :product_id AND stock >= :quantity
            ");
            $result = $stmt->execute([
                'quantity' => $item['quantity'],
                'product_id' => $item['product_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                // Stock insufficient or product not found
                throw new Exception("Stock insuffisant pour le produit ID: " . $item['product_id']);
            }
        }
    }
    
    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $newStatus, 'id' => $orderId]);
    
    echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
