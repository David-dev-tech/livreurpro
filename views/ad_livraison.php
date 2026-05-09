<?php
// Connexion à la base de données
require_once '../config/config.php';

// Récupération de toutes les livraisons avec les infos livreur et utilisateur
$stmt = $pdo->query("
    SELECT 
        l.id_livraison,
        l.adresse_ramassage,
        l.adresse_depot,
        l.distance,
        l.prix,
        l.poids,
        l.statut,
        l.type_vehicule,
        l.date_creation,
        l.date_validation,
        u.nom_utilisateur,
        u.mail AS mail_user,
        liv.nom AS livreur_nom,
        liv.prenom AS livreur_prenom,
        liv.numero AS livreur_numero
    FROM livraison l
    LEFT JOIN utilisateur u ON l.id_user = u.id_user
    LEFT JOIN livreur liv ON l.id_livreur = liv.id_livreur
    ORDER BY l.date_creation DESC
");
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques rapides
$total       = count($livraisons);
$en_attente  = count(array_filter($livraisons, fn($r) => $r['statut'] === 'en_attente'));
$en_cours    = count(array_filter($livraisons, fn($r) => $r['statut'] === 'en_cours'));
$terminees   = count(array_filter($livraisons, fn($r) => $r['statut'] === 'terminee'));
$annulees    = count(array_filter($livraisons, fn($r) => $r['statut'] === 'annulee'));

// Libellés & couleurs des statuts
function statutLabel(string $s): string {
    return match($s) {
        'en_attente'  => 'En attente',
        'acceptee'    => 'Acceptée',
        'en_cours'    => 'En cours',
        'terminee'    => 'Terminée',
        'annulee'     => 'Annulée',
        default       => ucfirst($s),
    };
}
function statutClass(string $s): string {
    return match($s) {
        'en_attente' => 'status-warning',
        'acceptee'   => 'status-info',
        'en_cours'   => 'status-primary',
        'terminee'   => 'status-success',
        'annulee'    => 'status-danger',
        default      => 'status-default',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Livraisons</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dark/ad_css.css">
    <style>
        /* ── Badges de statut ── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .status-warning  { background: rgba(255,193,7,.15);  color: #FFC107; border:1px solid rgba(255,193,7,.3); }
        .status-info     { background: rgba(13,202,240,.15); color: #0dcaf0; border:1px solid rgba(13,202,240,.3); }
        .status-primary  { background: rgba(0,212,232,.15);  color: #00D4E8; border:1px solid rgba(0,212,232,.3); }
        .status-success  { background: rgba(25,135,84,.2);   color: #20c997; border:1px solid rgba(25,135,84,.35); }
        .status-danger   { background: rgba(220,53,69,.15);  color: #f87171; border:1px solid rgba(220,53,69,.3); }

        /* ── Cartes de stats ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--card-bg, #1C1F24);
            border: 1px solid var(--border, #2a2d35);
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .stat-card .stat-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted, #8a8f9a);
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 600;
        }
        .stat-card .stat-value {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 2rem;
            line-height: 1;
            color: var(--text, #e8eaf0);
        }
        .stat-card .stat-icon {
            font-size: .9rem;
            margin-bottom: 2px;
        }
        .stat-card.accent { border-color: #00D4E8; }
        .stat-card.accent .stat-value { color: #00D4E8; }

        /* ── Filtres ── */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        .filters-bar input,
        .filters-bar select {
            background: var(--input-bg, #23272e);
            border: 1px solid var(--border, #2a2d35);
            color: var(--text, #e8eaf0);
            border-radius: 6px;
            padding: 8px 12px;
            font-family: 'Barlow', sans-serif;
            font-size: .875rem;
            outline: none;
            transition: border-color .2s;
        }
        .filters-bar input:focus,
        .filters-bar select:focus { border-color: #00D4E8; }
        .filters-bar input { min-width: 220px; flex: 1; }

        /* ── Adresses tronquées ── */
        .addr { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ── Responsive table scroll ── */
        .table-scroll { overflow-x: auto; }

        /* ── Bouton export ── */
        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: 1px solid #00D4E8;
            color: #00D4E8;
            border-radius: 6px;
            padding: 8px 14px;
            font-family: 'Barlow', sans-serif;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, color .2s;
        }
        .btn-export:hover { background: #00D4E8; color: #1C1F24; }
    </style>
</head>
<body>

<!-- ═══════════ HEADER (identique à ad_livreur.php) ═══════════ -->
<header class="header">
    <div class="container">
        <div class="nav-container">
            <div class="menu-toggle" id="menuToggle">
                <span></span><span></span><span></span>
            </div>

            <a href="ad_dashboard.php" style="text-decoration:none;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
            </a>

            <div class="nav-links" id="navLinks">
                <a href="ad_dashboard.php">Dashboard</a>
                <a href="ad_utilisateur.php">Utilisateurs</a>
                <a href="ad_livreur.php">Livreurs</a>
                <a href="ad_livraison.php" class="active">Livraisons</a>
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

<!-- ═══════════ CONTENU PRINCIPAL ═══════════ -->
<main class="main">
    <div class="container">

        <!-- En-tête de page -->
        <div class="page-header">
            <h1 class="page-title">Gestion des Livraisons</h1>
            <p class="page-subtitle">Suivez et gérez toutes les livraisons en temps réel</p>
        </div>

        <!-- ── Cartes de statistiques ── -->
        <div class="stats-grid">
            <div class="stat-card accent">
                <span class="stat-icon" style="color:#00D4E8"><i class="fas fa-box"></i></span>
                <span class="stat-label">Total</span>
                <span class="stat-value"><?php echo $total; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:#FFC107"><i class="fas fa-clock"></i></span>
                <span class="stat-label">En attente</span>
                <span class="stat-value" style="color:#FFC107"><?php echo $en_attente; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:#00D4E8"><i class="fas fa-motorcycle"></i></span>
                <span class="stat-label">En cours</span>
                <span class="stat-value" style="color:#00D4E8"><?php echo $en_cours; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:#20c997"><i class="fas fa-check-circle"></i></span>
                <span class="stat-label">Terminées</span>
                <span class="stat-value" style="color:#20c997"><?php echo $terminees; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-icon" style="color:#f87171"><i class="fas fa-times-circle"></i></span>
                <span class="stat-label">Annulées</span>
                <span class="stat-value" style="color:#f87171"><?php echo $annulees; ?></span>
            </div>
        </div>

        <!-- ── Tableau des livraisons ── -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    Liste des livraisons&nbsp;(<?php echo $total; ?>)
                </div>
            </div>

            <!-- Filtres -->
            <div class="filters-bar">
                <input type="text" id="searchInput" placeholder="Rechercher par client, livreur, adresse…" oninput="filterTable()">
                <select id="filterStatut" onchange="filterTable()">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="acceptee">Acceptée</option>
                    <option value="en_cours">En cours</option>
                    <option value="terminee">Terminée</option>
                    <option value="annulee">Annulée</option>
                </select>
                <select id="filterVehicule" onchange="filterTable()">
                    <option value="">Tous les véhicules</option>
                    <option value="moto">Moto</option>
                    <option value="tricycle">Tricycle</option>
                    <option value="camionnette">Camionnette</option>
                    <option value="camion">Camion</option>
                </select>
            </div>

            <!-- Tableau -->
            <div class="table-scroll">
                <table id="livraisonsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Livreur</th>
                            <th>Ramassage</th>
                            <th>Dépôt</th>
                            <th>Distance</th>
                            <th>Poids</th>
                            <th>Prix</th>
                            <th>Véhicule</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($livraisons)): ?>
                            <tr>
                                <td colspan="12" style="text-align:center;padding:30px;">
                                    Aucune livraison enregistrée pour le moment.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($livraisons as $liv): ?>
                                <tr
                                    data-statut="<?php echo htmlspecialchars($liv['statut']); ?>"
                                    data-vehicule="<?php echo htmlspecialchars($liv['type_vehicule'] ?? ''); ?>"
                                >
                                    <td><?php echo $liv['id_livraison']; ?></td>

                                    <!-- Client -->
                                    <td>
                                        <strong><?php echo htmlspecialchars($liv['nom_utilisateur'] ?? 'N/A'); ?></strong><br>
                                        <small style="color:var(--text-muted,#8a8f9a);">
                                            <?php echo htmlspecialchars($liv['mail_user'] ?? ''); ?>
                                        </small>
                                    </td>

                                    <!-- Livreur -->
                                    <td>
                                        <?php if ($liv['livreur_nom']): ?>
                                            <strong><?php echo htmlspecialchars($liv['livreur_nom'] . ' ' . $liv['livreur_prenom']); ?></strong><br>
                                            <small style="color:var(--text-muted,#8a8f9a);">
                                                <?php echo htmlspecialchars($liv['livreur_numero']); ?>
                                            </small>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted,#8a8f9a);">Non assigné</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Adresses -->
                                    <td title="<?php echo htmlspecialchars($liv['adresse_ramassage']); ?>">
                                        <span class="addr"><?php echo htmlspecialchars($liv['adresse_ramassage']); ?></span>
                                    </td>
                                    <td title="<?php echo htmlspecialchars($liv['adresse_depot']); ?>">
                                        <span class="addr"><?php echo htmlspecialchars($liv['adresse_depot']); ?></span>
                                    </td>

                                    <!-- Métriques -->
                                    <td><?php echo $liv['distance'] !== null ? number_format($liv['distance'], 1) . ' km' : '-'; ?></td>
                                    <td><?php echo $liv['poids']    !== null ? number_format($liv['poids'],    2) . ' kg' : '-'; ?></td>
                                    <td><?php echo $liv['prix']     !== null ? number_format($liv['prix'],     0, ',', ' ') . ' FCFA' : '-'; ?></td>

                                    <!-- Véhicule -->
                                    <td><?php echo htmlspecialchars(ucfirst($liv['type_vehicule'] ?? '-')); ?></td>

                                    <!-- Statut -->
                                    <td>
                                        <span class="status-badge <?php echo statutClass($liv['statut']); ?>">
                                            <?php echo statutLabel($liv['statut']); ?>
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td style="white-space:nowrap;">
                                        <?php echo $liv['date_creation'] ? date('d/m/Y H:i', strtotime($liv['date_creation'])) : '-'; ?>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <button class="btn-small"
                                            onclick="voirDetails(<?php echo $liv['id_livraison']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-small btn-danger"
                                            onclick="if(confirm('Supprimer cette livraison ?')) location.href='ad_livraison_delete.php?id=<?php echo $liv['id_livraison']; ?>'">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div><!-- /table-scroll -->
        </div><!-- /table-section -->

    </div><!-- /container -->
</main>

<!-- ═══════════ MODALE DÉTAIL LIVRAISON ═══════════ -->
<div id="modalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:1000;align-items:center;justify-content:center;">
    <div id="modalBox" style="background:#1C1F24;border:1px solid #2a2d35;border-radius:10px;padding:30px;max-width:500px;width:90%;position:relative;">
        <button onclick="fermerModal()"
            style="position:absolute;top:12px;right:14px;background:none;border:none;color:#8a8f9a;font-size:1.2rem;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
        <h3 style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:1.4rem;color:#00D4E8;margin-bottom:18px;">
            <i class="fas fa-box-open"></i> Détail livraison #<span id="modalId"></span>
        </h3>
        <div id="modalContent" style="font-family:'Barlow',sans-serif;font-size:.9rem;line-height:1.8;color:#c8cad0;"></div>
    </div>
</div>

<script>
// ── Menu mobile ──
document.getElementById('menuToggle').addEventListener('click', function () {
    document.getElementById('navLinks').classList.toggle('active');
    document.getElementById('menuOverlay').classList.toggle('active');
});

// ── Filtre dynamique ──
function filterTable() {
    const search   = document.getElementById('searchInput').value.toLowerCase();
    const statut   = document.getElementById('filterStatut').value;
    const vehicule = document.getElementById('filterVehicule').value;
    const rows     = document.querySelectorAll('#livraisonsTable tbody tr');

    rows.forEach(row => {
        const text     = row.innerText.toLowerCase();
        const rowStat  = row.dataset.statut   || '';
        const rowVeh   = row.dataset.vehicule || '';

        const matchText = !search   || text.includes(search);
        const matchStat = !statut   || rowStat   === statut;
        const matchVeh  = !vehicule || rowVeh    === vehicule;

        row.style.display = (matchText && matchStat && matchVeh) ? '' : 'none';
    });
}

// ── Export CSV ──
function exportCSV() {
    const table = document.getElementById('livraisonsTable');
    const rows  = [...table.querySelectorAll('tr')];
    const csv   = rows
        .filter(r => r.style.display !== 'none')
        .map(r => [...r.querySelectorAll('th,td')]
            .slice(0, -1) // on exclut la colonne Actions
            .map(c => '"' + c.innerText.replace(/"/g, '""').replace(/\n/g, ' ') + '"')
            .join(',')
        ).join('\n');

    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), { href: url, download: 'livraisons.csv' });
    a.click();
    URL.revokeObjectURL(url);
}

// ── Modale détail ──
// Données PHP sérialisées en JS pour la modale
const livraisons = <?php
    $jsData = array_map(function($l) {
        return [
            'id'         => $l['id_livraison'],
            'client'     => $l['nom_utilisateur'] ?? 'N/A',
            'mail'       => $l['mail_user']        ?? '',
            'livreur'    => $l['livreur_nom'] ? ($l['livreur_nom'].' '.$l['livreur_prenom']) : 'Non assigné',
            'tel'        => $l['livreur_numero']   ?? '',
            'ramassage'  => $l['adresse_ramassage'],
            'depot'      => $l['adresse_depot'],
            'distance'   => $l['distance'],
            'poids'      => $l['poids'],
            'prix'       => $l['prix'],
            'vehicule'   => ucfirst($l['type_vehicule'] ?? ''),
            'statut'     => $l['statut'],
            'creation'   => $l['date_creation'],
            'validation' => $l['date_validation'],
        ];
    }, $livraisons);
    echo json_encode($jsData, JSON_UNESCAPED_UNICODE);
?>;

const statutLabels = {
    en_attente: 'En attente', acceptee: 'Acceptée',
    en_cours: 'En cours', terminee: 'Terminée', annulee: 'Annulée'
};

function voirDetails(id) {
    const l = livraisons.find(x => x.id == id);
    if (!l) return;
    document.getElementById('modalId').textContent = l.id;
    document.getElementById('modalContent').innerHTML = `
        <p><strong>Client :</strong> ${l.client} — ${l.mail}</p>
        <p><strong>Livreur :</strong> ${l.livreur}${l.tel ? ' (' + l.tel + ')' : ''}</p>
        <p><strong>Adresse ramassage :</strong> ${l.ramassage}</p>
        <p><strong>Adresse dépôt :</strong> ${l.depot}</p>
        <p><strong>Distance :</strong> ${l.distance ? l.distance + ' km' : '-'}</p>
        <p><strong>Poids :</strong> ${l.poids ? l.poids + ' kg' : '-'}</p>
        <p><strong>Prix :</strong> ${l.prix ? Number(l.prix).toLocaleString('fr-FR') + ' FCFA' : '-'}</p>
        <p><strong>Véhicule :</strong> ${l.vehicule || '-'}</p>
        <p><strong>Statut :</strong> ${statutLabels[l.statut] || l.statut}</p>
        <p><strong>Créée le :</strong> ${l.creation ? l.creation.replace('T',' ') : '-'}</p>
        <p><strong>Validée le :</strong> ${l.validation ? l.validation.replace('T',' ') : '-'}</p>
    `;
    const ov = document.getElementById('modalOverlay');
    ov.style.display = 'flex';
}

function fermerModal() {
    document.getElementById('modalOverlay').style.display = 'none';
}
document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) fermerModal();
});
</script>
</body>
</html>