<?php
// // ==================== TRAITEMENT INSCRIPTION / CONNEXION UTILISATEUR ====================
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require '../PHPMailer-master/src/PHPMailer.php';
// require '../PHPMailer-master/src/SMTP.php';
// require '../PHPMailer-master/src/Exception.php';

// require_once '../config/config.php';

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     header('Location: ../views/us_form.php');
//     exit;
// }

// // Récupération et nettoyage des données
// $mail0 = trim($_POST['mail'] ?? '');

// // Vérification que le mail n'est pas vide
// if (empty($mail0)) {
//     echo "<script>alert('Veuillez entrer une adresse email.'); window.history.back();</script>";
//     exit;
// }

// try {
//     // ===================== VÉRIFICATION SI L'EMAIL EXISTE DÉJÀ =====================
//     $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE mail = ?");
//     $check->execute([$mail0]);

//     if ($check->rowCount() > 0) {

//         // ---- EMAIL DÉJÀ ENREGISTRÉ → PROCESSUS DE CONNEXION ----
//         $row       = $check->fetch(PDO::FETCH_ASSOC);
//         $id_user   = $row['id_user'];  // On récupère l'id existant, PAS de nouvel insert
//         $is_login  = true;

//     } else {

//         // ---- NOUVEL UTILISATEUR → INSCRIPTION ----
//         $is_login = false;

//         // Génération de l'ID utilisateur (id + 4 caractères aléatoires)
//         $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//         $aleatoire  = '';
//         for ($i = 0; $i < 4; $i++) {
//             $aleatoire .= $caracteres[random_int(0, strlen($caracteres) - 1)];
//         }
//         $id_user = 'id' . $aleatoire;

//         // Insertion dans la base de données
//         $stmt = $pdo->prepare("INSERT INTO utilisateur (id_user, mail, date_creation) 
//                                VALUES (?, ?, NOW())");
//         $stmt->execute([$id_user, $mail0]);
//     }

//     // // ===================== ENVOI DE L'EMAIL (inscription OU connexion) =====================
//     // $mail = new PHPMailer(true);

//     // try {
//     //     // Configuration SMTP Gmail
//     //     $mail->isSMTP();
//     //     $mail->Host       = 'smtp.gmail.com';
//     //     $mail->SMTPAuth   = true;
//     //     $mail->Username   = 'monsieuretonde@gmail.com';
//     //     $mail->Password   = 'avfy cxnf yjsz ruaj';
//     //     $mail->SMTPSecure = 'tls';
//     //     $mail->Port       = 587;
//     //     $mail->SMTPOptions = [
//     //         'ssl' => [
//     //             'verify_peer'       => false,
//     //             'verify_peer_name'  => false,
//     //             'allow_self_signed' => true,
//     //         ]
//     //     ];

//     //     // Expéditeur
//     //     $mail->setFrom('monsieuretonde@gmail.com', 'LivreurPro');

//     //     // Destinataires
//     //     $mail->addAddress($mail0);
//     //     $mail->addAddress('monsieuretonde@gmail.com');

//     //     // Contenu dynamique selon inscription ou connexion
//     //     $mail->isHTML(true);

//     //     if ($is_login) {
//     //         // ---- CONTENU EMAIL CONNEXION ----
//     //         $mail->Subject  = 'LivreurPro — Connexion à votre compte';
//     //         $header_color   = '#005566';
//     //         $accent_color   = '#00D4E8';
//     //         $greeting       = 'Bon retour sur LivreurPro ! 👋';
//     //         $intro_text     = 'Nous avons reçu une demande de connexion associée à votre adresse email.
//     //                            <br><br>
//     //                            Voici votre identifiant personnel pour accéder à votre compte :';
//     //         $code_label     = 'Votre identifiant de connexion';
//     //         $notice         = '⚠️ Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.';
//     //         $btn_text       = '🔗 Accéder à mon compte';
//     //         $footer_note    = 'Cet email a été envoyé suite à une tentative de connexion.';
//     //         $badge_html     = '<div style="display:inline-block;background:rgba(0,212,232,0.15);
//     //                             border:1px solid rgba(0,212,232,0.4);color:#00D4E8;
//     //                             font-size:11px;font-weight:700;letter-spacing:2px;
//     //                             text-transform:uppercase;padding:5px 14px;border-radius:50px;
//     //                             margin-bottom:20px;">CONNEXION AU COMPTE</div>';
//     //     } else {
//     //         // ---- CONTENU EMAIL INSCRIPTION ----
//     //         $mail->Subject  = 'Bienvenue sur LivreurPro — Votre identifiant de connexion';
//     //         $header_color   = '#1C1F24';
//     //         $accent_color   = '#00D4E8';
//     //         $greeting       = 'Bienvenue sur LivreurPro ! 🚀';
//     //         $intro_text     = 'Merci de vous être inscrit sur LivreurPro, la plateforme de livraison
//     //                            nouvelle génération au Cameroun.
//     //                            <br><br>
//     //                            Voici votre identifiant unique qui vous permettra de vous connecter
//     //                            et de gérer vos livraisons :';
//     //         $code_label     = 'Votre identifiant personnel';
//     //         $notice         = '⚠️ Conservez précieusement cet identifiant.<br>
//     //                            Il vous sera demandé à chaque connexion.';
//     //         $btn_text       = '🔗 Accéder à mon compte';
//     //         $footer_note    = 'Cet email a été envoyé suite à votre inscription.';
//     //         $badge_html     = '<div style="display:inline-block;background:rgba(0,212,232,0.15);
//     //                             border:1px solid rgba(0,212,232,0.4);color:#00D4E8;
//     //                             font-size:11px;font-weight:700;letter-spacing:2px;
//     //                             text-transform:uppercase;padding:5px 14px;border-radius:50px;
//     //                             margin-bottom:20px;">NOUVELLE INSCRIPTION</div>';
//     //     }

//     //     $mail->Body = '
//     //     <!DOCTYPE html>
//     //     <html lang="fr">
//     //     <head>
//     //         <meta charset="UTF-8">
//     //         <meta name="viewport" content="width=device-width, initial-scale=1.0">
//     //         <style>
//     //             * { margin:0; padding:0; box-sizing:border-box; }
//     //             body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif; background-color:#1C1F24; padding:20px; }
//     //             .email-container { max-width:550px; margin:0 auto; background:#252930; border-radius:16px; overflow:hidden; border:1px solid rgba(0,212,232,0.2); }
//     //             .email-header { background:linear-gradient(135deg,' . $header_color . ' 0%,#252930 100%); padding:30px 24px; text-align:center; border-bottom:2px solid ' . $accent_color . '; }
//     //             .logo { font-size:32px; font-weight:900; font-style:italic; color:#F2F4F7; letter-spacing:-1px; }
//     //             .logo span { color:' . $accent_color . '; }
//     //             .email-body { padding:32px 28px; background:#1C1F24; }
//     //             .greeting { font-size:24px; font-weight:700; color:#F2F4F7; margin-bottom:14px; }
//     //             .message { color:#9CA3AF; line-height:1.65; margin-bottom:24px; font-size:15px; }
//     //             .code-box { background:#2F343C; border-left:4px solid ' . $accent_color . '; padding:20px; margin:24px 0; text-align:center; border-radius:8px; }
//     //             .code-label { font-size:12px; text-transform:uppercase; letter-spacing:2px; color:' . $accent_color . '; margin-bottom:10px; font-weight:600; }
//     //             .code-value { font-family:"Courier New",monospace; font-size:32px; font-weight:700; color:' . $accent_color . '; letter-spacing:2px; background:#1C1F24; display:inline-block; padding:8px 24px; border-radius:8px; }
//     //             .info-text { color:#6B7280; font-size:13px; margin-top:14px; line-height:1.5; }
//     //             .btn { display:inline-block; background:' . $accent_color . '; color:#1C1F24; text-decoration:none; padding:12px 32px; border-radius:40px; font-weight:700; margin-top:16px; font-size:14px; }
//     //             .footer { background:#252930; padding:20px 28px; text-align:center; border-top:1px solid #2F343C; }
//     //             .footer-text { color:#6B7280; font-size:11px; line-height:1.6; }
//     //         </style>
//     //     </head>
//     //     <body>
//     //         <div class="email-container">
//     //             <div class="email-header">
//     //                 <div class="logo">LIVREUR<span>PRO</span></div>
//     //             </div>
//     //             <div class="email-body">
//     //                 <div style="text-align:center;">' . $badge_html . '</div>
//     //                 <div class="greeting">' . $greeting . '</div>
//     //                 <div class="message">' . $intro_text . '</div>
//     //                 <div class="code-box">
//     //                     <div class="code-label">' . $code_label . '</div>
//     //                     <div class="code-value">' . $id_user . '</div>
//     //                     <div class="info-text">' . $notice . '</div>
//     //                 </div>
//     //                 ' . (!$is_login ? '
//     //                 <div class="message" style="margin-top:20px;">
//     //                     Vous pouvez dès maintenant :
//     //                     <ul style="margin-top:12px;margin-left:20px;color:#9CA3AF;">
//     //                         <li>✅ Parcourir notre catalogue de livreurs</li>
//     //                         <li>✅ Créer votre première livraison</li>
//     //                         <li>✅ Suivre vos colis en temps réel</li>
//     //                     </ul>
//     //                 </div>' : '') . '
//     //                 <div style="text-align:center;">
//     //                     <a href="https://livreurpro.cm/connexion.php" class="btn">' . $btn_text . '</a>
//     //                 </div>
//     //             </div>
//     //             <div class="footer">
//     //                 <div class="footer-text">
//     //                     © 2025 LivreurPro — La livraison nouvelle génération au Cameroun<br>
//     //                     ' . $footer_note . ' Envoyé à <strong style="color:' . $accent_color . ';">' . htmlspecialchars($mail0) . '</strong><br>
//     //                     Si vous n\'êtes pas à l\'origine de cette action, ignorez cet email.
//     //                 </div>
//     //             </div>
//     //         </div>
//     //     </body>
//     //     </html>';

//     //     $mail->send();
//     //     header('Location: ../views/us_otp.php');
//     //     exit;

//     // } catch (Exception $e) {
//     //     echo "❌ Erreur envoi email : {$mail->ErrorInfo}";
//     // }

















    
// // ===================== ENVOI DES EMAILS =====================
// $mail = new PHPMailer(true);

// try {
//     // Configuration SMTP (identique)
//     $mail->isSMTP();
//     $mail->Host       = 'smtp.gmail.com';
//     $mail->SMTPAuth   = true;
//     $mail->Username   = 'monsieuretonde@gmail.com';
//     $mail->Password   = 'avfy cxnf yjsz ruaj';
//     $mail->SMTPSecure = 'tls';
//     $mail->Port       = 587;
//     $mail->CharSet    = 'UTF-8';
//     $mail->SMTPOptions = [
//         'ssl' => [
//             'verify_peer' => false,
//             'verify_peer_name' => false,
//             'allow_self_signed' => true
//         ]
//     ];
    
//     $mail->setFrom('monsieuretonde@gmail.com', 'LivreurPro');
//     $mail->isHTML(true);
    
//     // Contenu commun (votre HTML)
//     $mail->Subject = $is_login ? 'Connexion à votre compte' : 'Bienvenue sur LivreurPro';
//     $mail->Body = '... votre HTML ...'; // Mettez votre contenu ici
    
//     // 1️⃣ Envoyer d'abord à l'admin (toujours ok)
//     $mail->clearAddresses();
//     $mail->addAddress('monsieuretonde@gmail.com');
//     $mail->send();
    
//     // 2️⃣ Essayer d'envoyer à l'utilisateur
//     $mail->clearAddresses();
//     $mail->addAddress($mail0);
    
//     if ($mail->send()) {
//         // Succès : email utilisateur envoyé
//         error_log("Email envoyé avec succès à: " . $mail0);
//     } else {
//         // Échec : on note mais on continue
//         error_log("Impossible d'envoyer l'email à: " . $mail0 . " - " . $mail->ErrorInfo);
//     }
    
//     // Redirection quoi qu'il arrive (car l'admin a reçu l'info)
//     header('Location: ../views/us_otp.php');
//     exit;
    
// } catch (Exception $e) {
//     // Gestion d'erreur
//     error_log("Erreur SMTP: " . $mail->ErrorInfo);
    
//     // Si c'est l'email utilisateur qui a échoué mais admin OK
//     if (strpos($mail->ErrorInfo, $mail0) !== false) {
//         // On redirige quand même car l'admin est notifié
//         header('Location: ../views/us_otp.php');
//         exit;
//     } else {
//         // Erreur critique (même l'admin n'a pas reçu)
//         echo "<script>
//             alert('Service email temporairement indisponible. Veuillez réessayer.');
//             window.history.back();
//         </script>";
//         exit;
//     }
// }





// } catch (PDOException $e) {
//     error_log("Erreur inscription/connexion : " . $e->getMessage());
//     echo "<script>
//         alert('Une erreur est survenue. Veuillez réessayer.');
//         window.history.back();
//     </script>";
//     exit;
// }





































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

// Récupération et nettoyage de l'email
$mail0 = trim($_POST['mail'] ?? '');

if (empty($mail0)) {
    echo "<script>alert('Veuillez entrer une adresse email.'); window.history.back();</script>";
    exit;
}

// ===================== FONCTION : CRÉER UN MAILER SMTP =====================
function creerMailer(): PHPMailer {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host       = 'smtp.gmail.com';
    $mailer->SMTPAuth   = true;
    $mailer->Username   = 'monsieuretonde@gmail.com';
    $mailer->Password   = 'avfy cxnf yjsz ruaj';
    $mailer->SMTPSecure = 'tls';
    $mailer->Port       = 587;
    $mailer->CharSet    = 'UTF-8';
    $mailer->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];
    $mailer->setFrom('monsieuretonde@gmail.com', 'LivreurPro');
    $mailer->isHTML(true);
    return $mailer;
}

// ===================== FONCTION : CONSTRUIRE LE CORPS DU MAIL =====================
function construireEmail(string $id_user, string $mail0, bool $is_login): string {
    if ($is_login) {
        $badge        = 'CONNEXION';
        $badge_color  = '#3B82F6';
        $titre        = 'Bon retour ! 👋';
        $message      = 'Vous avez demandé à vous connecter à votre compte LivreurPro.<br>Voici votre code de vérification :';
        $label_code   = 'Code de connexion';
        $note         = 'Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.';
        $footer_note  = 'Demande de connexion reçue pour';
    } else {
        $badge        = 'INSCRIPTION';
        $badge_color  = '#10B981';
        $titre        = 'Bienvenue sur LivreurPro ! 🚀';
        $message      = 'Votre compte a été créé avec succès.<br>Voici votre code de vérification personnel :';
        $label_code   = 'Votre code d\'accès';
        $note         = 'Conservez ce code, il vous sera demandé à chaque connexion.';
        $footer_note  = 'Inscription effectuée pour';
    }

    $mail_safe = htmlspecialchars($mail0);
    $id_safe   = htmlspecialchars($id_user);

    return <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:32px 16px;">
            <tr>
                <td align="center">

                    <!-- Carte principale -->
                    <table width="520" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                        <!-- Header -->
                        <tr>
                            <td style="background:#0f172a;padding:24px 32px;text-align:center;">
                                <span style="font-size:22px;font-weight:900;font-style:italic;color:#ffffff;letter-spacing:-0.5px;">
                                    LIVREUR<span style="color:#00D4E8;">PRO</span>
                                </span>
                            </td>
                        </tr>

                        <!-- Badge -->
                        <tr>
                            <td style="padding:24px 32px 0;text-align:center;">
                                <span style="display:inline-block;background:{$badge_color}1a;color:{$badge_color};font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;padding:5px 16px;border-radius:100px;border:1px solid {$badge_color}40;">
                                    {$badge}
                                </span>
                            </td>
                        </tr>

                        <!-- Corps -->
                        <tr>
                            <td style="padding:20px 32px 28px;">
                                <h2 style="margin:0 0 10px;font-size:20px;color:#0f172a;font-weight:700;">{$titre}</h2>
                                <p style="margin:0 0 20px;color:#64748b;font-size:14px;line-height:1.7;">{$message}</p>

                                <!-- Bloc code -->
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;text-align:center;">
                                            <p style="margin:0 0 8px;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#94a3b8;">{$label_code}</p>
                                            <p style="margin:0;font-family:'Courier New',monospace;font-size:30px;font-weight:800;color:#0f172a;letter-spacing:4px;">{$id_safe}</p>
                                        </td>
                                    </tr>
                                </table>
                                <a href="https://codolo.gamer.gd/views/us_otp.php">Aller</a>

                                <p style="margin:16px 0 0;font-size:12px;color:#94a3b8;line-height:1.6;text-align:center;"> {$note}</p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 32px;text-align:center;">
                                <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.7;">
                                    © 2026 LivreurPro — Cameroun<br>
                                    {$footer_note} <strong style="color:#0f172a;">{$mail_safe}</strong>
                                </p>
                            </td>
                        </tr>

                    </table>
                    <!-- Fin carte -->

                </td>
            </tr>
        </table>

    </body>
    </html>
    HTML;
}

// ===================== FONCTION : ENVOYER UN MAIL =====================
function envoyerMail(string $destinataire, string $sujet, string $corps): bool {
    try {
        $mailer          = creerMailer();
        $mailer->addAddress($destinataire);
        $mailer->Subject = $sujet;
        $mailer->Body    = $corps;
        $mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Échec envoi email vers $destinataire : " . $e->getMessage());
        return false;
    }
}

// ===================== LOGIQUE PRINCIPALE =====================
try {

    // Vérification si l'email existe déjà
    $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE mail = ?");
    $check->execute([$mail0]);

    if ($check->rowCount() > 0) {

        // ---- EMAIL EXISTANT → CONNEXION ----
        $row      = $check->fetch(PDO::FETCH_ASSOC);
        $id_user  = $row['id_user'];
        $is_login = true;

    } else {

        // ---- NOUVEL UTILISATEUR → INSCRIPTION ----
        $is_login   = false;
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $aleatoire  = '';
        for ($i = 0; $i < 4; $i++) {
            $aleatoire .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        $id_user = 'id' . $aleatoire;

        $stmt = $pdo->prepare("INSERT INTO utilisateur (id_user, mail, date_creation) VALUES (?, ?, NOW())");
        $stmt->execute([$id_user, $mail0]);
    }

    // Construction du contenu du mail
    $sujet = $is_login
        ? 'LivreurPro — Votre code de connexion'
        : 'LivreurPro — Bienvenue, votre code d\'accès';

    $corps = construireEmail($id_user, $mail0, $is_login);

    // Envoi à l'utilisateur
    envoyerMail($mail0, $sujet, $corps);

    // Envoi de notification à l'admin
    $sujet_admin = ($is_login ? '[CONNEXION] ' : '[INSCRIPTION] ') . $mail0;
    envoyerMail('monsieuretonde@gmail.com', $sujet_admin, $corps);

    // Redirection dans tous les cas (l'inscription/connexion est faite)
    header('Location: ../views/us_otp.php');
    exit;

} catch (PDOException $e) {
    error_log("Erreur BDD inscription/connexion : " . $e->getMessage());
    echo "<script>
        alert('Une erreur est survenue. Veuillez réessayer.');
        window.history.back();
    </script>";
    exit;
}

?>