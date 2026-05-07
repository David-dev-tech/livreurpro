<?php
// ============================================================
// us_sup.php — Suppression du compte utilisateur
// Supprime l'utilisateur de la BD et détruit toutes les sessions
// ============================================================
session_start();
require_once '../config/config.php';

// Sécurité : l'utilisateur doit être connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.html');
    exit;
}

$id_user = $_SESSION['id_user'];

try {
    // Suppression de l'utilisateur (les livraisons liées peuvent
    // être supprimées en cascade si la FK est configurée en BD,
    // sinon on les supprime manuellement avant)
    $pdo->prepare("DELETE FROM livraison WHERE id_user = ?")->execute([$id_user]);
    $pdo->prepare("DELETE FROM utilisateur WHERE id_user = ?")->execute([$id_user]);

} catch (PDOException $e) {
    error_log("Erreur suppression compte : " . $e->getMessage());
    // En cas d'erreur BD, on redirige sans supprimer la session
    header('Location: ../views/us_profil.php?erreur=suppression');
    exit;
}

// Destruction complète de la session
$_SESSION = [];

// Suppression du cookie de session si présent
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Redirection vers la page d'accueil avec un message
header('Location: ../index.html?compte=supprime');
exit;
?>