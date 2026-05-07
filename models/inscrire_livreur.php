<?php
require_once '../config/config.php';

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        require '../PHPMailer-master/src/PHPMailer.php';
        require '../PHPMailer-master/src/SMTP.php';
        require '../PHPMailer-master/src/Exception.php';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Récupération et nettoyage des données
        $nom         = trim($_POST['nom'] ?? '');
        $prenom      = trim($_POST['prenom'] ?? '');
        $age         = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $sex         = $_POST['sex'] ?? '';
        $numero      = trim($_POST['numero'] ?? '');
        $mail0        = !empty($_POST['mail']) ? trim($_POST['mail']) : null;
        $adresse     = !empty($_POST['adresse']) ? trim($_POST['adresse']) : null;
        $numero_cni  = trim($_POST['numero_cni'] ?? '');
        $carte_grise = !empty($_POST['carte_grise']) ? trim($_POST['carte_grise']) : null;
        $type_vehicule  = trim($_POST['type_vehicule'] ?? '');

        // Requête SQL
        $sql = "INSERT INTO livreur 
                (nom, prenom, age, sex, numero, mail, adresse, numero_cni, carte_grise, type_vehicule) 
                VALUES 
                (:nom, :prenom, :age, :sex, :numero, :mail, :adresse, :numero_cni, :carte_grise, :type_vehicule)";

        $stmt = $pdo->prepare($sql);

        // Exécution
        $stmt->execute([
            ':nom'         => $nom,
            ':prenom'      => $prenom,
            ':age'         => $age,
            ':sex'         => $sex,
            ':numero'      => $numero,
            ':mail'        => $mail0,
            ':adresse'     => $adresse,
            ':numero_cni'  => $numero_cni,
            ':carte_grise' => $carte_grise,
            ':type_vehicule' => $type_vehicule
        ]);







        



        function creerMailer(): PHPMailer {
            $mailer = new PHPMailer(true);

            $mailer->isSMTP();
            $mailer->Host       = 'smtp.gmail.com';
            $mailer->SMTPAuth   = true;
            $mailer->Username   = 'monsieuretonde@gmail.com'; // TON EMAIL
            $mailer->Password   = 'avfy cxnf yjsz ruaj';       // MOT DE PASSE APP
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

        function envoyerMail($mail0, $sujet, $message) {
            try {
                $mail = creerMailer();

                $mail->addAddress($mail0);
                $mail->Subject = $sujet;
                $mail->Body    = $message;

                $mail->send();
                echo "✅ Email envoyé avec succès";

            } catch (Exception $e) {
                echo "❌ Erreur : " . $mail->ErrorInfo;
            }
        }

        // ================= TEST DIRECT =================
        envoyerMail(
            $mail0, // CHANGE ICI
            'Test envoi email',
            '<h2>Bonjour 👋 vous etes maintenant livreur de livreur pro cliquez <a href="https://codolo.gamer.gd/views/liv_dashboard.php"> ici </a></p>'
        );

        



        // Succès
        header("Location: ../views/ad_livreur.php");
        exit();

    } catch (PDOException $e) {

         echo "<script>
        alert('❌ Ce numéro est déjà utilisé !');
        window.history.back();
    </script>";
    }
}
?>