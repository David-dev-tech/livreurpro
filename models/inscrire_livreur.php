<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Récupération et nettoyage des données
        $nom         = trim($_POST['nom'] ?? '');
        $prenom      = trim($_POST['prenom'] ?? '');
        $age         = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $sex         = $_POST['sex'] ?? '';
        $numero      = trim($_POST['numero'] ?? '');
        $mail        = !empty($_POST['mail']) ? trim($_POST['mail']) : null;
        $adresse     = !empty($_POST['adresse']) ? trim($_POST['adresse']) : null;
        $numero_cni  = trim($_POST['numero_cni'] ?? '');
        $carte_grise = !empty($_POST['carte_grise']) ? trim($_POST['carte_grise']) : null;

        // Requête SQL
        $sql = "INSERT INTO livreur 
                (nom, prenom, age, sex, numero, mail, adresse, numero_cni, carte_grise) 
                VALUES 
                (:nom, :prenom, :age, :sex, :numero, :mail, :adresse, :numero_cni, :carte_grise)";

        $stmt = $pdo->prepare($sql);

        // Exécution
        $stmt->execute([
            ':nom'         => $nom,
            ':prenom'      => $prenom,
            ':age'         => $age,
            ':sex'         => $sex,
            ':numero'      => $numero,
            ':mail'        => $mail,
            ':adresse'     => $adresse,
            ':numero_cni'  => $numero_cni,
            ':carte_grise' => $carte_grise
        ]);

        // Succès
        header("Location: ../views/ad_livreur.php");
        exit();

    } catch (PDOException $e) {

        // Erreur
        echo "Element deja existant";
        exit();
    }
}
?>