<?php
session_start();

// =============================================
// INCLUSION DU FICHIER DE CONNEXION
// =============================================
require_once '../config/config.php';

// =============================================
// RÉCUPÉRATION DU LIVREUR CONNECTÉ
// =============================================
$id_livreur = isset($_GET['id']) ? (int)$_GET['id']
            : (isset($_SESSION['id_livreur']) ? (int)$_SESSION['id_livreur'] : 0);

if (!$id_livreur) {
    die('<p style="color:#f87171;padding:40px;font-family:sans-serif;">
         Accès refusé. <a href="liv_login.php" style="color:#00D4E8;">Se connecter</a></p>');
}

$_SESSION['id_livreur'] = $id_livreur;

// =============================================
// DONNÉES DU LIVREUR
// =============================================
$stmtL = $pdo->prepare("SELECT * FROM livreur WHERE id_livreur = ?");
$stmtL->execute([$id_livreur]);
$livreur = $stmtL->fetch(PDO::FETCH_ASSOC);

if (!$livreur) {
    die('<p style="color:#f87171;padding:40px;font-family:sans-serif;">Livreur introuvable.</p>');
}

// =============================================
// MISE À JOUR DU PROFIL
// =============================================
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $mail    = trim($_POST['mail'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $numero  = trim($_POST['numero'] ?? '');

    try {
        $pdo->prepare("UPDATE livreur SET mail = ?, adresse = ?, numero = ? WHERE id_livreur = ?")
            ->execute([$mail ?: null, $adresse ?: null, $numero, $id_livreur]);
        
        // Rafraîchir les données
        $stmtL->execute([$id_livreur]);
        $livreur = $stmtL->fetch(PDO::FETCH_ASSOC);
        
        $flash = ['type' => 'success', 'msg' => 'Profil mis à jour avec succès.'];
    } catch (PDOException $e) {
        $flash = ['type' => 'error', 'msg' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
    }
}

// =============================================
// NOTES & AVIS
// =============================================
$notesStmt = $pdo->prepare("SELECT * FROM note WHERE id_livreur = ? ORDER BY id_note DESC");
$notesStmt->execute([$id_livreur]);
$notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

$avgStmt = $pdo->prepare("SELECT AVG(note) AS moy, COUNT(*) AS nb FROM note WHERE id_livreur = ?");
$avgStmt->execute([$id_livreur]);
$avgData = $avgStmt->fetch(PDO::FETCH_ASSOC);
$moyenne = $avgData['moy'] ? round($avgData['moy'], 1) : null;

// Répartition des notes
$repartition = array_fill(1, 5, 0);
foreach ($notes as $n) {
    $repartition[(int)$n['note']]++;
}
$totalNotes = count($notes);

// =============================================
// STATISTIQUES LIVRAISONS
// =============================================
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN statut='terminee' THEN 1 ELSE 0 END) AS terminees,
        COALESCE(SUM(CASE WHEN statut='terminee' THEN prix ELSE 0 END), 0) AS gains,
        COALESCE(SUM(CASE WHEN statut='terminee' THEN distance ELSE 0 END), 0) AS km
    FROM livraison WHERE id_livreur = ?
");
$statsStmt->execute([$id_livreur]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$initiales = strtoupper(mb_substr($livreur['prenom'], 0, 1) . mb_substr($livreur['nom'], 0, 1));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivreurPro | Mon Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dark/liv_css.css">
    <style>
        .tab-nav {
            display: flex; gap: 4px;
            border-bottom: 1px solid var(--border);
            padding: 0 20px;
        }
        .tab-btn {
            background: none; border: none;
            color: var(--text-muted);
            font-family: var(--font-body); font-weight: 600; font-size: .875rem;
            padding: 12px 18px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
            margin-bottom: -1px;
        }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
        .tab-pane { display: none; padding: 24px; }
        .tab-pane.active { display: block; }

        /* Rating bar */
        .rating-bar-row {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 8px;
        }
        .rating-bar-label {
            font-size: .8rem; color: var(--text-muted);
            width: 18px; text-align: right; flex-shrink: 0;
        }
        .rating-bar-track {
            flex: 1; height: 7px;
            background: var(--surface2); border-radius: 4px; overflow: hidden;
        }
        .rating-bar-fill {
            height: 100%; border-radius: 4px;
            background: var(--warning);
            transition: width .5s ease;
        }
        .rating-bar-count {
            font-size: .78rem; color: var(--text-muted);
            width: 24px; text-align: right; flex-shrink: 0;
        }

        /* Form inside tab */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label {
            font-family: var(--font-display); font-weight: 700;
            font-size: .72rem; text-transform: uppercase; letter-spacing: .06em;
            color: var(--text-muted);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: var(--radius);
            padding: 9px 12px;
            font-family: var(--font-body); font-size: .875rem;
            outline: none; transition: border-color .2s;
        }
        .form-group input:focus,
        .form-group textarea:focus { border-color: var(--accent); }
        .form-group input[readonly] { opacity: .55; cursor: not-allowed; }
        .form-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
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
                <a href="liv_dashboard.php">Dashboard</a>
                <a href="liv_livraison.php">Mes livraisons</a>
                <a href="liv_compte.php" class="active">Mon profil</a>
                <div class="user-info">
                    <i class="fas fa-motorcycle"></i>
                    <span><?php echo htmlspecialchars($livreur['prenom']); ?></span>
                </div>
            </div>
        </div>
    </div>
</header>
<div class="menu-overlay" id="menuOverlay"></div>

<main class="main">
    <div class="container">

        <div class="page-header">
            <h1 class="page-title">Mon <span>Profil</span></h1>
            <p class="page-subtitle">Informations personnelles, statistiques et avis clients</p>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <!-- ── Carte identité + stats ── -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:28px;">
            <div class="stat-card" style="grid-column:span 4 / span 4;
                 background:linear-gradient(120deg,rgba(0,212,232,.1) 0%,transparent 60%);
                 border-color:rgba(0,212,232,.3);
                 flex-direction:row;align-items:center;gap:24px;flex-wrap:wrap;">
                <div class="profile-avatar"><?php echo $initiales; ?></div>
                <div style="flex:1;min-width:180px;">
                    <div class="profile-name">
                        <?php echo htmlspecialchars($livreur['nom'] . ' ' . $livreur['prenom']); ?>
                    </div>
                    <div class="profile-meta">
                        <i class="fas fa-motorcycle" style="color:var(--accent)"></i>
                        <?php echo ucfirst($livreur['type_vehicule']); ?>
                        &nbsp;·&nbsp;
                        <?php echo htmlspecialchars($livreur['numero']); ?>
                        <?php if ($livreur['mail']): ?>
                            &nbsp;·&nbsp; <?php echo htmlspecialchars($livreur['mail']); ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($moyenne): ?>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" style="font-size:.95rem;color:<?php echo $i <= round($moyenne) ? 'var(--warning)' : 'var(--border)'; ?>"></i>
                            <?php endfor; ?>
                            <span style="font-family:var(--font-display);font-weight:900;font-size:1.1rem;color:var(--warning);"><?php echo $moyenne; ?></span>
                            <span style="font-size:.78rem;color:var(--text-muted);">(<?php echo $totalNotes; ?> avis)</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card">
                <span class="stat-icon" style="color:var(--accent)"><i class="fas fa-box"></i></span>
                <span class="stat-label">Total livraisons</span>
                <span class="stat-value"><?php echo $stats['total']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--success)"><i class="fas fa-check-circle"></i></span>
                <span class="stat-label">Terminées</span>
                <span class="stat-value" style="color:var(--success)"><?php echo $stats['terminees']; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--success)"><i class="fas fa-wallet"></i></span>
                <span class="stat-label">Gains FCFA</span>
                <span class="stat-value" style="color:var(--success);font-size:1.5rem;">
                    <?php echo number_format($stats['gains'], 0, ',', ' '); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:var(--info)"><i class="fas fa-road"></i></span>
                <span class="stat-label">Km parcourus</span>
                <span class="stat-value" style="color:var(--info);font-size:1.5rem;">
                    <?php echo number_format($stats['km'], 0, ',', ' '); ?>
                </span>
            </div>
        </div>

        <!-- ── Onglets ── -->
        <div class="table-section" id="avis">
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('infos', this)">
                    <i class="fas fa-id-card"></i> Informations
                </button>
                <button class="tab-btn" onclick="switchTab('avis', this)">
                    <i class="fas fa-star"></i> Avis clients
                    <?php if ($totalNotes): ?>
                        <span style="background:var(--warning);color:#1C1F24;border-radius:10px;padding:1px 7px;font-size:.7rem;margin-left:4px;font-weight:700;"><?php echo $totalNotes; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- ═══ Onglet Infos ═══ -->
            <div class="tab-pane active" id="tab-infos">
                <form method="POST">
                    <input type="hidden" name="update_profil" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" value="<?php echo htmlspecialchars($livreur['nom']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" value="<?php echo htmlspecialchars($livreur['prenom']); ?>" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Âge</label>
                            <input type="number" value="<?php echo $livreur['age'] ?? ''; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Sexe</label>
                            <input type="text" value="<?php echo $livreur['sex'] === 'M' ? 'Masculin' : 'Féminin'; ?>" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Numéro de téléphone <span style="color:var(--accent)">*</span></label>
                            <input type="tel" name="numero" value="<?php echo htmlspecialchars($livreur['numero']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="mail" value="<?php echo htmlspecialchars($livreur['mail'] ?? ''); ?>" placeholder="exemple@email.com">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Numéro CNI</label>
                            <input type="text" value="<?php echo htmlspecialchars($livreur['numero_cni'] ?? '-'); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Carte grise</label>
                            <input type="text" value="<?php echo htmlspecialchars($livreur['carte_grise'] ?? '-'); ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label>Adresse</label>
                        <textarea name="adresse" rows="3" placeholder="Adresse complète"><?php echo htmlspecialchars($livreur['adresse'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label>Type de véhicule</label>
                        <input type="text" value="<?php echo ucfirst($livreur['type_vehicule']); ?>" readonly>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>

            <!-- ═══ Onglet Avis ═══ -->
            <div class="tab-pane" id="tab-avis">
                <?php if (empty($notes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p>Vous n'avez pas encore reçu d'avis clients.</p>
                    </div>
                <?php else: ?>
                    <div style="display:grid;grid-template-columns:200px 1fr;gap:32px;align-items:start;">

                        <!-- Résumé gauche -->
                        <div style="text-align:center;">
                            <div class="rating-big"><?php echo $moyenne; ?></div>
                            <div style="display:flex;justify-content:center;gap:4px;margin:8px 0;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color:<?php echo $i <= round($moyenne) ? 'var(--warning)' : 'var(--border)'; ?>;font-size:1.3rem;"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-count"><?php echo $totalNotes; ?> avis</div>

                            <div style="margin-top:20px;">
                                <?php for ($star = 5; $star >= 1; $star--): ?>
                                    <div class="rating-bar-row">
                                        <span class="rating-bar-label"><?php echo $star; ?></span>
                                        <i class="fas fa-star" style="color:var(--warning);font-size:.7rem;flex-shrink:0;"></i>
                                        <div class="rating-bar-track">
                                            <div class="rating-bar-fill" style="width:<?php echo $totalNotes ? round($repartition[$star]/$totalNotes*100) : 0; ?>%"></div>
                                        </div>
                                        <span class="rating-bar-count"><?php echo $repartition[$star]; ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Liste des avis -->
                        <div>
                            <?php foreach ($notes as $n): ?>
                                <div class="note-card">
                                    <div class="note-top">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star star <?php echo $i <= $n['note'] ? 'filled' : ''; ?>"
                                                   style="color:<?php echo $i <= $n['note'] ? 'var(--warning)' : 'var(--border)'; ?>;font-size:.9rem;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span style="font-family:var(--font-display);font-weight:900;font-size:1.1rem;color:var(--warning);">
                                            <?php echo $n['note']; ?>/5
                                        </span>
                                    </div>
                                    <?php if ($n['impression']): ?>
                                        <div class="note-impression">
                                            "<?php echo htmlspecialchars($n['impression']); ?>"
                                        </div>
                                    <?php else: ?>
                                        <div class="note-impression" style="opacity:.4;">Pas de commentaire.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /table-section -->
    </div>
</main>

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// Si l'URL contient #avis, activer l'onglet avis automatiquement
if (window.location.hash === '#avis') {
    const avisBtn = document.querySelectorAll('.tab-btn')[1];
    if (avisBtn) switchTab('avis', avisBtn);
}

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