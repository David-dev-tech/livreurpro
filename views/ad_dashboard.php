<?php
session_start();
require_once '../config/config.php';   // ← Ton fichier de connexion

try {
    // ==================== STATISTIQUES PRINCIPALES ====================
    $totalUsers = $pdo->query("SELECT COUNT(*) as total FROM utilisateur")->fetchColumn();
    $totalDrivers = $pdo->query("SELECT COUNT(*) as total FROM livreur")->fetchColumn();
    $totalDeliveries = $pdo->query("SELECT COUNT(*) as total FROM livraison")->fetchColumn();
    
    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(prix), 0) FROM livraison WHERE statut = 'terminee'")->fetchColumn();
    $totalCommission = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM commission")->fetchColumn();

    // Livraisons par statut
    $stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM livraison GROUP BY statut");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Répartition par véhicule
    $stmt = $pdo->query("SELECT type_vehicule, COUNT(*) as count FROM livreur GROUP BY type_vehicule");
    $vehicleCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // ==================== DERNIERS ENREGISTREMENTS ====================
    $recentUsers = $pdo->query("SELECT * FROM utilisateur ORDER BY date_creation DESC LIMIT 5")
                       ->fetchAll(PDO::FETCH_ASSOC);

    $recentDeliveries = $pdo->query("
        SELECT l.*, u.mail as client_email, 
               CONCAT(liv.nom, ' ', liv.prenom) as livreur_nom
        FROM livraison l
        LEFT JOIN utilisateur u ON l.id_user = u.id_user
        LEFT JOIN livreur liv ON l.id_livreur = liv.id_livreur
        ORDER BY l.date_creation DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $recentDrivers = $pdo->query("SELECT * FROM livreur ORDER BY id_livreur DESC LIMIT 5")
                         ->fetchAll(PDO::FETCH_ASSOC);

    // ==================== TOP 3 LIVREURS (CORRECTION ICI) ====================
    $topRatedDrivers = $pdo->query("
        SELECT 
            CONCAT(l.nom, ' ', l.prenom) as livreur_nom,
            ROUND(AVG(n.note), 1) as note_moyenne,
            COUNT(n.id_note) as nb_notes
        FROM livreur l
        LEFT JOIN note n ON l.id_livreur = n.id_livreur
        GROUP BY l.id_livreur, l.nom, l.prenom
        ORDER BY note_moyenne DESC
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Administration - Tableau de bord</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dark/ad_css.css">
    <style>
        /* Styles supplémentaires pour les badges de statut */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-attente { background: #f39c12; color: #fff; }
        .badge-acceptee { background: #3498db; color: #fff; }
        .badge-cours { background: #f1c40f; color: #000; }
        .badge-terminee { background: #2ecc71; color: #fff; }
        .badge-annulee { background: #e74c3c; color: #fff; }
        
        /* Grille des statistiques supplémentaires */
        .stats-grid-extras {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        /* Section des tops */
        .tops-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <a href="ad_dashboard.php" style="text-decoration: none;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
                </a>

                <div class="nav-links" id="navLinks">
                    <a href="ad_dashboard.php" class="active">Dashboard</a>
                    <a href="ad_utilisateur.php">Utilisateurs</a>
                    <a href="ad_livreur.php">Livreurs</a>
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
            <!-- ============================================= -->
            <!-- EN-TÊTE DE LA PAGE -->
            <!-- ============================================= -->
            <div class="page-header">
                <h1 class="page-title">Tableau de bord</h1>
                <p class="page-subtitle">Bienvenue, Administrateur - Vue d'ensemble de la plateforme</p>
            </div>

            <!-- ============================================= -->
            <!-- CARTES STATISTIQUES PRINCIPALES -->
            <!-- ============================================= -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Utilisateurs</span>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-change">Total inscrits</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Livreurs</span>
                        <i class="fas fa-truck stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalDrivers; ?></div>
                    <div class="stat-change">Partenaires actifs</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Livraisons</span>
                        <i class="fas fa-box stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalDeliveries; ?></div>
                    <div class="stat-change">Commandes totales</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Chiffre d'affaires</span>
                        <i class="fas fa-chart-line stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalRevenue, 0, ',', ' '); ?> FCFA</div>
                    <div class="stat-change">Livraisons terminées</div>
                </div>
            </div>
            
            <!-- ============================================= -->
            <!-- CARTES STATISTIQUES SUPPLÉMENTAIRES -->
            <!-- ============================================= -->
            <div class="stats-grid-extras">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Commission totale</span>
                        <i class="fas fa-percent stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalCommission, 0, ',', ' '); ?> FCFA</div>
                    <div class="stat-change">Commissions perçues</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">En attente</span>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo $statusCounts['en_attente'] ?? 0; ?></div>
                    <div class="stat-change">Livraisons à traiter</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">En cours</span>
                        <i class="fas fa-spinner stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo $statusCounts['en_cours'] ?? 0; ?></div>
                    <div class="stat-change">Livraisons en cours</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Terminées</span>
                        <i class="fas fa-check-circle stat-icon"></i>
                    </div>
                    <div class="stat-value"><?php echo $statusCounts['terminee'] ?? 0; ?></div>
                    <div class="stat-change">Livraisons terminées</div>
                </div>
            </div>
            
            <!-- ============================================= -->
            <!-- RÉPARTITION DES VÉHICULES (LIVREURS) -->
            <!-- ============================================= -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-motorcycle"></i> Répartition des livreurs par type de véhicule
                    </div>
                    <div class="stat-change">Total : <?php echo $totalDrivers; ?> livreurs</div>
                </div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; padding: 20px;">
                    <?php foreach($vehicleCounts as $type => $count): ?>
                    <div style="flex: 1; min-width: 120px; text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 10px;">
                        <i class="fas fa-<?php echo $type == 'moto' ? 'motorcycle' : ($type == 'tricycle' ? 'tricycle' : ($type == 'camionnette' ? 'van-shuttle' : 'truck')); ?>" style="font-size: 2rem; color: #00D4E8;"></i>
                        <div style="font-size: 1.5rem; font-weight: bold; margin-top: 10px;"><?php echo $count; ?></div>
                        <div style="font-size: 0.8rem; text-transform: capitalize;"><?php echo $type; ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($vehicleCounts)): ?>
                    <div style="padding: 20px; text-align: center;">Aucun livreur enregistré</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ============================================= -->
            <!-- TOP LIVREURS (MEILLEURES NOTES) -->
            <!-- ============================================= -->
            <div class="tops-grid">
                <div class="table-section" style="margin-bottom: 0;">
                    <div class="table-header">
                        <div class="table-title">
                            <i class="fas fa-star"></i> Top 3 des livreurs (meilleures notes)
                        </div>
                    </div>
                    <div style="padding: 15px;">
                        <?php foreach($topRatedDrivers as $index => $driver): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <div>
                                <span style="font-weight: bold; color: #00D4E8;">#<?php echo $index + 1; ?></span>
                                <span style="margin-left: 10px;"><?php echo htmlspecialchars($driver['livreur_nom']); ?></span>
                            </div>
                            <div>
                                <i class="fas fa-star" style="color: #f1c40f;"></i>
                                <span style="font-weight: bold;"><?php echo number_format($driver['note_moyenne'] ?? 0, 1); ?></span>
                                <span style="font-size: 0.7rem;">(<?php echo $driver['nb_notes'] ?? 0; ?> avis)</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($topRatedDrivers)): ?>
                        <div style="text-align: center; padding: 20px;">Aucune note disponible</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ============================================= -->
            <!-- TABLEAU : DERNIERS UTILISATEURS INSCRITS -->
            <!-- ============================================= -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-user-plus"></i> Derniers utilisateurs inscrits
                    </div>
                    <a href="ad_utilisateur.php" class="btn-add" style="text-decoration: none;">
                        <i class="fas fa-eye"></i> Voir tous
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Nom utilisateur</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id_user']); ?></td>
                            <td><?php echo htmlspecialchars($user['mail']); ?></td>
                            <td><?php echo htmlspecialchars($user['nom_utilisateur'] ?? 'Non renseigné'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['date_creation'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recentUsers)): ?>
                        <tr><td colspan="4" style="text-align: center;">Aucun utilisateur enregistré</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================= -->
            <!-- TABLEAU : DERNIÈRES LIVRAISONS -->
            <!-- ============================================= -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-truck"></i> Dernières livraisons
                    </div>
                    <a href="ad_livraison.php" class="btn-add" style="text-decoration: none;">
                        <i class="fas fa-eye"></i> Voir toutes
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Livreur</th>
                            <th>Distance</th>
                            <th>Poids</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentDeliveries as $delivery): ?>
                        <tr>
                            <td>#<?php echo $delivery['id_livraison']; ?></td>
                            <td><?php echo htmlspecialchars($delivery['client_email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($delivery['livreur_nom'] ?? 'Non assigné'); ?></td>
                            <td><?php echo $delivery['distance'] ? number_format($delivery['distance'], 1) . ' km' : '-'; ?></td>
                            <td><?php echo $delivery['poids'] ? number_format($delivery['poids'], 1) . ' kg' : '-'; ?></td>
                            <td><?php echo number_format($delivery['prix'] ?? 0, 0, ',', ' '); ?> FCFA</td>
                            <td>
                                <?php
                                // Affichage du badge de statut avec la couleur appropriée
                                $statusLabels = [
                                    'en_attente' => 'En attente',
                                    'acceptee' => 'Acceptée',
                                    'en_cours' => 'En cours',
                                    'terminee' => 'Terminée',
                                    'annulee' => 'Annulée'
                                ];
                                $statusClass = [
                                    'en_attente' => 'badge-attente',
                                    'acceptee' => 'badge-acceptee',
                                    'en_cours' => 'badge-cours',
                                    'terminee' => 'badge-terminee',
                                    'annulee' => 'badge-annulee'
                                ];
                                $statut = $delivery['statut'];
                                ?>
                                <span class="badge <?php echo $statusClass[$statut] ?? 'badge-attente'; ?>">
                                    <?php echo $statusLabels[$statut] ?? $statut; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($delivery['date_creation'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recentDeliveries)): ?>
                        <tr><td colspan="8" style="text-align: center;">Aucune livraison enregistrée</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================= -->
            <!-- TABLEAU : DERNIERS LIVREURS INSCRITS -->
            <!-- ============================================= -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-id-card"></i> Derniers livreurs inscrits
                    </div>
                    <a href="ad_livreur.php" class="btn-add" style="text-decoration: none;">
                        <i class="fas fa-eye"></i> Voir tous
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Téléphone</th>
                            <th>Type véhicule</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentDrivers as $driver): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($driver['nom']); ?></td>
                            <td><?php echo htmlspecialchars($driver['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($driver['numero']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($driver['type_vehicule'])); ?></td>
                            <td><?php echo htmlspecialchars($driver['mail'] ?? 'Non renseigné'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recentDrivers)): ?>
                        <tr><td colspan="5" style="text-align: center;">Aucun livreur enregistré</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ============================================= -->
    <!-- SCRIPT POUR LE MENU MOBILE UNIQUEMENT -->
    <!-- ============================================= -->
    <script>
    // Ce script gère uniquement l'ouverture/fermeture du menu sur mobile
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    const menuOverlay = document.getElementById('menuOverlay');
    
    if(menuToggle && navLinks && menuOverlay) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });
        
        menuOverlay.addEventListener('click', () => {
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
        });
    }
    </script>
</body>
</html>