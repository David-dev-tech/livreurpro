<?php
session_start();

// =============================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================
require_once '../config/config.php';

// =============================================
// RÉCUPÉRATION DES LIVREURS
// =============================================
try {
    $stmt = $pdo->query("SELECT * FROM livreur ORDER BY id_livreur DESC");
    $livreurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des livreurs : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivreurPro | Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dark/ad_css.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="menu-toggle" id="menuToggle">
                    <span></span><span></span><span></span>
                </div>

                <a href="ad_dashboard.php" style="text-decoration: none;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
                </a>

                <div class="nav-links" id="navLinks">
                    <a href="ad_dashboard.php">Dashboard</a>
                    <a href="ad_utilisateur.php">Utilisateurs</a>
                    <a href="ad_livreur.php" class="active">Livreurs</a>
                    <a href="ad_livraison.php">Livraisons</a>
                                    <a href="#" class="nav-link-disabled" style="opacity: 0.5; pointer-events: none; cursor: default; color: #888;">Commission</a>

                    <div class="user-info">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="menu-overlay" id="menuOverlay"></div>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Gestion des Livreurs</h1>
                <p class="page-subtitle">Enregistrez et gérez tous les livreurs de votre équipe</p>
            </div>

            <!-- Formulaire d'ajout -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-user-plus"></i> Enregistrer un nouveau livreur</div>
                </div>
                
                <form action="../models/inscrire_livreur.php" method="POST" class="add-form">
                    <!-- Ton formulaire reste identique -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom <span class="required">*</span></label>
                            <input type="text" name="nom" required placeholder="Nom du livreur">
                        </div>
                        <div class="form-group">
                            <label>Prénom <span class="required">*</span></label>
                            <input type="text" name="prenom" required placeholder="Prénom du livreur">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Âge</label>
                            <input type="number" name="age" min="18" placeholder="Âge">
                        </div>
                        <div class="form-group">
                            <label>Sexe <span class="required">*</span></label>
                            <select name="sex" required>
                                <option value="">Sélectionnez</option>
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Numéro de téléphone <span class="required">*</span></label>
                            <input type="tel" name="numero" required placeholder="+237 6XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="mail" placeholder="exemple@email.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Adresse</label>
                        <textarea name="adresse" rows="3" placeholder="Adresse complète du livreur"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Type de véhicule <span class="required">*</span></label>
                        <select name="type_vehicule" required>
                            <option value="">Sélectionnez le type de véhicule</option>
                            <option value="moto">Moto</option>
                            <option value="tricycle">Tricycle</option>
                            <option value="camionnette">Camionnette</option>
                            <option value="camion">Camion</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Numéro CNI <span class="required">*</span></label>
                            <input type="text" name="numero_cni" required placeholder="Numéro de la CNI">
                        </div>
                        <div class="form-group">
                            <label>Carte grise (optionnel)</label>
                            <input type="text" name="carte_grise" placeholder="Numéro ou référence carte grise">
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="reset" class="btn-secondary">Réinitialiser</button>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer le livreur</button>
                    </div>
                </form>
            </div>

            <!-- Tableau des livreurs -->
            <div class="table-section" style="margin-top: 40px;">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-list"></i> Liste des livreurs enregistrés (<?= count($livreurs) ?>)
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Âge</th>
                            <th>Sexe</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>CNI</th>
                            <th>Carte grise</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($livreurs)): ?>
                            <tr><td colspan="9" style="text-align:center; padding:40px;">Aucun livreur enregistré pour le moment.</td></tr>
                        <?php else: ?>
                            <?php foreach ($livreurs as $l): ?>
                                <tr>
                                    <td><?= $l['id_livreur'] ?></td>
                                    <td><strong><?= htmlspecialchars($l['nom'] . ' ' . $l['prenom']) ?></strong></td>
                                    <td><?= $l['age'] ?? '-' ?></td>
                                    <td><?= $l['sex'] === 'M' ? 'Masculin' : 'Féminin' ?></td>
                                    <td><?= htmlspecialchars($l['numero']) ?></td>
                                    <td><?= htmlspecialchars($l['mail'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($l['numero_cni'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($l['carte_grise'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn-small" onclick="alert('Modification à venir')">Modifier</button>
                                        <button class="btn-small btn-danger" onclick="if(confirm('Supprimer ce livreur ?')) location.href='ad_livreur_delete.php?id=<?= $l['id_livreur'] ?>'">Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('active');
            document.getElementById('menuOverlay').classList.toggle('active');
        });
    </script>
</body>
</html>