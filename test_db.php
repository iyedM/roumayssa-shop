<?php
// test_db.php
// Script de test pour Roumayssa Shop

// ------------------------------
// Connexion à la base de données
// ------------------------------
$host = 'localhost';
$db   = 'roumayssa_shop';
$user = 'iyed';           // Ton utilisateur MySQL
$pass = 'iyed';  // Mot de passe MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<h2>Connexion à la base réussie ✅</h2>";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// ------------------------------
// Fonction pour afficher les données
// ------------------------------
function showTable($pdo, $tableName) {
    echo "<h3>Table: $tableName</h3>";
    $stmt = $pdo->query("SELECT * FROM $tableName");
    $rows = $stmt->fetchAll();
    if (!$rows) {
        echo "Aucune donnée.<br>";
        return;
    }
    echo "<table border='1' cellpadding='5'>";
    // Header
    echo "<tr>";
    foreach (array_keys($rows[0]) as $col) {
        echo "<th>$col</th>";
    }
    echo "</tr>";
    // Rows
    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>$val</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";
}

// ------------------------------
// Afficher toutes les tables de test
// ------------------------------
$tables = ['admin', 'categories', 'products', 'purchases', 'orders', 'order_items', 'site_content'];

foreach ($tables as $t) {
    showTable($pdo, $t);
}

?>
