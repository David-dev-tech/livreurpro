<?php
session_start();

// ── Connexion BDD ──────────────────────────────────────────
$host = 'localhost'; $dbname = 'livpro';
$username = 'root';  $password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur BDD : " . $e->getMessage()); }

// ── Auth : récupérer le livreur connecté ──────────────────
// On accepte ?id=X en GET pour la démo ; en prod, utiliser la session.
$id_livreur = isset($_GET['id']) ? (int)$_GET['id']
            : (isset($_SESSION['id_livreur']) ? (int)$_SESSION['id_livreur'] : 0);

if (!$id_livreur) {
    // Aucun livreur authentifié → redirige ou affiche erreur
    die('<p style="color:#f87171;font-family:sans-serif;padding:40px;">
         Accès refusé. <a href="liv_login.php" style="color:#00D4E8;">Se connecter</a></p>');
}

// Stocker dans session pour les autres pages
$_SESSION['id_livreur'] = $id_livreur;

// ── Données du livreur ────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM livreur WHERE id_livreur = ?");
$stmt->execute([$id_livreur]);
$livreur = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$livreur) die('<p style="color:#f87171;font-family:sans-serif;padding:40px;">Livreur introuvable.</p>');

// ── Statistiques ──────────────────────────────────────────
$stats = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(statut='en_attente')  AS en_attente,
        SUM(statut='en_cours')    AS en_cours,
        SUM(statut='terminee')    AS terminees,
        SUM(statut='annulee')     AS annulees,
        COALESCE(SUM(CASE WHEN statut='terminee' THEN prix END), 0) AS gains
    FROM livraison WHERE id_livreur = ?
");
$stats->execute([$id_livreur]);
$s = $stats->fetch(PDO::FETCH_ASSOC);

// ── 5 dernières livraisons ────────────────────────────────
$recents = $pdo->prepare("
    SELECT l.*, u.nom_utilisateur
    FROM livraison l
    LEFT JOIN utilisateur u ON l.id_user = u.id_user
    WHERE l.id_livreur = ?
    ORDER BY l.date_creation DESC LIMIT 5
");
$recents->execute([$id_livreur]);
$dernières = $recents->fetchAll(PDO::FETCH_ASSOC);

// ── Note moyenne ──────────────────────────────────────────
$noteStmt = $pdo->prepare("SELECT AVG(note) AS moy, COUNT(*) AS nb FROM note WHERE id_livreur = ?");
$noteStmt->execute([$id_livreur]);
$noteData = $noteStmt->fetch(PDO::FETCH_ASSOC);
$moyenne  = $noteData['moy'] ? round($noteData['moy'], 1) : null;

// ── Helpers ───────────────────────────────────────────────
function statutLabel(string $s): string {
    return match($s) {
        'en_attente' => 'En attente', 'acceptee' => 'Acceptée',
        'en_cours'   => 'En cours',   'terminee' => 'Terminée',
        'annulee'    => 'Annulée',    default    => ucfirst($s),
    };
}
function statutClass(string $s): string {
    return match($s) {
        'en_attente' => 'status-warning', 'acceptee'  => 'status-info',
        'en_cours'   => 'status-primary', 'terminee'  => 'status-success',
        'annulee'    => 'status-danger',  default     => '',
    };
}
$initiales = strtoupper(mb_substr($livreur['prenom'], 0, 1) . mb_substr($livreur['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivreurPro | Mon Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dark/liv_css.css">
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="header">
    <div class="container">
        <div class="nav-container">
            <div class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></div>

            <a href="liv_dashboard.php?id=<?php echo $id_livreur; ?>" style="text-decoration:none;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
            </a>

            <div class="nav-links" id="navLinks">
                <a href="liv_dashboard.php" class="active">Dashboard</a>
                <a href="liv_livraison.php">Mes livraisons</a>
                <a href="liv_compte.php">Mon profil</a>
                <div class="user-info">
                    <i class="fas fa-motorcycle"></i>
                    <span><?php echo htmlspecialchars($livreur['prenom']); ?></span>
                </div>
            </div>
        </div>
    </div>
</header>
<div class="menu-overlay" id="menuOverlay"></div>

<!-- ══ MAIN ══ -->
<main class="main">
    <div class="container">

        <!-- Bannière de bienvenue -->
        <div class="welcome-banner">
            <div class="wb-icon"><i class="fas fa-motorcycle"></i></div>
            <div>
                <h2>Bonjour, <?php echo htmlspecialchars($livreur['prenom'] . ' ' . $livreur['nom']); ?> 👋</h2>
                <p>Voici un aperçu de votre activité · Véhicule :
                    <strong style="color:var(--accent)"><?php echo ucfirst($livreur['type_vehicule']); ?></strong>
                    <?php if ($moyenne): ?>
                        &nbsp;·&nbsp; Note moyenne :
                        <strong style="color:var(--warning)"><?php echo $moyenne; ?> / 5
                            <i class="fas fa-star"></i></strong>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Cartes stats -->
        <div class="stats-grid">
            <div class="stat-card accent-card">
                <span class="stat-icon" style="color:var(--accent)"><i class="fas fa-box"></i></span>
                <span class="stat-label">Total livraisons</span>
                <span class="stat-value"><?php echo $s['total']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--warning)"><i class="fas fa-clock"></i></span>
                <span class="stat-label">En attente</span>
                <span class="stat-value" style="color:var(--warning)"><?php echo $s['en_attente']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--accent)"><i class="fas fa-truck"></i></span>
                <span class="stat-label">En cours</span>
                <span class="stat-value" style="color:var(--accent)"><?php echo $s['en_cours']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--success)"><i class="fas fa-check-circle"></i></span>
                <span class="stat-label">Terminées</span>
                <span class="stat-value" style="color:var(--success)"><?php echo $s['terminees']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--danger)"><i class="fas fa-times-circle"></i></span>
                <span class="stat-label">Annulées</span>
                <span class="stat-value" style="color:var(--danger)"><?php echo $s['annulees']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--success)"><i class="fas fa-wallet"></i></span>
                <span class="stat-label">Gains (FCFA)</span>
                <span class="stat-value" style="color:var(--success)">
                    <?php echo number_format($s['gains'], 0, ',', ' '); ?>
                </span>
            </div>
        </div>

        <!-- Grille dashboard -->
        <div class="dashboard-grid">

            <!-- Dernières livraisons -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-history"></i> Dernières livraisons</div>
                    <a href="liv_livraisons.php?id=<?php echo $id_livreur; ?>" class="btn-secondary" style="font-size:.8rem;padding:6px 12px;">Voir tout</a>
                </div>
                <div style="padding: 6px 16px;">
                    <?php if (empty($dernières)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>Aucune livraison pour l'instant.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($dernières as $liv): ?>
                            <div class="recent-item">
                                <div class="ri-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="ri-info">
                                    <div class="ri-title">
                                        <?php echo htmlspecialchars($liv['adresse_depot']); ?>
                                    </div>
                                    <div class="ri-sub">
                                        <?php echo htmlspecialchars($liv['nom_utilisateur'] ?? 'Client inconnu'); ?>
                                        &nbsp;·&nbsp;
                                        <?php echo $liv['prix'] ? number_format($liv['prix'], 0, ',', ' ') . ' FCFA' : '-'; ?>
                                    </div>
                                </div>
                                <span class="status-badge <?php echo statutClass($liv['statut']); ?>">
                                    <?php echo statutLabel($liv['statut']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Infos rapides + note -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- Infos profil -->
                <div class="card">
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
                        <div class="profile-avatar" style="width:56px;height:56px;font-size:1.4rem;margin:0;">
                            <?php echo $initiales; ?>
                        </div>
                        <div>
                            <div style="font-family:var(--font-display);font-weight:900;font-size:1.15rem;">
                                <?php echo htmlspecialchars($livreur['nom'] . ' ' . $livreur['prenom']); ?>
                            </div>
                            <div style="color:var(--text-muted);font-size:.8rem;">
                                <?php echo htmlspecialchars($livreur['numero']); ?>
                            </div>
                        </div>
                        <a href="liv_profil.php?id=<?php echo $id_livreur; ?>"
                           style="margin-left:auto;font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;">
                            Voir profil →
                        </a>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:.85rem;">
                        <div>
                            <span style="color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Véhicule</span>
                            <p style="color:var(--accent);font-weight:600;margin-top:2px;"><?php echo ucfirst($livreur['type_vehicule']); ?></p>
                        </div>
                        <div>
                            <span style="color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Email</span>
                            <p style="margin-top:2px;"><?php echo htmlspecialchars($livreur['mail'] ?? 'Non renseigné'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Note moyenne -->
                <div class="card" style="text-align:center;">
                    <div style="font-family:var(--font-display);font-weight:700;font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:12px;">
                        <i class="fas fa-star" style="color:var(--warning);"></i> Ma note moyenne
                    </div>
                    <?php if ($moyenne): ?>
                        <div class="rating-big"><?php echo $moyenne; ?></div>
                        <div style="display:flex;justify-content:center;gap:4px;margin:8px 0;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($moyenne) ? 'star filled' : 'star'; ?>"
                                   style="color:<?php echo $i <= round($moyenne) ? 'var(--warning)' : 'var(--border)'; ?>;font-size:1.2rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-count"><?php echo $noteData['nb']; ?> avis</div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:16px 0;">
                            <i class="fas fa-star" style="font-size:1.6rem;color:var(--border);"></i>
                            <p style="margin-top:8px;">Aucun avis reçu pour l'instant.</p>
                        </div>
                    <?php endif; ?>
                    <a href="liv_profil.php?id=<?php echo $id_livreur; ?>#avis"
                       style="display:inline-block;margin-top:12px;font-size:.8rem;color:var(--accent);text-decoration:none;font-weight:600;">
                        Voir tous les avis →
                    </a>
                </div>

            </div>
        </div><!-- /dashboard-grid -->

    </div>
</main>

<script>
document.getElementById('menuToggle').addEventListener('click', function () {
    document.getElementById('navLinks').classList.toggle('active');
    document.getElementById('menuOverlay').classList.toggle('active');
});
document.getElementById('menuOverlay').addEventListener('click', function () {
    document.getElementById('navLinks').classList.remove('active');
    this.classList.remove('active');
});
</script>
</body>
</html>