<?php
// ==================== TRAITEMENT INSCRIPTION UTILISATEUR ====================
// Fichier : traitement_inscription.php   (ou le nom que tu utilises actuellement)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';



require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/form_inscription.php');
    exit;
}

// Récupération et nettoyage des données
$mail0 = trim($_POST['mail'] ?? '');

// Vérification que le mail n'est pas vide
if (empty($mail0)) {
    echo "<script>alert('Veuillez entrer une adresse email.'); window.history.back();</script>";
    exit;
}

// Génération de l'ID utilisateur (id + 4 caractères aléatoires)
$caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$aleatoire = '';
for ($i = 0; $i < 4; $i++) {
    $aleatoire .= $caracteres[random_int(0, strlen($caracteres) - 1)];
}
$id_user = 'id' . $aleatoire;

try {
    // ===================== VÉRIFICATION SI L'EMAIL EXISTE DÉJÀ =====================
    $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE mail = ?");
    $check->execute([$mail0]);

    if ($check->rowCount() > 0) {
        // Email déjà enregistré → Alerte + retour au formulaire
        echo "<script>
            alert('Cette adresse email est déjà enregistrée dans la base de données !');
            window.history.back();
        </script>";
        exit;
    }

    // ===================== INSERTION DANS LA BASE DE DONNÉES =====================
    $stmt = $pdo->prepare("INSERT INTO utilisateur (id_user, mail, date_creation) 
                           VALUES (?, ?, NOW())");

    $stmt->execute([$id_user, $mail0]);

    // ===================== MESSAGE DE CONFIRMATION =====================
    $mail = new PHPMailer(true);

    try {
        // 🔧 Configuration SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'monsieuretonde@gmail.com'; // 🔴 ton gmail
        $mail->Password = 'avfy cxnf yjsz ruaj'; // 🔴 celui généré

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

        // 📩 Expéditeur
        $mail->setFrom('monsieuretonde@gmail.com', 'Livpro');

        // 📬 Destinataire
        $mail->addAddress($mail0);
        $mail->addAddress('monsieuretonde@gmail.com');
        // 📝 Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Nouveau message';

        $mail->Body = "
            <h3>votre code est $id_user</h3>
            
        ";

        $mail->send();
        header('Location: ../views/otp.php');

    } catch (Exception $e) {
        echo "❌ Erreur: {$mail->ErrorInfo}";
    }





    

} catch (PDOException $e) {
    // Erreur SQL (ex: contrainte d'unicité, etc.)
    error_log("Erreur insertion utilisateur : " . $e->getMessage());
    
    echo "<script>
        alert('Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.');
        window.history.back();
    </script>";
    exit;
}
?>