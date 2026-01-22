<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<style>
.admin-navbar {
    background: #2c3e50;
    color: white;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.admin-navbar a {
    color: white;
    text-decoration: none;
    margin-right: 15px;
}
.admin-navbar a:hover {
    text-decoration: underline;
}
.admin-navbar .links {
    display: flex;
    align-items: center;
}
.admin-navbar .user {
    font-weight: bold;
}
</style>

<div style="background:#e91e63; padding:10px; color:white; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <strong>Roumayssa Shop - Admin</strong>
    </div>
    <div>
        <a href="dashboard.php" style="color:white; margin-right:15px;">Dashboard</a>
        <a href="products.php" style="color:white; margin-right:15px;">Produits</a>
        <a href="categories.php" style="color:white; margin-right:15px;">Catégories</a>
        <a href="orders.php" style="color:white; margin-right:15px;">Commandes</a>
        <a href="purchases.php" style="color:white; margin-right:15px;">Achats</a>
        <a href="site_content.php" style="color:white; margin-right:15px;">Contenu site</a>
        <a href="logout.php" style="color:white;">Déconnexion</a>
    </div>
</div>

