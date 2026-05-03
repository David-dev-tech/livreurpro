<?php
// ==================== TRAITEMENT INSCRIPTION / CONNEXION UTILISATEUR ====================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/us_form.php');
    exit;
}

// Récupération et nettoyage des données
$mail0 = trim($_POST['mail'] ?? '');

// Vérification que le mail n'est pas vide
if (empty($mail0)) {
    echo "<script>alert('Veuillez entrer une adresse email.'); window.history.back();</script>";
    exit;
}

try {
    // ===================== VÉRIFICATION SI L'EMAIL EXISTE DÉJÀ =====================
    $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE mail = ?");
    $check->execute([$mail0]);

    if ($check->rowCount() > 0) {

        // ---- EMAIL DÉJÀ ENREGISTRÉ → PROCESSUS DE CONNEXION ----
        $row       = $check->fetch(PDO::FETCH_ASSOC);
        $id_user   = $row['id_user'];  // On récupère l'id existant, PAS de nouvel insert
        $is_login  = true;

    } else {

        // ---- NOUVEL UTILISATEUR → INSCRIPTION ----
        $is_login = false;

        // Génération de l'ID utilisateur (id + 4 caractères aléatoires)
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $aleatoire  = '';
        for ($i = 0; $i < 4; $i++) {
            $aleatoire .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        $id_user = 'id' . $aleatoire;

        // Insertion dans la base de données
        $stmt = $pdo->prepare("INSERT INTO utilisateur (id_user, mail, date_creation) 
                               VALUES (?, ?, NOW())");
        $stmt->execute([$id_user, $mail0]);
    }

    // ===================== ENVOI DE L'EMAIL (inscription OU connexion) =====================
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'monsieuretonde@gmail.com';
        $mail->Password   = 'avfy cxnf yjsz ruaj';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        // Expéditeur
        $mail->setFrom('monsieuretonde@gmail.com', 'LivreurPro');

        // Destinataires
        $mail->addAddress($mail0);
        $mail->addAddress('monsieuretonde@gmail.com');

        // Contenu dynamique selon inscription ou connexion
        $mail->isHTML(true);

        if ($is_login) {
            // ---- CONTENU EMAIL CONNEXION ----
            $mail->Subject  = 'LivreurPro — Connexion à votre compte';
            $header_color   = '#005566';
            $accent_color   = '#00D4E8';
            $greeting       = 'Bon retour sur LivreurPro ! 👋';
            $intro_text     = 'Nous avons reçu une demande de connexion associée à votre adresse email.
                               <br><br>
                               Voici votre identifiant personnel pour accéder à votre compte :';
            $code_label     = 'Votre identifiant de connexion';
            $notice         = '⚠️ Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.';
            $btn_text       = '🔗 Accéder à mon compte';
            $footer_note    = 'Cet email a été envoyé suite à une tentative de connexion.';
            $badge_html     = '<div style="display:inline-block;background:rgba(0,212,232,0.15);
                                border:1px solid rgba(0,212,232,0.4);color:#00D4E8;
                                font-size:11px;font-weight:700;letter-spacing:2px;
                                text-transform:uppercase;padding:5px 14px;border-radius:50px;
                                margin-bottom:20px;">CONNEXION AU COMPTE</div>';
        } else {
            // ---- CONTENU EMAIL INSCRIPTION ----
            $mail->Subject  = 'Bienvenue sur LivreurPro — Votre identifiant de connexion';
            $header_color   = '#1C1F24';
            $accent_color   = '#00D4E8';
            $greeting       = 'Bienvenue sur LivreurPro ! 🚀';
            $intro_text     = 'Merci de vous être inscrit sur LivreurPro, la plateforme de livraison
                               nouvelle génération au Cameroun.
                               <br><br>
                               Voici votre identifiant unique qui vous permettra de vous connecter
                               et de gérer vos livraisons :';
            $code_label     = 'Votre identifiant personnel';
            $notice         = '⚠️ Conservez précieusement cet identifiant.<br>
                               Il vous sera demandé à chaque connexion.';
            $btn_text       = '🔗 Accéder à mon compte';
            $footer_note    = 'Cet email a été envoyé suite à votre inscription.';
            $badge_html     = '<div style="display:inline-block;background:rgba(0,212,232,0.15);
                                border:1px solid rgba(0,212,232,0.4);color:#00D4E8;
                                font-size:11px;font-weight:700;letter-spacing:2px;
                                text-transform:uppercase;padding:5px 14px;border-radius:50px;
                                margin-bottom:20px;">NOUVELLE INSCRIPTION</div>';
        }

        $mail->Body = '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif; background-color:#1C1F24; padding:20px; }
                .email-container { max-width:550px; margin:0 auto; background:#252930; border-radius:16px; overflow:hidden; border:1px solid rgba(0,212,232,0.2); }
                .email-header { background:linear-gradient(135deg,' . $header_color . ' 0%,#252930 100%); padding:30px 24px; text-align:center; border-bottom:2px solid ' . $accent_color . '; }
                .logo { font-size:32px; font-weight:900; font-style:italic; color:#F2F4F7; letter-spacing:-1px; }
                .logo span { color:' . $accent_color . '; }
                .email-body { padding:32px 28px; background:#1C1F24; }
                .greeting { font-size:24px; font-weight:700; color:#F2F4F7; margin-bottom:14px; }
                .message { color:#9CA3AF; line-height:1.65; margin-bottom:24px; font-size:15px; }
                .code-box { background:#2F343C; border-left:4px solid ' . $accent_color . '; padding:20px; margin:24px 0; text-align:center; border-radius:8px; }
                .code-label { font-size:12px; text-transform:uppercase; letter-spacing:2px; color:' . $accent_color . '; margin-bottom:10px; font-weight:600; }
                .code-value { font-family:"Courier New",monospace; font-size:32px; font-weight:700; color:' . $accent_color . '; letter-spacing:2px; background:#1C1F24; display:inline-block; padding:8px 24px; border-radius:8px; }
                .info-text { color:#6B7280; font-size:13px; margin-top:14px; line-height:1.5; }
                .btn { display:inline-block; background:' . $accent_color . '; color:#1C1F24; text-decoration:none; padding:12px 32px; border-radius:40px; font-weight:700; margin-top:16px; font-size:14px; }
                .footer { background:#252930; padding:20px 28px; text-align:center; border-top:1px solid #2F343C; }
                .footer-text { color:#6B7280; font-size:11px; line-height:1.6; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <div class="logo">LIVREUR<span>PRO</span></div>
                </div>
                <div class="email-body">
                    <div style="text-align:center;">' . $badge_html . '</div>
                    <div class="greeting">' . $greeting . '</div>
                    <div class="message">' . $intro_text . '</div>
                    <div class="code-box">
                        <div class="code-label">' . $code_label . '</div>
                        <div class="code-value">' . $id_user . '</div>
                        <div class="info-text">' . $notice . '</div>
                    </div>
                    ' . (!$is_login ? '
                    <div class="message" style="margin-top:20px;">
                        Vous pouvez dès maintenant :
                        <ul style="margin-top:12px;margin-left:20px;color:#9CA3AF;">
                            <li>✅ Parcourir notre catalogue de livreurs</li>
                            <li>✅ Créer votre première livraison</li>
                            <li>✅ Suivre vos colis en temps réel</li>
                        </ul>
                    </div>' : '') . '
                    <div style="text-align:center;">
                        <a href="https://livreurpro.cm/connexion.php" class="btn">' . $btn_text . '</a>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-text">
                        © 2025 LivreurPro — La livraison nouvelle génération au Cameroun<br>
                        ' . $footer_note . ' Envoyé à <strong style="color:' . $accent_color . ';">' . htmlspecialchars($mail0) . '</strong><br>
                        Si vous n\'êtes pas à l\'origine de cette action, ignorez cet email.
                    </div>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
        header('Location: ../views/us_otp.php');
        exit;

    } catch (Exception $e) {
        echo "❌ Erreur envoi email : {$mail->ErrorInfo}";
    }

} catch (PDOException $e) {
    error_log("Erreur inscription/connexion : " . $e->getMessage());
    echo "<script>
        alert('Une erreur est survenue. Veuillez réessayer.');
        window.history.back();
    </script>";
    exit;
}
?>