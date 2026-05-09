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
// TRAITEMENT DES ACTIONS (Accepter, Refuser...)
// =============================================
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_livraison'])) {
    $id_liv = (int)$_POST['id_livraison'];
    $action = $_POST['action'];

    $check = $pdo->prepare("SELECT * FROM livraison WHERE id_livraison = ?");
    $check->execute([$id_liv]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($action === 'accepter') {
            $pdo->prepare("UPDATE livraison SET id_livreur = ?, statut = 'acceptee', date_validation = NOW() WHERE id_livraison = ?")
                ->execute([$id_livreur, $id_liv]);
            $flash = ['type' => 'success', 'msg' => 'Livraison #' . $id_liv . ' acceptée.'];
        } elseif ($action === 'refuser') {
            $pdo->prepare("UPDATE livraison SET statut = 'refusee' WHERE id_livraison = ?")
                ->execute([$id_liv]);
            $flash = ['type' => 'error', 'msg' => 'Livraison #' . $id_liv . ' refusée.'];
        } elseif ($action === 'demarrer' && $row['statut'] === 'acceptee' && $row['id_livreur'] == $id_livreur) {
            $pdo->prepare("UPDATE livraison SET statut = 'en_cours' WHERE id_livraison = ?")
                ->execute([$id_liv]);
            $flash = ['type' => 'success', 'msg' => 'Livraison #' . $id_liv . ' démarrée.'];
        } elseif ($action === 'terminer' && $row['statut'] === 'en_cours' && $row['id_livreur'] == $id_livreur) {
            $pdo->prepare("UPDATE livraison SET statut = 'terminee', date_validation = NOW() WHERE id_livraison = ?")
                ->execute([$id_liv]);
            if ($row['prix']) {
                $commission = round($row['prix'] * 0.10, 2);
                $pdo->prepare("INSERT INTO commission (id_livraison, montant) VALUES (?, ?)")
                    ->execute([$id_liv, $commission]);
            }
            $flash = ['type' => 'success', 'msg' => 'Livraison #' . $id_liv . ' terminée.'];
        } elseif ($action === 'annuler' && in_array($row['statut'], ['acceptee', 'en_cours']) && $row['id_livreur'] == $id_livreur) {
            $pdo->prepare("UPDATE livraison SET statut = 'annulee' WHERE id_livraison = ?")
                ->execute([$id_liv]);
            $flash = ['type' => 'error', 'msg' => 'Livraison #' . $id_liv . ' annulée.'];
        }
    }
}

// =============================================
// LISTE DES LIVRAISONS
// =============================================
$filterStatut = isset($_GET['statut']) ? $_GET['statut'] : '';
$validStatuts = ['', 'en_attente', 'acceptee', 'en_cours', 'terminee', 'annulee', 'refusee'];
if (!in_array($filterStatut, $validStatuts)) $filterStatut = '';

$sql = "
    SELECT l.*, u.nom_utilisateur, u.mail AS mail_user
    FROM livraison l
    LEFT JOIN utilisateur u ON l.id_user = u.id_user
    WHERE (l.id_livreur = :id OR (l.statut = 'en_attente' AND l.id_livreur IS NULL))
";
$params = [':id' => $id_livreur];

if ($filterStatut) {
    $sql .= " AND l.statut = :statut";
    $params[':statut'] = $filterStatut;
}
$sql .= " ORDER BY l.date_creation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonctions utilitaires
function statutLabel(string $s): string {
    return match($s) {
        'en_attente' => 'En attente', 'acceptee' => 'Acceptée',
        'en_cours' => 'En cours', 'terminee' => 'Terminée',
        'annulee' => 'Annulée', 'refusee' => 'Refusée',
        default => ucfirst($s),
    };
}

function statutClass(string $s): string {
    return match($s) {
        'en_attente' => 'status-warning', 'acceptee' => 'status-info',
        'en_cours' => 'status-primary', 'terminee' => 'status-success',
        'annulee', 'refusee' => 'status-danger',
        default => '',
    };
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivreurPro | Mes Livraisons</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="../css/dark/liv_css.css">
    <style>
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.85);
        backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        z-index: 200; opacity: 0; pointer-events: none;
        transition: opacity .25s ease; padding: 20px;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        width: min(900px, 95vw);
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        transform: translateY(20px);
        transition: transform .25s ease;
        box-shadow: 0 24px 60px rgba(0,0,0,.7);
    }
    .modal-overlay.open .modal-box { transform: translateY(0); }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 16px;
        border-bottom: 1px solid var(--border);
    }
    .modal-title {
        font-family: var(--font-display);
        font-weight: 700;
        font-style: italic;
        font-size: 1.4rem;
        color: var(--accent);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modal-close {
        background: none;
        border: 1px solid var(--border);
        color: var(--text-muted);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        transition: all var(--transition);
    }
    .modal-close:hover { border-color: var(--danger); color: var(--danger); background: var(--danger-bg); }
    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px 24px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px 24px;
        margin-bottom: 20px;
    }
    .info-item {
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
    }
    .info-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--text-muted);
        display: block;
        margin-bottom: 4px;
    }
    .info-value {
        font-size: .9rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    .full-width { grid-column: 1 / -1; }
    .map-container {
        border-radius: var(--radius-md);
        overflow: hidden;
        border: 1px solid var(--border);
        margin-bottom: 20px;
        position: relative;
        background: var(--bg-base);
        min-height: 350px;
    }
    #modalMap {
        width: 100%;
        height: 350px;
        background: var(--bg-base);
    }
    .map-loader {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 17, 23, 0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        z-index: 10;
        border-radius: var(--radius-md);
    }
    .map-loader.hidden { display: none; }
    .map-loader i { font-size: 32px; color: var(--accent); animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .map-legend {
        display: flex;
        gap: 20px;
        padding: 10px 14px;
        background: rgba(0,0,0,.3);
        border-top: 1px solid var(--border);
        font-size: .75rem;
    }
    .legend-dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    /* ── FOOTER MODAL ── */
    .modal-footer {
        padding: 16px 24px 24px;
        border-top: 1px solid var(--border);
    }
    .action-buttons {
        display: flex;
        gap: 16px;
    }
    .btn-modal {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 15px 20px;
        border-radius: var(--radius-md);
        font-family: var(--font-display);
        font-weight: 900;
        font-style: italic;
        font-size: 1.05rem;
        letter-spacing: .04em;
        cursor: pointer;
        transition: all .2s ease;
        border: none;
        text-transform: uppercase;
    }
    .btn-accept {
        background: #22C55E;
        color: #0F1117;
        box-shadow: 0 4px 20px rgba(34,197,94,.35);
    }
    .btn-accept:hover {
        background: #16a34a;
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(34,197,94,.5);
    }
    .btn-refuse {
        background: #F87171;
        color: #0F1117;
        box-shadow: 0 4px 20px rgba(248,113,113,.35);
    }
    .btn-refuse:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(248,113,113,.5);
    }
    .btn-modal i { font-size: 1.1rem; }

    @media (max-width: 768px) {
        .menu-toggle { display: flex; }
        .nav-links {
            position: fixed;
            top: 68px; left: -100%;
            width: 280px;
            height: calc(100vh - 68px);
            background: var(--bg-card);
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
            transition: left 0.3s;
            z-index: 95;
        }
        .nav-links.active { left: 0; }
        .info-grid { grid-template-columns: 1fr; }
        #modalMap { height: 250px; }
        .action-buttons { flex-direction: column; }
    }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <div class="nav-container">
            <div class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></div>
            <a href="liv_dashboard.php?id=<?php echo $id_livreur; ?>" style="text-decoration:none;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;">L.Pro</div>
            </a>
            <div class="nav-links" id="navLinks">
                <a href="liv_dashboard.php">Dashboard</a>
                <a href="liv_livraison.php" class="active">Mes livraisons</a>
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

<main class="main">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Mes <span>Livraisons</span></h1>
            <p class="page-subtitle">Gérez et suivez toutes vos livraisons en temps réel</p>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="table-section">
            <div class="table-header">
                <div class="table-title"><i class="fas fa-list"></i> Liste des livraisons (<?php echo count($livraisons); ?>)</div>
            </div>

            <div class="filters-bar">
                <input type="text" id="searchInput" placeholder="Rechercher par client, adresse…" oninput="filterTable()">
                <select id="filterStatut" onchange="location.href='liv_livraisons.php?id=<?php echo $id_livreur; ?>&statut='+this.value">
                    <option value="" <?php echo !$filterStatut ? 'selected' : ''; ?>>Tous les statuts</option>
                    <option value="en_attente" <?php echo $filterStatut==='en_attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="acceptee" <?php echo $filterStatut==='acceptee' ? 'selected' : ''; ?>>Acceptée</option>
                    <option value="en_cours" <?php echo $filterStatut==='en_cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="terminee" <?php echo $filterStatut==='terminee' ? 'selected' : ''; ?>>Terminée</option>
                    <option value="annulee" <?php echo $filterStatut==='annulee' ? 'selected' : ''; ?>>Annulée</option>
                    <option value="refusee" <?php echo $filterStatut==='refusee' ? 'selected' : ''; ?>>Refusée</option>
                </select>
            </div>

            <div class="table-scroll">
                <table id="livraisonsTable">
                    <thead>
                        <tr>
                            <th>ID</th><th>Client</th><th>Ramassage</th><th>Dépôt</th>
                            <th>Distance</th><th>Poids</th><th>Prix</th><th>Véhicule</th>
                            <th>Statut</th><th>Date</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($livraisons)): ?>
                            <tr><td colspan="11"><div class="empty-state"><i class="fas fa-box-open"></i><p>Aucune livraison trouvée.</p></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($livraisons as $liv): ?>
                            <tr>
                                <td><strong>#<?php echo $liv['id_livraison']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($liv['nom_utilisateur'] ?? 'N/A'); ?><br>
                                    <small style="color:var(--text-muted)"><?php echo htmlspecialchars($liv['mail_user'] ?? ''); ?></small>
                                </td>
                                <td title="<?php echo htmlspecialchars($liv['adresse_ramassage']); ?>">
                                    <span class="addr"><?php echo htmlspecialchars($liv['adresse_ramassage']); ?></span>
                                </td>
                                <td title="<?php echo htmlspecialchars($liv['adresse_depot']); ?>">
                                    <span class="addr"><?php echo htmlspecialchars($liv['adresse_depot']); ?></span>
                                </td>
                                <td><?php echo $liv['distance'] ? number_format($liv['distance'], 1).' km' : '-'; ?></td>
                                <td><?php echo $liv['poids'] ? number_format($liv['poids'], 2).' kg' : '-'; ?></td>
                                <td><?php echo $liv['prix'] ? number_format($liv['prix'],0,',',' ').' FCFA' : '-'; ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($liv['type_vehicule'] ?? '-')); ?></td>
                                <td><span class="status-badge <?php echo statutClass($liv['statut']); ?>"><?php echo statutLabel($liv['statut']); ?></span></td>
                                <td style="white-space:nowrap;font-size:.8rem;color:var(--text-muted);"><?php echo $liv['date_creation'] ? date('d/m/Y H:i', strtotime($liv['date_creation'])) : '-'; ?></td>
                                <td style="white-space:nowrap;">
                                    <button class="btn-small" onclick='voirDetails(<?php echo json_encode($liv); ?>)' title="Voir détails">
                                        <i class="fas fa-eye"></i> Détails
                                    </button>

                                    <!-- ACCEPTER -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_livraison" value="<?php echo $liv['id_livraison']; ?>">
                                        <input type="hidden" name="action" value="accepter">
                                        <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:#22C55E;color:#0F1117;border:none;border-radius:6px;font-weight:700;font-size:.8rem;cursor:pointer;">
                                            <i class="fas fa-check"></i> Accepter
                                        </button>
                                    </form>

                                    <!-- REFUSER -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_livraison" value="<?php echo $liv['id_livraison']; ?>">
                                        <input type="hidden" name="action" value="refuser">
                                        <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:#F87171;color:#0F1117;border:none;border-radius:6px;font-weight:700;font-size:.8rem;cursor:pointer;">
                                            <i class="fas fa-times"></i> Refuser
                                        </button>
                                    </form>

                                    <?php if ($liv['statut'] === 'acceptee' && $liv['id_livreur'] == $id_livreur): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id_livraison" value="<?php echo $liv['id_livraison']; ?>">
                                            <input type="hidden" name="action" value="demarrer">
                                            <button type="submit" class="btn-small btn-success"><i class="fas fa-play"></i> Démarrer</button>
                                        </form>
                                    <?php elseif ($liv['statut'] === 'en_cours' && $liv['id_livreur'] == $id_livreur): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id_livraison" value="<?php echo $liv['id_livraison']; ?>">
                                            <input type="hidden" name="action" value="terminer">
                                            <button type="submit" class="btn-small btn-success"><i class="fas fa-flag-checkered"></i> Terminer</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- =============================================
     MODALE AVEC CARTE + BOUTONS ACCEPTER/REFUSER
     ============================================= -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">

        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-route"></i>
                Itinéraire de livraison #<span id="modalId"></span>
            </div>
            <button class="modal-close" onclick="fermerModal()"><i class="fas fa-times"></i></button>
        </div>

        <div class="modal-body">
            <div class="info-grid" id="modalInfos"></div>
            <div class="map-container">
                <div id="modalMap"></div>
                <div class="map-loader" id="mapLoader">
                    <i class="fas fa-spinner fa-pulse"></i>
                    <span>Chargement de la carte...</span>
                </div>
                <div class="map-legend">
                    <span><span class="legend-dot" style="background:#22C55E;"></span> Ramassage</span>
                    <span><span class="legend-dot" style="background:#F87171;"></span> Dépôt</span>
                    <span><span class="legend-dot" style="background:#00D4E8;"></span> Itinéraire</span>
                </div>
            </div>
        </div>

        <!-- ── FOOTER : boutons Accepter / Refuser toujours visibles si en_attente ── -->
        <div class="modal-footer" id="modalFooter"></div>

    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let mapInstance = null;
let currentMarkers = null;
let currentRoute = null;

const PROXY_URL = '../models/proxy.php';

async function geocodeAddress(address) {
    try {
        const url = `${PROXY_URL}?action=search&q=${encodeURIComponent(address + ', Cameroun')}&limit=1`;
        const response = await fetch(url);
        const data = await response.json();
        if (data && data.length > 0) {
            return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon), name: data[0].display_name };
        }
    } catch(e) { console.error('Erreur géocodage:', e); }
    return null;
}

async function getRoute(startLat, startLng, endLat, endLng) {
    try {
        const url = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.routes && data.routes[0]) return data.routes[0].geometry;
    } catch(e) { console.error('Erreur routage:', e); }
    return null;
}

function createIcon(color, letter) {
    return L.divIcon({
        html: `<div style="background:${color};width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 2px 10px rgba(0,0,0,.3)"><span style="color:white;font-weight:bold;font-size:16px">${letter}</span></div>`,
        iconSize: [36, 36],
        className: ''
    });
}

function getStatutLabel(s) {
    return {en_attente:'En attente',acceptee:'Acceptée',en_cours:'En cours',terminee:'Terminée',annulee:'Annulée',refusee:'Refusée'}[s] || s;
}
function getStatutClass(s) {
    return {en_attente:'status-warning',acceptee:'status-info',en_cours:'status-primary',terminee:'status-success',annulee:'status-danger',refusee:'status-danger'}[s] || '';
}
function escapeHtml(t) {
    if (!t) return '-';
    const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
}

async function voirDetails(livraison) {
    document.getElementById('modalId').textContent = livraison.id_livraison;

    document.getElementById('modalInfos').innerHTML = `
        <div class="info-item"><span class="info-label">Client</span><span class="info-value">${escapeHtml(livraison.nom_utilisateur)}</span></div>
        <div class="info-item"><span class="info-label">Email</span><span class="info-value">${escapeHtml(livraison.mail_user)}</span></div>
        <div class="info-item"><span class="info-label">Statut</span><span class="info-value"><span class="status-badge ${getStatutClass(livraison.statut)}">${getStatutLabel(livraison.statut)}</span></span></div>
        <div class="info-item"><span class="info-label">Distance</span><span class="info-value">${livraison.distance ? livraison.distance + ' km' : '-'}</span></div>
        <div class="info-item"><span class="info-label">Poids</span><span class="info-value">${livraison.poids ? livraison.poids + ' kg' : '-'}</span></div>
        <div class="info-item"><span class="info-label">Prix</span><span class="info-value">${livraison.prix ? Number(livraison.prix).toLocaleString('fr-FR') + ' FCFA' : '-'}</span></div>
        <div class="info-item"><span class="info-label">Véhicule</span><span class="info-value">${escapeHtml(livraison.type_vehicule)}</span></div>
        <div class="info-item"><span class="info-label">Date création</span><span class="info-value">${livraison.date_creation || '-'}</span></div>
        <div class="info-item full-width"><span class="info-label">Adresse de ramassage</span><span class="info-value">${escapeHtml(livraison.adresse_ramassage)}</span></div>
        <div class="info-item full-width"><span class="info-label">Adresse de dépôt</span><span class="info-value">${escapeHtml(livraison.adresse_depot)}</span></div>
    `;

    // ── BOUTONS ACCEPTER / REFUSER — toujours visibles ──
    const footer = document.getElementById('modalFooter');
    footer.innerHTML = `
        <div class="action-buttons">
            <form method="POST" style="flex:1">
                <input type="hidden" name="id_livraison" value="${livraison.id_livraison}">
                <input type="hidden" name="action" value="accepter">
                <button type="submit" class="btn-modal btn-accept">
                    <i class="fas fa-check-circle"></i> Accepter la livraison
                </button>
            </form>
            <form method="POST" style="flex:1">
                <input type="hidden" name="id_livraison" value="${livraison.id_livraison}">
                <input type="hidden" name="action" value="refuser">
                <button type="submit" class="btn-modal btn-refuse">
                    <i class="fas fa-times-circle"></i> Refuser la livraison
                </button>
            </form>
        </div>
    `;

    document.getElementById('modalOverlay').classList.add('open');
    setTimeout(() => initMapWithAddresses(livraison), 200);
}

async function initMapWithAddresses(livraison) {
    const loader = document.getElementById('mapLoader');
    loader.classList.remove('hidden');

    if (!mapInstance) {
        mapInstance = L.map('modalMap').setView([4.05, 9.7], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapInstance);
    }

    if (currentMarkers) { mapInstance.removeLayer(currentMarkers); currentMarkers = null; }
    if (currentRoute)   { mapInstance.removeLayer(currentRoute);   currentRoute   = null; }

    setTimeout(() => mapInstance.invalidateSize(), 100);

    const [pickup, dropoff] = await Promise.all([
        geocodeAddress(livraison.adresse_ramassage),
        geocodeAddress(livraison.adresse_depot)
    ]);

    if (!pickup || !dropoff) {
        loader.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Impossible de localiser les adresses</span>';
        return;
    }

    currentMarkers = L.layerGroup().addTo(mapInstance);
    L.marker([pickup.lat,  pickup.lng],  { icon: createIcon('#22C55E', 'D') })
        .bindPopup(`<b>📦 Ramassage</b><br>${livraison.adresse_ramassage}`)
        .addTo(currentMarkers);
    L.marker([dropoff.lat, dropoff.lng], { icon: createIcon('#F87171', 'A') })
        .bindPopup(`<b>🏁 Dépôt</b><br>${livraison.adresse_depot}`)
        .addTo(currentMarkers);

    mapInstance.fitBounds([[pickup.lat, pickup.lng],[dropoff.lat, dropoff.lng]], { padding: [50, 50] });

    const routeGeometry = await getRoute(pickup.lat, pickup.lng, dropoff.lat, dropoff.lng);
    if (routeGeometry) {
        currentRoute = L.geoJSON(routeGeometry, {
            style: { color: '#00D4E8', weight: 4, opacity: 0.8, dashArray: '8, 8' }
        }).addTo(mapInstance);
    }

    loader.classList.add('hidden');
}

function fermerModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

document.getElementById('modalOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) fermerModal();
});

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#livraisonsTable tbody tr').forEach(r => {
        r.style.display = !q || r.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
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