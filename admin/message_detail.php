<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// Get message
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = :id");
$stmt->execute(['id' => $id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    header("Location: messages.php");
    exit;
}

// Mark as read
if ($message['lu'] == 0) {
    $pdo->prepare("UPDATE contact_messages SET lu = 1 WHERE id = :id")->execute(['id' => $id]);
    $message['lu'] = 1;
}

// Handle toggle read status
if (isset($_GET['toggle_read'])) {
    $newStatus = $message['lu'] == 1 ? 0 : 1;
    $pdo->prepare("UPDATE contact_messages SET lu = :status WHERE id = :id")->execute([
        'status' => $newStatus,
        'id' => $id
    ]);
    header("Location: message_detail.php?id=" . $id);
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM contact_messages WHERE id = :id")->execute(['id' => $id]);
    header("Location: messages.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message de <?= htmlspecialchars($message['full_name']) ?> - <?= SITE_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body { background:var(--neutral-beige); }
        .admin-navbar {
            background: var(--dark-text);
            color: var(--white);
            padding: var(--space-md) 0;
            box-shadow: var(--shadow-md);
        }
        .message-header {
            background: linear-gradient(135deg, var(--primary-pink), var(--accent-coral));
            color: white;
            padding: var(--space-xl);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
        }
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }
        .info-card {
            background: var(--white);
            padding: var(--space-md);
            border-radius: var(--radius-sm);
            border-left: 4px solid var(--primary-pink);
        }
    </style>
</head>
<body>

<nav class="admin-navbar">
    <div class="container navbar-container">
        <a href="/admin/dashboard.php" style="color:var(--white); font-weight:600; font-size:1.25rem;">
            <i class="fas fa-shield-alt"></i> Administration
        </a>
        <div class="navbar-menu">
            <a href="/" class="navbar-link" style="color:var(--white);"><i class="fas fa-home"></i> Site</a>
            <a href="/admin/products.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-box"></i> Produits</a>
            <a href="/admin/categories.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-tags"></i> Catégories</a>
            <a href="/admin/orders.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="/admin/messages.php" class="navbar-link" style="color:var(--secondary-rose);"><i class="fas fa-envelope"></i> Messages</a>
            <a href="/admin/logout.php" class="navbar-link" style="color:var(--white);"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container section">
    <div class="mb-4">
        <a href="/admin/messages.php" class="text-primary">
            <i class="fas fa-arrow-left"></i> Retour aux messages
        </a>
    </div>
    
    <div class="message-header">
        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
            <div>
                <h1 style="color:white; margin-bottom:0.5rem;">
                    <i class="fas fa-envelope"></i> Message de <?= htmlspecialchars($message['full_name']) ?>
                </h1>
                <p style="opacity:0.9; margin:0;">
                    <i class="fas fa-clock"></i> Reçu le <?= date('d/m/Y à H:i', strtotime($message['created_at'])) ?>
                </p>
            </div>
            <span class="badge <?= $message['lu'] == 1 ? 'badge-success' : 'badge-warning' ?>" style="font-size:1rem;">
                <?= $message['lu'] == 1 ? 'Lu' : 'Non lu' ?>
            </span>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="contact-info">
        <div class="info-card">
            <div style="display:flex; align-items:center; gap:1rem;">
                <i class="fas fa-user" style="font-size:1.5rem; color:var(--primary-pink);"></i>
                <div>
                    <small class="text-muted">Nom complet</small>
                    <p style="margin:0; font-weight:600;"><?= htmlspecialchars($message['full_name']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div style="display:flex; align-items:center; gap:1rem;">
                <i class="fas fa-envelope" style="font-size:1.5rem; color:var(--primary-pink);"></i>
                <div>
                    <small class="text-muted">Email</small>
                    <p style="margin:0; font-weight:600;">
                        <a href="mailto:<?= htmlspecialchars($message['email']) ?>" style="color:var(--primary-pink);">
                            <?= htmlspecialchars($message['email']) ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <?php if($message['phone']): ?>
        <div class="info-card">
            <div style="display:flex; align-items:center; gap:1rem;">
                <i class="fas fa-phone" style="font-size:1.5rem; color:var(--primary-pink);"></i>
                <div>
                    <small class="text-muted">Téléphone</small>
                    <p style="margin:0; font-weight:600;">
                        <a href="tel:<?= htmlspecialchars($message['phone']) ?>" style="color:var(--primary-pink);">
                            <?= htmlspecialchars($message['phone']) ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Message Content -->
    <div class="card">
        <h2 class="mb-3"><i class="fas fa-comment-alt"></i> Message</h2>
        <div style="background:var(--neutral-beige); padding:var(--space-lg); border-radius:var(--radius-sm); line-height:1.8;">
            <?= nl2br(htmlspecialchars($message['message'])) ?>
        </div>
    </div>
    
    <!-- Actions -->
    <div style="display:flex; gap:var(--space-md); margin-top:var(--space-lg); flex-wrap:wrap;">
        <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: Votre message&body=Bonjour <?= htmlspecialchars($message['full_name']) ?>,%0D%0A%0D%0A" 
           class="btn btn-primary" style="flex:1;">
            <i class="fas fa-reply"></i> Répondre par email
        </a>
        
        <?php if($message['phone']): ?>
        <a href="tel:<?= htmlspecialchars($message['phone']) ?>" 
           class="btn btn-secondary" style="flex:1;">
            <i class="fas fa-phone"></i> Appeler
        </a>
        <?php endif; ?>
        
        <a href="?id=<?= $id ?>&toggle_read=1" 
           class="btn btn-outline">
            <i class="fas fa-<?= $message['lu'] == 1 ? 'envelope' : 'envelope-open' ?>"></i> 
            Marquer comme <?= $message['lu'] == 1 ? 'non lu' : 'lu' ?>
        </a>
        
        <a href="?id=<?= $id ?>&delete=1" 
           class="btn btn-outline" 
           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')"
           style="color:var(--error-red); border-color:var(--error-red);">
            <i class="fas fa-trash"></i> Supprimer
        </a>
    </div>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
