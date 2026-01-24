<?php
session_start();
require_once "../includes/db.php";
require_once "../config/config.php";
require_once "../includes/security.php";

// Initialize secure session
initSecureSession();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $login = sanitizeString($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate inputs
        if (!validateRequired($login) || !validateRequired($password)) {
            $error = "Tous les champs sont requis.";
        } else {
            // Check if admin exists
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :login OR username = :login");
            $stmt->execute(['login' => $login]);
            $admin = $stmt->fetch();
            
            // Verify password using password_verify
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful - regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['last_activity'] = time();
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - <?= SITE_NAME ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--secondary-rose), var(--primary-pink));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-lg);
        }
        
        .login-container {
            background: var(--white);
            max-width: 450px;
            width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--space-2xl);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-pink), var(--accent-coral));
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto var(--space-lg);
        }
        
        .password-toggle {
            position: absolute;
            right: var(--space-md);
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color var(--transition-fast);
        }
        
        .password-toggle:hover {
            color: var(--primary-pink);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1 style="margin-bottom:0.5rem;">Administration</h1>
            <p class="text-muted"><?= SITE_NAME ?></p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
       <form method="POST" action="login.php">
            <?= csrfTokenField() ?>
            <div class="form-group">
                <label class="form-label" for="login">
                    <i class="fas fa-user"></i> Email ou Nom d'utilisateur
                </label>
                <input type="text" 
                       id="login" 
                       name="login" 
                       class="form-control" 
                       placeholder="admin@example.com"
                       required
                       autofocus>
            </div>
            
            <div class="form-group" style="position:relative;">
                <label class="form-label" for="password">
                    <i class="fas fa-lock"></i> Mot de passe
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control" 
                       placeholder="••••••••"
                       required>
                <span class="password-toggle" onclick="togglePassword()">
                    <i id="toggleIcon" class="fas fa-eye"></i>
                </span>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
        
        <div style="text-align:center; margin-top:var(--space-xl); padding-top:var(--space-xl); border-top:1px solid #E0E0E0;">
            <a href="/" style="color:var(--primary-pink);">
                <i class="fas fa-arrow-left"></i> Retour au site
            </a>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
