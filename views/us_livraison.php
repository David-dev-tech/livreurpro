<?php
// ============================================================
// mes_livraisons.php — Historique des livraisons de l'utilisateur
// avec carte Leaflet interactive affichant l'itinéraire
// ============================================================
session_start();
require_once '../config/config.php';

// Redirection si non connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.html');
    exit;
}

$id_user = $_SESSION['id_user'];

// Récupération de toutes les livraisons de l'utilisateur
$stmt = $pdo->prepare("
    SELECT
        l.*,
        liv.nom        AS livreur_nom,
        liv.prenom     AS livreur_prenom,
        liv.numero     AS livreur_numero,
        liv.type_vehicule AS livreur_vehicule
    FROM livraison l
    LEFT JOIN livreur liv ON l.id_livreur = liv.id_livreur
    WHERE l.id_user = ?
    ORDER BY l.date_creation DESC
");
$stmt->execute([$id_user]);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <title>LivreurPro | Mes Livraisons</title>

  <link rel="stylesheet" href="../css/dark/us_css.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>
    :root {
      --cyan        : #00D4E8;
      --cyan-border : rgba(0, 212, 232, 0.30);
      --cyan-dim    : rgba(0, 212, 232, 0.08);
      --cyan-glow   : rgba(0, 212, 232, 0.18);
      --bg          : #13151A;
      --card        : #1C1F26;
      --card2       : #232830;
      --card3       : #2A303A;
      --white       : #EFF3F8;
      --grey        : #7A8694;
      --grey-light  : #B8C4D0;

      /* statuts */
      --s-attente   : #F59E0B;
      --s-acceptee  : #3B82F6;
      --s-cours     : #8B5CF6;
      --s-terminee  : #10B981;
      --s-annulee   : #EF4444;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background : var(--bg);
      color      : var(--white);
      font-family: 'Barlow', 'Segoe UI', sans-serif;
      min-height : 100vh;
    }

    /* ─── HERO BANNER ─── */
    .page-hero {
      background : linear-gradient(135deg, var(--card) 0%, #0e1117 100%);
      border-bottom: 1px solid var(--cyan-border);
      padding    : 2.5rem 0 2rem;
      position   : relative;
      overflow   : hidden;
    }
    .page-hero::before {
      content  : '';
      position : absolute;
      top: -60px; right: -60px;
      width: 280px; height: 280px;
      background: radial-gradient(circle, var(--cyan-glow) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-inner {
      max-width : 1200px;
      margin    : 0 auto;
      padding   : 0 1.5rem;
      display   : flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .hero-title {
      font-family : 'Barlow Condensed', sans-serif;
      font-size   : 2rem;
      font-weight : 800;
      letter-spacing: .04em;
      color       : var(--white);
    }
    .hero-title i { color: var(--cyan); margin-right: .5rem; }
    .hero-subtitle {
      font-size : .9rem;
      color     : var(--grey-light);
      margin-top: .3rem;
    }
    .hero-stat-group {
      display: flex;
      gap: 1.2rem;
      flex-wrap: wrap;
    }
    .hero-stat {
      background : var(--card2);
      border     : 1px solid var(--cyan-border);
      padding    : .7rem 1.2rem;
      border-radius: 6px;
      text-align : center;
      min-width  : 90px;
    }
    .hero-stat-val {
      font-family : 'Barlow Condensed', sans-serif;
      font-size   : 1.6rem;
      font-weight : 800;
      color       : var(--cyan);
      line-height : 1;
    }
    .hero-stat-label {
      font-size  : .65rem;
      color      : var(--grey);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-top : .25rem;
    }

    /* ─── MAIN CONTAINER ─── */
    .main-wrap {
      max-width : 1200px;
      margin    : 0 auto;
      padding   : 2rem 1.5rem 4rem;
    }

    /* ─── FILTRES ─── */
    .filter-bar {
      display  : flex;
      gap      : .8rem;
      flex-wrap: wrap;
      margin-bottom: 1.8rem;
      align-items: center;
    }
    .filter-label {
      font-size : .7rem;
      text-transform: uppercase;
      letter-spacing: .1em;
      color     : var(--grey);
      font-weight: 600;
      white-space: nowrap;
    }
    .filter-btn {
      padding   : .45rem 1.1rem;
      border    : 1px solid var(--card3);
      background: var(--card2);
      color     : var(--grey-light);
      border-radius: 30px;
      font-size : .78rem;
      font-family: inherit;
      cursor    : pointer;
      transition: all .22s;
      font-weight: 500;
    }
    .filter-btn:hover { border-color: var(--cyan-border); color: var(--cyan); }
    .filter-btn.active {
      background : var(--cyan);
      color      : #111;
      border-color: var(--cyan);
      font-weight : 700;
    }

    /* ─── ÉTAT VIDE ─── */
    .empty-wrap {
      text-align : center;
      padding    : 5rem 2rem;
    }
    .empty-wrap i { font-size: 4rem; color: var(--card3); margin-bottom: 1.2rem; display: block; }
    .empty-wrap h3 { color: var(--grey-light); font-size: 1.2rem; margin-bottom: .5rem; }
    .empty-wrap p  { color: var(--grey); font-size: .88rem; }
    .btn-go {
      display    : inline-flex;
      align-items: center;
      gap        : .4rem;
      margin-top : 1.5rem;
      padding    : .7rem 1.8rem;
      background : var(--cyan);
      color      : #111;
      border-radius: 4px;
      font-weight : 700;
      text-decoration: none;
      font-size  : .85rem;
      transition : all .22s;
    }
    .btn-go:hover { background: #00b8cc; transform: translateY(-2px); }

    /* ─── GRILLE CARTES ─── */
    .deliveries-grid {
      display             : grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap                 : 1.4rem;
    }

    /* ─── CARTE LIVRAISON ─── */
    .delivery-card {
      background    : var(--card);
      border        : 1px solid var(--card3);
      border-radius : 8px;
      overflow      : hidden;
      transition    : border-color .25s, transform .25s, box-shadow .25s;
      position      : relative;
    }
    .delivery-card:hover {
      border-color: var(--cyan-border);
      transform   : translateY(-3px);
      box-shadow  : 0 12px 40px rgba(0,0,0,.35);
    }
    /* Barre de couleur statut en haut */
    .card-stripe {
      height   : 3px;
      width    : 100%;
    }

    /* En-tête carte */
    .card-head {
      padding : 1rem 1.2rem .75rem;
      display : flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .6rem;
    }
    .card-id {
      font-family : 'Barlow Condensed', sans-serif;
      font-size   : .68rem;
      color       : var(--grey);
      letter-spacing: .1em;
      text-transform: uppercase;
    }
    .card-date {
      font-size  : .75rem;
      color      : var(--grey);
      margin-top : .18rem;
    }
    .status-badge {
      padding    : .28rem .75rem;
      border-radius: 20px;
      font-size  : .67rem;
      font-weight: 700;
      letter-spacing: .07em;
      text-transform: uppercase;
      white-space: nowrap;
      flex-shrink: 0;
    }

    /* Corps carte */
    .card-body { padding: 0 1.2rem .9rem; }

    .route-line {
      display    : flex;
      flex-direction: column;
      gap        : 0;
      position   : relative;
      margin-bottom: .9rem;
    }
    .route-point {
      display    : flex;
      align-items: center;
      gap        : .6rem;
      font-size  : .82rem;
      color      : var(--grey-light);
      padding    : .35rem 0;
      position   : relative;
      z-index    : 1;
    }
    .route-point-icon {
      width     : 26px; height: 26px;
      border-radius: 50%;
      display   : flex; align-items: center; justify-content: center;
      font-size : .6rem;
      flex-shrink: 0;
    }
    .route-point-icon.pickup  { background: rgba(16,185,129,.18); color: #10B981; border: 1px solid rgba(16,185,129,.3); }
    .route-point-icon.dropoff { background: rgba(239,68,68,.18);  color: #EF4444; border: 1px solid rgba(239,68,68,.3);  }

    /* Ligne verticale entre les deux points */
    .route-connector {
      width   : 2px;
      height  : 18px;
      background: linear-gradient(to bottom, #10B981, #EF4444);
      margin  : 0 12px;
      opacity : .5;
    }

    /* Méta infos (poids, distance, prix, livreur) */
    .card-meta {
      display             : grid;
      grid-template-columns: 1fr 1fr;
      gap                 : .5rem .8rem;
      margin-bottom       : .9rem;
    }
    .meta-item {
      background  : var(--card2);
      border-radius: 5px;
      padding     : .45rem .65rem;
    }
    .meta-label {
      font-size  : .58rem;
      color      : var(--grey);
      text-transform: uppercase;
      letter-spacing: .08em;
      font-weight: 600;
      display    : flex;
      align-items: center;
      gap        : .3rem;
    }
    .meta-label i { color: var(--cyan); }
    .meta-value {
      font-size  : .88rem;
      font-weight: 700;
      color      : var(--white);
      margin-top : .2rem;
    }

    /* Bouton voir itinéraire */
    .btn-itinerary {
      display    : flex;
      align-items: center;
      justify-content: center;
      gap        : .4rem;
      width      : 100%;
      padding    : .6rem;
      background : var(--cyan-dim);
      border     : 1px solid var(--cyan-border);
      color      : var(--cyan);
      font-family: inherit;
      font-size  : .78rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      cursor     : pointer;
      border-radius: 5px;
      transition : all .22s;
    }
    .btn-itinerary:hover { background: var(--cyan); color: #111; }
    .btn-itinerary i { font-size: .8rem; }

    /* ─── MODALE CARTE ─── */
    .map-modal {
      display    : none;
      position   : fixed;
      inset      : 0;
      background : rgba(0,0,0,.88);
      z-index    : 5000;
      justify-content: center;
      align-items    : center;
      backdrop-filter: blur(6px);
    }
    .map-modal.active { display: flex; }

    .map-modal-box {
      background   : var(--card);
      border       : 1px solid var(--cyan-border);
      border-radius: 10px;
      width        : 95%;
      max-width    : 900px;
      max-height   : 92vh;
      overflow     : hidden;
      display      : flex;
      flex-direction: column;
      animation    : slideUp .3s ease;
    }
    @keyframes slideUp {
      from { opacity:0; transform: translateY(30px); }
      to   { opacity:1; transform: translateY(0); }
    }

    .map-modal-header {
      padding      : 1rem 1.4rem;
      background   : var(--card2);
      border-bottom: 1px solid var(--cyan-border);
      display      : flex;
      align-items  : center;
      gap          : .8rem;
    }
    .map-modal-header h3 {
      flex       : 1;
      font-family: 'Barlow Condensed', sans-serif;
      font-size  : 1.1rem;
      font-weight: 700;
      color      : var(--white);
      letter-spacing: .04em;
    }
    .map-modal-header h3 i { color: var(--cyan); margin-right: .35rem; }
    .map-close-btn {
      background : none;
      border     : none;
      color      : var(--grey);
      font-size  : 1.6rem;
      cursor     : pointer;
      line-height: 1;
      transition : color .2s;
    }
    .map-close-btn:hover { color: var(--cyan); }

    /* Info strip au-dessus de la carte */
    .map-info-strip {
      display  : flex;
      flex-wrap: wrap;
      gap      : 1.2rem;
      padding  : .8rem 1.4rem;
      background: var(--card2);
      border-bottom: 1px solid var(--card3);
    }
    .strip-item {
      display    : flex;
      align-items: center;
      gap        : .4rem;
      font-size  : .8rem;
      color      : var(--grey-light);
    }
    .strip-item i { color: var(--cyan); font-size: .75rem; }
    .strip-item strong { color: var(--white); }

    /* Loader itinéraire */
    .route-loader {
      display     : none;
      align-items : center;
      gap         : .5rem;
      padding     : .5rem 1.4rem;
      background  : var(--card3);
      font-size   : .75rem;
      color       : var(--grey-light);
      border-bottom: 1px solid var(--card3);
    }
    .route-loader.active { display: flex; }
    .route-loader-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--cyan);
      animation: pulse-dot .8s ease-in-out infinite;
    }
    .route-loader-dot:nth-child(2) { animation-delay: .16s; }
    .route-loader-dot:nth-child(3) { animation-delay: .32s; }
    @keyframes pulse-dot {
      0%, 100% { opacity: .3; transform: scale(.8); }
      50%       { opacity: 1;  transform: scale(1.2); }
    }

    /* Carte Leaflet */
    #detail-map {
      flex    : 1;
      height  : 450px;
      min-height: 300px;
    }
    @media (max-width:600px) { #detail-map { height: 300px; } }

    /* Légende sous la carte */
    .map-legend {
      display  : flex;
      align-items: center;
      gap      : 1.5rem;
      flex-wrap: wrap;
      padding  : .75rem 1.4rem;
      background: var(--card3);
      font-size: .75rem;
      color    : var(--grey-light);
    }
    .legend-item {
      display: flex;
      align-items: center;
      gap: .4rem;
    }
    .legend-dot {
      width: 10px; height: 10px;
      border-radius: 50%;
    }

    /* ─── RESPONSIVE ─── */
    @media (max-width:600px) {
      .deliveries-grid { grid-template-columns: 1fr; }
      .hero-inner { flex-direction: column; align-items: flex-start; }
    }

    /* ─── Couleurs statut ─── */
    .stripe-attente   { background: var(--s-attente);  }
    .stripe-acceptee  { background: var(--s-acceptee); }
    .stripe-cours     { background: var(--s-cours);    }
    .stripe-terminee  { background: var(--s-terminee); }
    .stripe-annulee   { background: var(--s-annulee);  }

    .badge-en_attente { background: rgba(245,158,11,.18); color: var(--s-attente);  border: 1px solid rgba(245,158,11,.3);  }
    .badge-acceptee   { background: rgba(59,130,246,.18);  color: var(--s-acceptee); border: 1px solid rgba(59,130,246,.3);  }
    .badge-en_cours   { background: rgba(139,92,246,.18);  color: var(--s-cours);    border: 1px solid rgba(139,92,246,.3);  }
    .badge-terminee   { background: rgba(16,185,129,.18);  color: var(--s-terminee); border: 1px solid rgba(16,185,129,.3);  }
    .badge-annulee    { background: rgba(239,68,68,.18);   color: var(--s-annulee);  border: 1px solid rgba(239,68,68,.3);   }
  </style>
</head>
<body>

  <!-- ══════════════ HEADER ══════════════ -->
  <header class="header">
    <div class="container">
      <div class="nav-container">
        <div class="menu-toggle" id="menuToggle">
          <span></span><span></span><span></span>
        </div>
        <a href="../index.html" style="text-decoration:none;">
          <div style="display:inline-flex;align-items:center;justify-content:center;
                      width:75px;height:48px;background:#00D4E8;border:2px solid #00D4E8;
                      clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
                      font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;
                      font-size:1.3rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
        </a>
        <div class="nav-links" id="navLinks">
          <a href="../index.html">Accueil</a>
          <a href="us_catalogue.php">Catalogue</a>
          <a href="us_profil.php">Profil</a>
          <a href="mes_livraisons.php" class="active">Mes Commandes</a>
          <a href="us_contact.php">Contact</a>
          <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['mail'] ?? 'Utilisateur'); ?></span>
            
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ══════════════ HERO BANNER ══════════════ -->
  <?php
    $total      = count($livraisons);
    $terminees  = count(array_filter($livraisons, fn($l) => $l['statut'] === 'terminee'));
    $en_cours   = count(array_filter($livraisons, fn($l) => in_array($l['statut'], ['en_cours','acceptee','en_attente'])));
    $total_depense = array_sum(array_column(array_filter($livraisons, fn($l) => $l['statut'] === 'terminee'), 'prix'));
  ?>
  <section class="page-hero">
    <div class="hero-inner">
      <div>
        <h1 class="hero-title"><i class="fas fa-route"></i> Mes Livraisons</h1>
        <p class="hero-subtitle">Suivez toutes vos commandes et visualisez les itinéraires sur la carte</p>
      </div>
      <div class="hero-stat-group">
        <div class="hero-stat">
          <div class="hero-stat-val"><?= $total ?></div>
          <div class="hero-stat-label">Total</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-val"><?= $terminees ?></div>
          <div class="hero-stat-label">Livrées</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-val"><?= $en_cours ?></div>
          <div class="hero-stat-label">En cours</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-val"><?= number_format($total_depense, 0, ',', ' ') ?></div>
          <div class="hero-stat-label">FCFA dépensés</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════ CONTENU PRINCIPAL ══════════════ -->
  <main class="main-wrap">

    <?php if (empty($livraisons)): ?>
      <!-- État vide -->
      <div class="empty-wrap">
        <i class="fas fa-box-open"></i>
        <h3>Aucune livraison pour le moment</h3>
        <p>Commandez votre première livraison depuis le catalogue de livreurs.</p>
        <a href="catalogue.php" class="btn-go">
          <i class="fas fa-search"></i> Parcourir le catalogue
        </a>
      </div>

    <?php else: ?>

      <!-- Filtres statut -->
      <div class="filter-bar">
        <span class="filter-label"><i class="fas fa-filter"></i> Filtrer :</span>
        <button class="filter-btn active" data-filter="all">Toutes</button>
        <button class="filter-btn" data-filter="en_attente">En attente</button>
        <button class="filter-btn" data-filter="acceptee">Acceptée</button>
        <button class="filter-btn" data-filter="en_cours">En cours</button>
        <button class="filter-btn" data-filter="terminee">Terminée</button>
        <button class="filter-btn" data-filter="annulee">Annulée</button>
      </div>

      <!-- Grille de cartes -->
      <div class="deliveries-grid" id="deliveriesGrid">
        <?php foreach ($livraisons as $liv):
          $statut    = $liv['statut'];
          $stripeClass = match($statut) {
            'en_attente' => 'stripe-attente',
            'acceptee'   => 'stripe-acceptee',
            'en_cours'   => 'stripe-cours',
            'terminee'   => 'stripe-terminee',
            'annulee'    => 'stripe-annulee',
            default      => 'stripe-attente'
          };
          $badgeClass = 'badge-' . $statut;
          $badgeLabel = match($statut) {
            'en_attente' => '⏳ En attente',
            'acceptee'   => '✅ Acceptée',
            'en_cours'   => '🚚 En cours',
            'terminee'   => '🎉 Terminée',
            'annulee'    => '❌ Annulée',
            default      => $statut
          };
          $date = $liv['date_creation']
            ? (new DateTime($liv['date_creation']))->format('d/m/Y à H:i')
            : '—';

          $pickup_coords  = $liv['adresse_ramassage'] ?? '';
          $dropoff_coords = $liv['adresse_depot']     ?? '';

          $pickupLabel  = $pickup_coords  ?: 'Non renseigné';
          $dropoffLabel = $dropoff_coords ?: 'Non renseigné';

          $vehicleLabel = match($liv['type_vehicule']) {
            'moto'        => '🏍️ Moto',
            'tricycle'    => '🛺 Tricycle',
            'camionnette' => '🚐 Camionnette',
            'camion'      => '🚛 Camion',
            default       => ucfirst($liv['type_vehicule'] ?? '—')
          };
        ?>
        <div
          class="delivery-card"
          data-statut="<?= htmlspecialchars($statut) ?>"
          data-id="<?= $liv['id_livraison'] ?>"
          data-pickup="<?= htmlspecialchars($pickup_coords) ?>"
          data-dropoff="<?= htmlspecialchars($dropoff_coords) ?>"
          data-pickup-label="<?= htmlspecialchars($pickupLabel) ?>"
          data-dropoff-label="<?= htmlspecialchars($dropoffLabel) ?>"
          data-distance="<?= htmlspecialchars($liv['distance'] ?? '0') ?>"
          data-prix="<?= htmlspecialchars($liv['prix'] ?? '0') ?>"
          data-poids="<?= htmlspecialchars($liv['poids'] ?? '0') ?>"
          data-vehicle="<?= htmlspecialchars($vehicleLabel) ?>"
          data-livreur="<?= htmlspecialchars(($liv['livreur_prenom'] ?? '') . ' ' . ($liv['livreur_nom'] ?? '')) ?>"
          data-date="<?= htmlspecialchars($date) ?>"
        >
          <!-- Bande couleur statut -->
          <div class="card-stripe <?= $stripeClass ?>"></div>

          <!-- En-tête -->
          <div class="card-head">
            <div>
              <div class="card-id"># LIV-<?= str_pad($liv['id_livraison'], 4, '0', STR_PAD_LEFT) ?></div>
              <div class="card-date"><i class="fas fa-calendar-alt"></i> <?= $date ?></div>
            </div>
            <span class="status-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
          </div>

          <!-- Corps -->
          <div class="card-body">

            <!-- Route pickup → dropoff -->
            <div class="route-line">
              <div class="route-point">
                <div class="route-point-icon pickup"><i class="fas fa-map-marker-alt"></i></div>
                <span title="<?= htmlspecialchars($pickupLabel) ?>" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px;">
                  <?= mb_strlen($pickupLabel) > 40 ? mb_substr($pickupLabel, 0, 40).'…' : htmlspecialchars($pickupLabel) ?>
                </span>
              </div>
              <div class="route-connector"></div>
              <div class="route-point">
                <div class="route-point-icon dropoff"><i class="fas fa-flag-checkered"></i></div>
                <span title="<?= htmlspecialchars($dropoffLabel) ?>" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px;">
                  <?= mb_strlen($dropoffLabel) > 40 ? mb_substr($dropoffLabel, 0, 40).'…' : htmlspecialchars($dropoffLabel) ?>
                </span>
              </div>
            </div>

            <!-- Méta -->
            <div class="card-meta">
              <div class="meta-item">
                <div class="meta-label"><i class="fas fa-weight-hanging"></i> Poids</div>
                <div class="meta-value"><?= number_format((float)$liv['poids'], 1) ?> kg</div>
              </div>
              <div class="meta-item">
                <div class="meta-label"><i class="fas fa-road"></i> Distance</div>
                <div class="meta-value"><?= number_format((float)($liv['distance'] ?? 0), 1) ?> km</div>
              </div>
              <div class="meta-item">
                <div class="meta-label"><i class="fas fa-money-bill-wave"></i> Prix</div>
                <div class="meta-value"><?= number_format((float)($liv['prix'] ?? 0), 0, ',', ' ') ?> FCFA</div>
              </div>
              <div class="meta-item">
                <div class="meta-label"><i class="fas fa-motorcycle"></i> Véhicule</div>
                <div class="meta-value" style="font-size:.78rem;"><?= $vehicleLabel ?></div>
              </div>
            </div>

            <!-- Livreur -->
            <?php if ($liv['livreur_nom']): ?>
            <div style="font-size:.78rem;color:var(--grey-light);margin-bottom:.9rem;display:flex;align-items:center;gap:.4rem;">
              <i class="fas fa-user" style="color:var(--cyan);font-size:.7rem;"></i>
              <span><?= htmlspecialchars($liv['livreur_prenom'] . ' ' . $liv['livreur_nom']) ?></span>
              <?php if ($liv['livreur_numero']): ?>
                &nbsp;·&nbsp; <i class="fas fa-phone-alt" style="color:var(--cyan);font-size:.7rem;"></i>
                <span><?= htmlspecialchars($liv['livreur_numero']) ?></span>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Bouton carte -->
            <button class="btn-itinerary" onclick="openMapDetail(this.closest('.delivery-card'))">
              <i class="fas fa-map-marked-alt"></i> Voir l'itinéraire sur la carte
            </button>

          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </main>


  <!-- ══════════════ MODALE CARTE ITINÉRAIRE ══════════════ -->
  <div class="map-modal" id="mapDetailModal">
    <div class="map-modal-box">

      <!-- En-tête -->
      <div class="map-modal-header">
        <h3><i class="fas fa-route"></i> <span id="modalMapTitle">Itinéraire</span></h3>
        <button class="map-close-btn" onclick="closeMapDetail()" aria-label="Fermer">&times;</button>
      </div>

      <!-- Infos strip -->
      <div class="map-info-strip" id="modalMapStrip">
        <div class="strip-item"><i class="fas fa-road"></i> Distance : <strong id="stripDistance">—</strong></div>
        <div class="strip-item"><i class="fas fa-money-bill-wave"></i> Prix : <strong id="stripPrix">—</strong></div>
        <div class="strip-item"><i class="fas fa-weight-hanging"></i> Poids : <strong id="stripPoids">—</strong></div>
        <div class="strip-item"><i class="fas fa-user"></i> Livreur : <strong id="stripLivreur">—</strong></div>
      </div>

      <!-- Loader itinéraire -->
      <div class="route-loader" id="routeLoader">
        <div class="route-loader-dot"></div>
        <div class="route-loader-dot"></div>
        <div class="route-loader-dot"></div>
        <span>Calcul de l'itinéraire en cours…</span>
      </div>

      <!-- Carte -->
      <div id="detail-map"></div>

      <!-- Légende -->
      <div class="map-legend">
        <div class="legend-item">
          <div class="legend-dot" style="background:#10B981;"></div>
          Point de ramassage
        </div>
        <div class="legend-item">
          <div class="legend-dot" style="background:#EF4444;"></div>
          Point de dépôt
        </div>
        <div class="legend-item">
          <div class="legend-dot" style="background:#00D4E8;border-radius:0;height:3px;width:20px;"></div>
          Itinéraire routier
        </div>
        <div style="margin-left:auto;font-size:.68rem;color:var(--grey);">
          <i class="fas fa-info-circle"></i> Itinéraire via OpenStreetMap / OSRM
        </div>
      </div>

    </div>
  </div>


  <!-- ══════════════ SCRIPTS ══════════════ -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
  /* ─────────────────────────────────────────
     FILTRES
  ───────────────────────────────────────── */
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const filter = this.dataset.filter;
      document.querySelectorAll('.delivery-card').forEach(card => {
        card.style.display = (filter === 'all' || card.dataset.statut === filter) ? '' : 'none';
      });
    });
  });

  /* ─────────────────────────────────────────
     CARTE LEAFLET (modale détail)
  ───────────────────────────────────────── */
  let detailMap = null;
  let mapLayers = [];

  /* Icônes SVG personnalisées */
  function makeIcon(color, label) {
    return L.divIcon({
      className: '',
      html: `<div style="position:relative;">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42" viewBox="0 0 30 42">
          <path d="M15 0C6.7 0 0 6.7 0 15c0 10.5 15 27 15 27S30 25.5 30 15C30 6.7 23.3 0 15 0z"
                fill="${color}" stroke="rgba(0,0,0,.4)" stroke-width="1.5"/>
          <circle cx="15" cy="15" r="7" fill="rgba(255,255,255,.95)"/>
        </svg>
        <span style="position:absolute;top:8px;left:50%;transform:translateX(-50%);
                     font-size:9px;font-weight:900;color:${color};">${label}</span>
      </div>`,
      iconSize  : [30, 42],
      iconAnchor: [15, 42],
      popupAnchor: [0, -45]
    });
  }

  const iconPickup  = makeIcon('#10B981', 'A');
  const iconDropoff = makeIcon('#EF4444', 'B');

  /* Parse "lat,lng" → [lat, lng] ou null */
  function parseCoords(str) {
    if (!str) return null;
    const parts = str.trim().split(',');
    if (parts.length < 2) return null;
    const lat = parseFloat(parts[0]);
    const lng = parseFloat(parts[1]);
    if (isNaN(lat) || isNaN(lng)) return null;
    return [lat, lng];
  }

  /* ─────────────────────────────────────────
     FALLBACK : ligne droite en pointillés
  ───────────────────────────────────────── */
  function tracerLigneDroite(pickupCoords, dropoffCoords, dataset) {
    const line = L.polyline([pickupCoords, dropoffCoords], {
      color    : '#00D4E8',
      weight   : 3,
      opacity  : 0.85,
      dashArray: '10, 6'
    }).addTo(detailMap);
    mapLayers.push(line);

    const midLat = (pickupCoords[0] + dropoffCoords[0]) / 2;
    const midLng = (pickupCoords[1] + dropoffCoords[1]) / 2;
    const midMarker = L.circleMarker([midLat, midLng], {
      radius: 6, fillColor: '#00D4E8', color: '#fff', weight: 2, fillOpacity: 1
    }).bindTooltip(
      `<b>Distance estimée</b><br>${parseFloat(dataset.distance || 0).toFixed(2)} km`,
      { permanent: false, direction: 'top' }
    ).addTo(detailMap);
    mapLayers.push(midMarker);

    detailMap.fitBounds([pickupCoords, dropoffCoords], { padding: [50, 50], animate: true });
    document.getElementById('routeLoader').classList.remove('active');
  }

  /* ─────────────────────────────────────────
     OUVERTURE MODALE + TRACÉ OSRM
  ───────────────────────────────────────── */
  function openMapDetail(card) {
    const dataset = card.dataset;

    // Remplir les infos de la strip
    document.getElementById('modalMapTitle').textContent =
      'Itinéraire — LIV-' + String(dataset.id).padStart(4, '0');
    document.getElementById('stripDistance').textContent =
      parseFloat(dataset.distance || 0).toFixed(1) + ' km';
    document.getElementById('stripPrix').textContent =
      parseInt(dataset.prix || 0).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('stripPoids').textContent =
      parseFloat(dataset.poids || 0).toFixed(1) + ' kg';
    document.getElementById('stripLivreur').textContent =
      dataset.livreur?.trim() || 'Non assigné';

    // Ouvrir la modale
    document.getElementById('mapDetailModal').classList.add('active');

    // Initialiser la carte une seule fois
    if (!detailMap) {
      detailMap = L.map('detail-map', {
        center : [5.5, 12.3],
        zoom   : 6,
        minZoom: 4,
        maxZoom: 18
      });
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
      }).addTo(detailMap);
    }

    // Nettoyer les couches précédentes
    mapLayers.forEach(l => detailMap.removeLayer(l));
    mapLayers = [];

    const pickupCoords  = parseCoords(dataset.pickup);
    const dropoffCoords = parseCoords(dataset.dropoff);

    if (!pickupCoords && !dropoffCoords) {
      detailMap.setView([5.5, 12.3], 6);
      const noData = L.popup({ closeButton: false })
        .setLatLng([5.5, 12.3])
        .setContent('<div style="color:#EF4444;font-weight:bold;">Coordonnées GPS non disponibles.</div>')
        .openOn(detailMap);
      mapLayers.push(noData);
      setTimeout(() => detailMap.invalidateSize(), 150);
      return;
    }

    const bounds = [];

    // Marqueur ramassage
    if (pickupCoords) {
      const mA = L.marker(pickupCoords, { icon: iconPickup })
        .bindPopup(`<b style="color:#10B981;">📍 Ramassage</b><br><small>${dataset.pickupLabel}</small><br><code style="font-size:10px;">${dataset.pickup}</code>`)
        .addTo(detailMap);
      mapLayers.push(mA);
      bounds.push(pickupCoords);
    }

    // Marqueur dépôt
    if (dropoffCoords) {
      const mB = L.marker(dropoffCoords, { icon: iconDropoff })
        .bindPopup(`<b style="color:#EF4444;">🏁 Dépôt</b><br><small>${dataset.dropoffLabel}</small><br><code style="font-size:10px;">${dataset.dropoff}</code>`)
        .addTo(detailMap);
      mapLayers.push(mB);
      bounds.push(dropoffCoords);
    }

    // Vue provisoire sur les marqueurs pendant le chargement OSRM
    if (bounds.length === 2) {
      detailMap.fitBounds(bounds, { padding: [60, 60], maxZoom: 14 });
    } else if (bounds.length === 1) {
      detailMap.setView(bounds[0], 13);
    }

    setTimeout(() => detailMap.invalidateSize(), 200);

    // ── Itinéraire OSRM (vraies routes) ──────────────────────────────
    if (pickupCoords && dropoffCoords) {

      // Afficher le loader
      document.getElementById('routeLoader').classList.add('active');

      const osrmUrl = `https://router.project-osrm.org/route/v1/driving/`
        + `${pickupCoords[1]},${pickupCoords[0]};`
        + `${dropoffCoords[1]},${dropoffCoords[0]}`
        + `?overview=full&geometries=geojson`;

      fetch(osrmUrl)
        .then(res => res.json())
        .then(data => {
          document.getElementById('routeLoader').classList.remove('active');

          if (data.code === 'Ok' && data.routes && data.routes.length > 0) {

            // Convertir les coordonnées GeoJSON [lng, lat] → Leaflet [lat, lng]
            const routeCoords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);

            // Ombre sous la route
            const lineShadow = L.polyline(routeCoords, {
              color      : 'rgba(0,0,0,.45)',
              weight     : 7,
              opacity    : 0.4,
              lineJoin   : 'round',
              lineCap    : 'round',
              interactive: false
            }).addTo(detailMap);
            lineShadow.bringToBack();
            mapLayers.push(lineShadow);

            // Route principale cyan
            const line = L.polyline(routeCoords, {
              color   : '#00D4E8',
              weight  : 4,
              opacity : 0.95,
              lineJoin: 'round',
              lineCap : 'round'
            }).addTo(detailMap);
            mapLayers.push(line);

            // Point milieu avec distance réelle OSRM
            const mid = Math.floor(routeCoords.length / 2);
            const distanceKm = (data.routes[0].distance / 1000).toFixed(2);
            const midMarker = L.circleMarker(routeCoords[mid], {
              radius     : 7,
              fillColor  : '#00D4E8',
              color      : '#fff',
              weight     : 2,
              fillOpacity: 1
            }).bindTooltip(
              `<b>Distance réelle</b><br>${distanceKm} km`,
              { permanent: false, direction: 'top' }
            ).addTo(detailMap);
            mapLayers.push(midMarker);

            // Ajuster la vue sur la route entière
            detailMap.fitBounds(line.getBounds(), { padding: [50, 50], animate: true });

          } else {
            // OSRM a répondu mais sans route (zone non couverte) → fallback
            tracerLigneDroite(pickupCoords, dropoffCoords, dataset);
          }
        })
        .catch(() => {
          // Erreur réseau → fallback ligne droite
          tracerLigneDroite(pickupCoords, dropoffCoords, dataset);
        });
    }
  }

  function closeMapDetail() {
    document.getElementById('mapDetailModal').classList.remove('active');
  }

  /* ─────────────────────────────────────────
     ÉVÉNEMENTS GLOBAUX
  ───────────────────────────────────────── */
  document.getElementById('mapDetailModal').addEventListener('click', function(e) {
    if (e.target === this) closeMapDetail();
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeMapDetail();
  });

  const menuToggle = document.getElementById('menuToggle');
  const navLinks   = document.getElementById('navLinks');
  menuToggle?.addEventListener('click', () => {
    menuToggle.classList.toggle('active');
    navLinks.classList.toggle('active');
  });
  </script>

</body>
</html>