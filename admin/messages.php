<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$whereClause = "";
if ($filter === 'unread') {
    $whereClause = "WHERE lu = 0";
} elseif ($filter === 'read') {
    $whereClause = "WHERE lu = 1";
}

// Count total messages
$totalMessages = $pdo->query("SELECT COUNT(*) FROM contact_messages $whereClause")->fetchColumn();
$totalPages = ceil($totalMessages / $perPage);

// Get messages
$stmt = $pdo->query("
    SELECT * FROM contact_messages 
    $whereClause
    ORDER BY created_at DESC 
    LIMIT $perPage OFFSET $offset
");
$messages = $stmt->fetchAll();

// Count unread messages
$unreadCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE lu = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?= SITE_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body { background:var(--neutral-beige); }
        .message-row {
            background: var(--white);
            padding: var(--space-md);
            border-radius: var(--radius-sm);
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            transition: all var(--transition-normal);
            cursor: pointer;
            border-left: 4px solid transparent;
        }
        .message-row:hover {
            box-shadow: var(--shadow-md);
            transform: translateX(4px);
        }
        .message-row.unread {
            background: #FFF5F7;
            border-left-color: var(--primary-pink);
            font-weight: 600;
        }
        .message-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-pink);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .message-content {
            flex: 1;
        }
        .message-meta {
            display: flex;
            gap: var(--space-md);
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .filter-tabs {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-sm);
            background: var(--white);
            border: 2px solid #E0E0E0;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
            color: var(--dark-text);
        }
        .filter-tab:hover {
            border-color: var(--primary-pink);
        }
        .filter-tab.active {
            background: var(--primary-pink);
            color: white;
            border-color: var(--primary-pink);
        }
    </style>
</head>
<body>

<?php include '../templates/admin_navbar.php'; ?>

<div class="container section">
    <div class="flex-between mb-4">
        <div>
            <h1><i class="fas fa-envelope"></i> Messages de contact</h1>
            <p class="text-muted"><?= $totalMessages ?> message<?= $totalMessages > 1 ? 's' : '' ?> au total</p>
        </div>
        <?php if($unreadCount > 0): ?>
            <span class="badge badge-danger" style="font-size:1.25rem; padding:0.5rem 1rem;">
                <?= $unreadCount ?> non lu<?= $unreadCount > 1 ? 's' : '' ?>
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
            <i class="fas fa-inbox"></i> Tous (<?= $totalMessages ?>)
        </a>
        <a href="?filter=unread" class="filter-tab <?= $filter === 'unread' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Non lus (<?= $unreadCount ?>)
        </a>
        <a href="?filter=read" class="filter-tab <?= $filter === 'read' ? 'active' : '' ?>">
            <i class="fas fa-envelope-open"></i> Lus (<?= $totalMessages - $unreadCount ?>)
        </a>
    </div>
    
    <?php if(empty($messages)): ?>
        <div class="card text-center p-4">
            <i class="fas fa-inbox" style="font-size:4rem; color:var(--primary-pink); opacity:0.3; margin-bottom:1rem;"></i>
            <h3>Aucun message</h3>
            <p class="text-muted">Vous n'avez pas encore reçu de messages de contact.</p>
        </div>
    <?php else: ?>
        <div class="card">
            <?php foreach($messages as $msg): ?>
                <div class="message-row <?= $msg['lu'] == 0 ? 'unread' : '' ?>" 
                     onclick="window.location.href='/admin/message_detail.php?id=<?= $msg['id'] ?>'">
                    <div class="message-icon">
                        <i class="fas fa-<?= $msg['lu'] == 0 ? 'envelope' : 'envelope-open' ?>"></i>
                    </div>
                    <div class="message-content">
                        <strong><?= htmlspecialchars($msg['full_name']) ?></strong>
                        <div class="message-meta">
                            <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($msg['email']) ?></span>
                            <?php if($msg['phone']): ?>
                                <span><i class="fas fa-phone"></i> <?= htmlspecialchars($msg['phone']) ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <p class="text-muted" style="margin-top:0.5rem; margin-bottom:0;">
                            <?php 
                            $preview = substr($msg['message'], 0, 100);
                            echo htmlspecialchars($preview) . (strlen($msg['message']) > 100 ? '...' : '');
                            ?>
                        </p>
                    </div>
                    <i class="fas fa-chevron-right" style="color:var(--primary-pink);"></i>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
            <div style="display:flex; justify-content:center; gap:0.5rem; margin-top:var(--space-xl); flex-wrap:wrap;">
                <?php if($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="btn btn-primary btn-sm"><?= $i ?></span>
                    <?php elseif($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                        <a href="?page=<?= $i ?>&filter=<?= $filter ?>" class="btn btn-outline btn-sm"><?= $i ?></a>
                    <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                        <span class="btn btn-outline btn-sm" disabled>...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>" class="btn btn-outline btn-sm">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
