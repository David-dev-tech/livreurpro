<?php
// traitement.php

// Inclusion du fichier de configuration (connexion à la base de données)
require_once 'config.php';

// Vérifie que la requête est de type POST (formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération du numéro envoyé via le formulaire
    // trim() permet de supprimer les espaces inutiles
    $numero = trim($_POST['numero'] ?? '');

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
    if (empty($numero)) {
        // Redirection avec message d'erreur si le numéro est vide
        header('Location: form_inscription.php');
        exit;
    }

    try {
        // Préparation de la requête SQL pour éviter les injections SQL
        $stmt = $pdo->prepare("INSERT INTO utilisateur (id_user, numero) VALUES (?, ?)");

        // Exécution de la requête avec les valeurs
        $stmt->execute([$id_user, $numero]);

        // Redirection en cas de succès
        header('Location: catalogue.php');
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur SQL, on enregistre l'erreur dans les logs
        error_log("Erreur insertion : " . $e->getMessage());

        // Redirection avec message d'erreur générique
        header('Location: form_inscription.php');
        exit;
    }

} else {
    // Si la page est accédée sans POST, redirection vers le formulaire
    header('Location: form_inscription.php');
    exit;
}
?>