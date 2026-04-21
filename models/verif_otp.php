<?php
require_once '../config/config.php';

// Démarrage de la session AVANT tout traitement
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_user = trim($_POST['text'] ?? '');

    if (empty($id_user)) {
        die("Erreur : Le code de vérification est requis.");
    }

    try {
        $stmt = $pdo->prepare("SELECT id_user, mail FROM utilisateur WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        $user = $stmt->fetch();

        if ($user) {
            // ── Création de la session ──────────────────────────
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['mail']    = $user['mail'];
            // ────────────────────────────────────────────────────

            header('Location: ../views/us_catalogue.php');
            exit;
        } else {
            echo "Aucun utilisateur trouvé avec cet ID : " . htmlspecialchars($id_user);
        }

    } catch (PDOException $e) {
        die("Erreur lors de la recherche : " . $e->getMessage());
    }
}
?>