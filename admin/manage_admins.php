<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";
require_once "../includes/security.php";

// Initialize secure session
initSecureSession();

// Protection: Only logged in admins can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Handle Admin Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $firstName = sanitizeString($_POST['first_name'] ?? '');
        $lastName = sanitizeString($_POST['last_name'] ?? '');
        $username = sanitizeString($_POST['username'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (!$firstName || !$lastName || !$username || !$email || !$password) {
            $error = "Tous les champs sont obligatoires.";
        } elseif (strlen($password) < 6) {
            $error = "Le mot de passe doit faire au moins 6 caractères.";
        } else {
            // Check if username/email already exists
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = :u OR email = :e");
            $stmt->execute(['u' => $username, 'e' => $email]);
            if ($stmt->fetch()) {
                $error = "Le nom d'utilisateur ou l'email est déjà utilisé.";
            } else {
                // Create admin
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO admin (first_name, last_name, username, email, password)
                        VALUES (:fn, :ln, :u, :e, :p)
                    ");
                    $stmt->execute([
                        'fn' => $firstName,
                        'ln' => $lastName,
                        'u' => $username,
                        'e' => $email,
                        'p' => $hashedPassword
                    ]);
                    $success = "Nouvel administrateur créé avec succès !";
                    // Clear post data
                    $_POST = [];
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                }
            }
        }
    }
}

// Get all admins
$admins = $pdo->query("SELECT id, first_name, last_name, username, email, created_at FROM admin ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Admins - <?= SITE_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body { background:var(--neutral-beige); }
        .admin-mgmt-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: var(--space-xl);
            align-items: start;
        }
        @media (max-width: 992px) {
            .admin-mgmt-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include '../templates/admin_navbar.php'; ?>

<div class="container section">
    <h1 class="mb-4"><i class="fas fa-user-shield"></i> Gestion des Administrateurs</h1>

    <?php if($error): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <div class="admin-mgmt-grid">
        <!-- Creation Form -->
        <div class="card">
            <h2 class="mb-3">Créer un admin</h2>
            <form method="POST">
                <?= csrfTokenField() ?>
                <div class="form-row" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 caractères" required>
                </div>
                <button type="submit" name="create_admin" class="btn btn-primary btn-block">
                    <i class="fas fa-plus-circle"></i> Créer l'administrateur
                </button>
            </form>
        </div>

        <!-- Admin List -->
        <div class="card">
            <h2 class="mb-3">Administrateurs existants</h2>
            <div class="table-container" style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--neutral-beige);">
                            <th style="padding:1rem; text-align:left;">Nom</th>
                            <th style="padding:1rem; text-align:left;">Utilisateur</th>
                            <th style="padding:1rem; text-align:left;">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($admins as $admin): ?>
                        <tr style="border-bottom:1px solid var(--neutral-beige);">
                            <td style="padding:1rem;">
                                <strong><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></strong>
                                <?php if($admin['id'] == $_SESSION['admin_id']): ?>
                                    <span class="badge badge-primary" style="font-size:0.6rem; vertical-align:middle;">Vous</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:1rem;"><?= htmlspecialchars($admin['username']) ?></td>
                            <td style="padding:1rem; font-size:0.875rem;"><?= htmlspecialchars($admin['email']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
