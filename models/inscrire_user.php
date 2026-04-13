<?php
// pour send le mail

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';


// Inclusion du fichier de configuration (connexion à la base de données)
require_once '../config/config.php';

// Vérifie que la requête est de type POST (formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération du numéro envoyé via le formulaire
    // trim() permet de supprimer les espaces inutiles
    $mail = trim($_POST['mail'] ?? '');

    // Génération d'un identifiant aléatoire
    // Liste des caractères possibles pour l'ID
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // Variable qui contiendra l'ID aléatoire
    $aleatoire = '';

    // Boucle pour générer 4 caractères aléatoires
    for ($i = 0; $i < 4; $i++) {
        // random_int() permet de choisir un index sécurisé aléatoire
        $aleatoire .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    // Création de l'id_user final avec le préfixe "id"
    $id_user = 'id' . $aleatoire;

    // Vérification que le champ numéro n'est pas vide
    if (empty($mail)) {
        // Redirection avec message d'erreur si le numéro est vide
        header('Location: form_inscription.php');
        exit;
    }

    try {
        // Préparation de la requête SQL pour éviter les injections SQL
        // La date de création est ajoutée automatiquement par MySQL (NOW())
        $stmt = $pdo->prepare("INSERT INTO utilisateur (id_user, mail, date_creation) VALUES (?, ?, NOW())");

        // Exécution de la requête avec les valeurs
        $stmt->execute([$id_user, $mail]);

        // Redirection en cas de succès
        // header('Location: send_mail.php');
        // exit;







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
        $mail->addAddress('leaannatismey@gmail.com');

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
        // En cas d'erreur SQL, on enregistre l'erreur dans les logs
        error_log("Erreur insertion : " . $e->getMessage());

        // Redirection avec message d'erreur générique
        header('Location: ../views/form_inscription.php');
        exit;
    }

} else {
    // Si la page est accédée sans POST, redirection vers le formulaire
    header('Location: ../views/form_inscription.php');
    exit;
}
?>