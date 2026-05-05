<?php
// config.php
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'livpro');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// try {
//     $pdo = new PDO(
//         "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
//         DB_USER,
//         DB_PASS,
//         [
//             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::ATTR_EMULATE_PREPARES => false,
//         ]
//     );
// } catch (PDOException $e) {
//     die("Erreur de connexion à la base de données : " . $e->getMessage());
// }



$host = "sqlXXX.infinityfree.com";
$dbname = "if0_41836883_livpro";
$user = "if0_41836883";
$password = "Compte02";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

?>