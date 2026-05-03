<?php
// ============================================================
// traiter_livraison.php
// ============================================================
session_start();

// Bufferiser la sortie : évite qu'un warning PHP ne corrompe
// le JSON et provoque une erreur réseau côté fetch()
ob_start();

header('Content-Type: application/json');

require_once '../config/config.php';

// ─────────────────────────────────────────────────────────────
// Utilitaire : retourne une erreur JSON complète et stoppe
// ─────────────────────────────────────────────────────────────
function jsonError(string $msg): void {
    ob_clean(); // vider les éventuels warnings PHP avant le JSON
    echo json_encode([
        'success'       => false,
        'message'       => $msg,
        // Informations de débogage visibles dans la modale
        'debug_post'    => $_POST,
        'debug_session' => [
            'id_user' => $_SESSION['id_user'] ?? '⚠️ NON DÉFINI',
            'mail'    => $_SESSION['mail']    ?? '⚠️ NON DÉFINI',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    ob_end_flush();
    exit;
}

// ─────────────────────────────────────────────────────────────
// 1. Méthode HTTP
// ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Méthode non autorisée. Reçu : ' . $_SERVER['REQUEST_METHOD']);
}

// ─────────────────────────────────────────────────────────────
// 2. Vérification session
// ─────────────────────────────────────────────────────────────
if (!isset($_SESSION['id_user'])) {
    jsonError(
        'Session absente : $_SESSION["id_user"] non défini. ' .
        'Avez-vous bien passé par valider_connexion.php avant d\'arriver ici ?'
    );
}
$id_user = $_SESSION['id_user'];

// ─────────────────────────────────────────────────────────────
// 3. Lecture et nettoyage des champs POST
// ─────────────────────────────────────────────────────────────
$id_livreur        = $_POST['livreur_id']         ?? null;
$adresse_ramassage = trim($_POST['adresse_ramassage'] ?? '');
$adresse_depot     = trim($_POST['adresse_depot']     ?? '');
$distance          = floatval($_POST['distance']       ?? 0);
$prix              = floatval($_POST['prix']            ?? 0);
$poids             = floatval($_POST['poids']           ?? 0);
$type_vehicule     = trim($_POST['type_vehicule']      ?? '');
$instructions      = trim($_POST['instructions']       ?? '');

// ─────────────────────────────────────────────────────────────
// 4. Validations avec messages précis
// ─────────────────────────────────────────────────────────────
if (!$id_livreur) {
    jsonError('Champ "livreur_id" manquant ou vide dans le POST.');
}
if (empty($adresse_ramassage)) {
    jsonError('Champ "adresse_ramassage" vide. Vérifiez que le bouton "Valider ce point" a bien été cliqué sur la carte de ramassage.');
}
if (empty($adresse_depot)) {
    jsonError('Champ "adresse_depot" vide. Vérifiez que le bouton "Valider ce point" a bien été cliqué sur la carte de dépôt.');
}
if ($poids <= 0) {
    jsonError('Poids invalide. Reçu : "' . $_POST['poids'] . '". Doit être un nombre > 0.');
}
if ($prix <= 0) {
    jsonError('Prix invalide. Reçu : "' . $_POST['prix'] . '". Doit être un nombre > 0.');
}

$typesAutorises = ['moto', 'tricycle', 'camionnette', 'camion'];
if (!in_array($type_vehicule, $typesAutorises)) {
    jsonError('Type de véhicule invalide. Reçu : "' . $type_vehicule . '". Autorisés : ' . implode(', ', $typesAutorises));
}

// ─────────────────────────────────────────────────────────────
// 5. Insertion en base de données
// ─────────────────────────────────────────────────────────────
try {

    // 5a. Vérifier que l'utilisateur existe (contrainte FK)
    $stmtUser = $pdo->prepare("SELECT id_user FROM utilisateur WHERE id_user = ?");
    $stmtUser->execute([$id_user]);
    if (!$stmtUser->fetch()) {
        jsonError(
            "L'utilisateur avec id_user=\"$id_user\" n'existe pas dans la table `utilisateur`. " .
            "Créez-le ou vérifiez que la session pointe vers le bon id."
        );
    }

    // 5b. Vérifier que le livreur existe
    $stmtLiv = $pdo->prepare("SELECT id_livreur FROM livreur WHERE id_livreur = ?");
    $stmtLiv->execute([$id_livreur]);
    if (!$stmtLiv->fetch()) {
        jsonError("Le livreur avec id_livreur=$id_livreur n'existe pas dans la table `livreur`.");
    }

    // 5c. Insérer la livraison
    $sql = "INSERT INTO livraison
                (id_user, id_livreur, adresse_ramassage, adresse_depot,
                 distance, prix, poids, type_vehicule, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())";

    $stmt = $pdo->prepare($sql);
    $ok   = $stmt->execute([
        $id_user,
        $id_livreur,
        $adresse_ramassage,
        $adresse_depot,
        $distance,
        $prix,
        $poids,
        $type_vehicule,
    ]);

    if (!$ok) {
        $info = $stmt->errorInfo();
        jsonError('Échec de execute() PDO : ' . ($info[2] ?? 'erreur inconnue'));
    }

    $id_livraison = (int) $pdo->lastInsertId();

    // 5d. Créer la commission (10 % du prix)
    $commission = round($prix * 0.10, 2);
    $stmtCom    = $pdo->prepare("INSERT INTO commission (id_livraison, montant) VALUES (?, ?)");
    $stmtCom->execute([$id_livraison, $commission]);

    // ── Succès ────────────────────────────────────────────
    ob_clean();
    echo json_encode([
        'success'      => true,
        'message'      => 'Livraison créée avec succès',
        'id_livraison' => $id_livraison,
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();

} catch (PDOException $e) {
    jsonError('Erreur SQL (PDOException) : ' . $e->getMessage());
}
?>