<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php"; // connexion PDO

// Si déjà connecté, rediriger vers dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? ''; // Email ou username
    $password = $_POST['password'] ?? '';

    // Vérifier si admin existe (sans hash)
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :login OR username = :login");
    $stmt->execute(['login' => $login]);
    $admin = $stmt->fetch();

    if ($admin && $password === $admin['password']) {
        // Connexion réussie
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin - Roumayssa Shop</title>
    <style>
        body { font-family: Arial; background: #f8f8f8; display:flex; justify-content:center; align-items:center; height:100vh; }
        .login-box { background:white; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; }
        input { width:100%; padding:10px; margin:5px 0; }
        button { width:100%; padding:10px; background:#e91e63; color:white; border:none; cursor:pointer; }
        .error { color:red; margin:5px 0; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Connexion Admin</h2>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="text" name="login" placeholder="Email ou Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
