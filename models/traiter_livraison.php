<?php
// ============================================================
// traiter_livraison.php
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

session_start();
ob_start();
header('Content-Type: application/json');

require_once '../config/config.php';

// ===================== FONCTION ERREUR =====================
function jsonError(string $msg): void {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $msg,
        'debug_post' => $_POST,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    ob_end_flush();
    exit;
}

// ===================== VÉRIFICATIONS =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Méthode non autorisée');
}

if (!isset($_SESSION['id_user'])) {
    jsonError('Session expirée. Veuillez vous reconnecter.');
}

$id_user = $_SESSION['id_user'];

$id_livreur        = $_POST['livreur_id'] ?? null;
$adresse_ramassage = trim($_POST['adresse_ramassage'] ?? '');
$adresse_depot     = trim($_POST['adresse_depot'] ?? '');
$distance          = floatval($_POST['distance'] ?? 0);
$prix              = floatval($_POST['prix'] ?? 0);
$poids             = floatval($_POST['poids'] ?? 0);
$type_vehicule     = trim($_POST['type_vehicule'] ?? '');
$instructions      = trim($_POST['instructions'] ?? '');

// Validations
if (!$id_livreur || empty($adresse_ramassage) || empty($adresse_depot)) {
    jsonError('Données incomplètes (adresse ou livreur manquant)');
}
if ($poids <= 0 || $prix <= 0) {
    jsonError('Poids ou prix invalide');
}

$typesAutorises = ['moto', 'tricycle', 'camionnette', 'camion', 'voiture'];
if (!in_array($type_vehicule, $typesAutorises)) {
    jsonError('Type de véhicule non valide');
}

// ===================== INSERTION EN BASE =====================
try {
    // Vérifier que le livreur existe et récupérer son email
    $stmt = $pdo->prepare("SELECT id_livreur, mail, nom, prenom FROM livreur WHERE id_livreur = ?");
    $stmt->execute([$id_livreur]);
    $livreur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$livreur) {
        jsonError("Livreur ID $id_livreur introuvable");
    }

    // Insertion de la livraison
    $sql = "INSERT INTO livraison 
            (id_user, id_livreur, adresse_ramassage, adresse_depot, distance, prix, poids, 
             type_vehicule, statut, date_creation) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $id_user,
        $id_livreur,
        $adresse_ramassage,
        $adresse_depot,
        $distance,
        $prix,
        $poids,
        $type_vehicule
    ]);

    $id_livraison = (int) $pdo->lastInsertId();

    // Insertion de la commission (10%)
    $commission = round($prix * 0.10, 2);
    $stmtCom = $pdo->prepare("INSERT INTO commission (id_livraison, montant) VALUES (?, ?)");
    $stmtCom->execute([$id_livraison, $commission]);

    // ===================== ENVOI EMAIL AU LIVREUR =====================
    $emailEnvoye = false;
    
    if (!empty($livreur['mail'])) {
        $emailEnvoye = envoyerNotificationLivraison(
            $livreur['mail'],
            $livreur['nom'],
            $livreur['prenom'],
            $id_livraison,
            $adresse_ramassage,
            $adresse_depot,
            $prix,
            $instructions
        );
        
        // Journaliser le résultat
        if ($emailEnvoye) {
            error_log("✅ Email envoyé avec succès à " . $livreur['mail'] . " pour la livraison #$id_livraison");
        } else {
            error_log("❌ Échec d'envoi d'email à " . $livreur['mail'] . " pour la livraison #$id_livraison");
        }
    } else {
        error_log("⚠️ Le livreur #$id_livreur n'a pas d'adresse email configurée");
    }

    // ===================== RÉPONSE SUCCÈS =====================
    ob_clean();
    echo json_encode([
        'success'      => true,
        'message'      => 'Livraison créée avec succès',
        'id_livraison' => $id_livraison,
        'email_envoye' => $emailEnvoye
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("❌ Erreur SQL: " . $e->getMessage());
    jsonError('Erreur SQL : ' . $e->getMessage());
} catch (Exception $e) {
    error_log("❌ Erreur générale: " . $e->getMessage());
    jsonError('Erreur : ' . $e->getMessage());
}

// ===================== FONCTION EMAIL AMÉLIORÉE =====================
function envoyerNotificationLivraison($email, $nom, $prenom, $id_livraison, $ramassage, $depot, $prix, $instructions = '') {
    try {
        // Vérifier que l'email est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Email invalide: $email");
            return false;
        }
        
        $mailer = new PHPMailer(true);
        
        // Configuration SMTP détaillée avec plus d'infos de debug
        $mailer->isSMTP();
        $mailer->Host       = 'smtp.gmail.com';
        $mailer->SMTPAuth   = true;
        $mailer->Username   = 'monsieuretonde@gmail.com';
        $mailer->Password   = 'avfy cxnf yjsz ruaj'; // Vérifiez que ce mot de passe est correct
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port       = 587;
        $mailer->CharSet    = 'UTF-8';
        $mailer->SMTPDebug  = 0; // Mettre à 2 pour debug (à enlever en production)
        
        // Désactiver la vérification SSL (pour test uniquement)
        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];
        
        // Expéditeur et destinataire
        $mailer->setFrom('monsieuretonde@gmail.com', 'LivreurPro');
        $mailer->addAddress($email, "$prenom $nom");
        $mailer->addReplyTo('monsieuretonde@gmail.com', 'Support LivreurPro');
        
        // Format HTML
        $mailer->isHTML(true);
        $mailer->Subject = "🚚 Nouvelle livraison disponible - #$id_livraison";
        
        // Corps du message HTML amélioré
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #00D4E8; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #00D4E8; border-radius: 5px; }
                .button { display: inline-block; background: #00D4E8; color: #000; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
                .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin:0; color:#000;'>🚚 LivreurPro</h2>
                    <p style='margin:5px 0 0; color:#000;'>Nouvelle livraison disponible</p>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$prenom $nom</strong>,</p>
                    <p>Une nouvelle livraison vous a été attribuée. Voici les détails :</p>
                    
                    <div class='info-box'>
                        <p><strong>📦 ID Livraison :</strong> #$id_livraison</p>
                        <p><strong>📍 Lieu de ramassage :</strong><br>" . nl2br(htmlspecialchars($ramassage)) . "</p>
                        <p><strong>🎯 Lieu de dépôt :</strong><br>" . nl2br(htmlspecialchars($depot)) . "</p>
                        <p><strong>💰 Prix :</strong> " . number_format($prix, 0, ',', ' ') . " FCFA</p>
                    </div>";
        
        if (!empty($instructions)) {
            $body .= "
                    <div class='info-box'>
                        <p><strong>📝 Instructions spéciales :</strong><br>" . nl2br(htmlspecialchars($instructions)) . "</p>
                    </div>";
        }
        
        $base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $base_url = str_replace('/views', '', $base_url);
        
        $body .= "
                    <div style='text-align: center;'>
                        <a href='https://codolo.gamer.gd/views/liv_dashboard.php' class='button'>
                            📊 Accéder à mon tableau de bord
                        </a>
                    </div>
                    
                    <div class='footer'>
                        <p>Cet email a été envoyé automatiquement par LivreurPro.<br>
                        Merci de ne pas y répondre directement.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        // Version texte alternative
        $altBody = "Bonjour $prenom $nom,\n\n";
        $altBody .= "Une nouvelle livraison vous a été attribuée.\n\n";
        $altBody .= "ID Livraison: #$id_livraison\n";
        $altBody .= "Ramassage: $ramassage\n";
        $altBody .= "Dépôt: $depot\n";
        $altBody .= "Prix: " . number_format($prix, 0) . " FCFA\n\n";
        if (!empty($instructions)) {
            $altBody .= "Instructions: $instructions\n\n";
        }
        $altBody .= "Connectez-vous à votre tableau de bord pour plus d'informations.\n";
        $altBody .= "https://codolo.gamer.gd/views/liv_dashboard.php";
        
        $mailer->Body = $body;
        $mailer->AltBody = $altBody;
        
        // Envoi
        if ($mailer->send()) {
            return true;
        } else {
            error_log("Erreur PHPMailer: " . $mailer->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Exception PHPMailer: " . $e->getMessage());
        return false;
    }
}
?>