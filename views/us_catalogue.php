<?php
// ============================================================
// catalogue.php — Page catalogue des livreurs + Formulaire de
// livraison avec carte interactive Leaflet pour les adresses
// ============================================================
session_start();

require_once '../config/config.php';

// Récupération de tous les livreurs triés par nom
$stmt = $pdo->query("SELECT * FROM livreur ORDER BY nom ASC");
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des notes moyennes par livreur
$notesStmt = $pdo->query("
    SELECT id_livreur,
           ROUND(AVG(note), 1) AS note_moy,
           COUNT(*)            AS nb_avis
    FROM note
    GROUP BY id_livreur
");
$notes = [];
foreach ($notesStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $notes[$row['id_livreur']] = $row;
}

// Fusion des notes dans les données livreurs
foreach ($drivers as &$d) {
    $id = $d['id_livreur'];
    $d['note']    = isset($notes[$id]) ? (float)$notes[$id]['note_moy'] : 0;
    $d['nb_avis'] = isset($notes[$id]) ? (int)$notes[$id]['nb_avis']    : 0;
    // Capacité par défaut si non définie en BD
    if (!isset($d['capacite_poids'])) $d['capacite_poids'] = 500;
}
unset($d);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <title>LivreurPro | Catalogue des livreurs</title>

  <!-- Feuille de style principale du projet -->
  <link rel="stylesheet" href="../css/dark/us_css.css" />

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Leaflet CSS — nécessaire pour la carte interactive -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>
    /* =====================================================
       VARIABLES DE COULEUR (reprises du thème dark du projet)
       ===================================================== */
    :root {
      --cyan          : #00D4E8;
      --cyan-border   : rgba(0, 212, 232, 0.35);
      --cyan-dim      : rgba(0, 212, 232, 0.10);
      --anthracite    : #1C1F24;
      --anthracite2   : #23272E;
      --anthracite3   : #2D333B;
      --white         : #F0F4F8;
      --grey          : #8B949E;
      --grey-light    : #C9D1D9;
    }

    /* =====================================================
       MODALE PRINCIPALE
       ===================================================== */
    .modal {
      display    : none;
      position   : fixed;
      inset      : 0;
      background : rgba(0, 0, 0, 0.85);
      z-index    : 2000;
      justify-content : center;
      align-items     : center;
      backdrop-filter : blur(6px);
    }
    .modal.active { display: flex; }

    .modal-content {
      background   : var(--anthracite2);
      border       : 1px solid var(--cyan-border);
      width        : 92%;
      max-width    : 780px;   /* plus large pour accueillir la carte */
      max-height   : 94vh;
      overflow-y   : auto;
      position     : relative;
      border-radius: 6px;
      animation    : modalFadeIn .3s ease;
    }
    /* Barre cyan à gauche */
    .modal-content::before {
      content  : '';
      position : absolute;
      top      : 0; left : 0;
      width    : 3px; height : 100%;
      background: var(--cyan);
      border-radius: 6px 0 0 6px;
    }

    @keyframes modalFadeIn {
      from { opacity:0; transform:translateY(-28px); }
      to   { opacity:1; transform:translateY(0);     }
    }

    /* ---- En-tête modale ---- */
    .modal-header {
      padding       : 1.1rem 1.5rem;
      border-bottom : 1px solid var(--anthracite3);
      display       : flex;
      align-items   : center;
      justify-content: space-between;
    }
    .modal-title {
      font-family : 'Barlow Condensed', sans-serif;
      font-size   : 1.25rem;
      color       : var(--white);
      letter-spacing: .05em;
    }
    .modal-title i { color: var(--cyan); margin-right: .45rem; }

    .modal-close {
      background : none;
      border     : none;
      color      : var(--grey);
      font-size  : 1.7rem;
      line-height: 1;
      cursor     : pointer;
      transition : color .25s;
    }
    .modal-close:hover { color: var(--cyan); }

    /* ---- Corps modale ---- */
    .modal-body { padding: 1.4rem 1.5rem; }

    /* ---- Carte récap livreur sélectionné ---- */
    .driver-info-card {
      background   : var(--anthracite3);
      padding      : .9rem 1rem;
      margin-bottom: 1.4rem;
      border-left  : 3px solid var(--cyan);
      border-radius: 0 4px 4px 0;
      display      : grid;
      grid-template-columns: 1fr 1fr;
      gap          : .3rem .8rem;
    }
    .driver-info-card p { margin:0; font-size:.88rem; color:var(--grey-light); }
    .driver-info-card strong { color:var(--cyan); }

    /* ---- Grille de formulaire ---- */
    .form-row {
      display              : grid;
      grid-template-columns: 1fr 1fr;
      gap                  : 1rem;
    }
    @media (max-width:640px) { .form-row { grid-template-columns:1fr; gap:.75rem; } }

    .form-group { margin-bottom: 1.1rem; }

    .form-group label {
      display       : block;
      font-size     : .72rem;
      font-weight   : 600;
      color         : var(--grey);
      margin-bottom : .35rem;
      text-transform: uppercase;
      letter-spacing: .1em;
    }
    .form-group label i { color:var(--cyan); margin-right:.28rem; }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width      : 100%;
      padding    : .65rem .95rem;
      background : var(--anthracite);
      border     : 1px solid var(--anthracite3);
      font-family: 'Barlow', sans-serif;
      font-size  : .88rem;
      color      : var(--white);
      border-radius: 3px;
      transition : all .25s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline    : none;
      border-color: var(--cyan);
      box-shadow : 0 0 0 2px var(--cyan-dim);
    }
    .form-group input.error,
    .form-group select.error,
    .form-group textarea.error {
      border-color: #EF4444;
      box-shadow  : 0 0 0 2px rgba(239,68,68,.18);
    }

    /* Champ adresse en lecture seule (rempli par la carte) */
    .addr-readonly {
      background : var(--anthracite3) !important;
      cursor     : not-allowed;
      color      : var(--grey-light) !important;
    }

    /* ---- Messages d'erreur sous les champs ---- */
    .error-message {
      color      : #EF4444;
      font-size  : .68rem;
      margin-top : .28rem;
      display    : none;
    }
    .error-message.visible { display: block; }

    /* =====================================================
       BLOC ADRESSE AVEC BOUTON "OUVRIR LA CARTE"
       ===================================================== */
    .addr-group {
      display  : flex;
      gap      : .5rem;
      align-items: stretch;
    }
    .addr-group input {
      flex        : 1;
      min-width   : 0;
    }
    /* Bouton carte */
    .btn-map {
      display    : flex;
      align-items: center;
      gap        : .35rem;
      padding    : .6rem .85rem;
      background : var(--cyan-dim);
      border     : 1px solid var(--cyan-border);
      color      : var(--cyan);
      font-size  : .8rem;
      font-weight: 600;
      letter-spacing:.05em;
      cursor     : pointer;
      white-space: nowrap;
      border-radius: 3px;
      transition : all .25s;
      flex-shrink: 0;
    }
    .btn-map:hover {
      background : var(--cyan);
      color      : var(--anthracite);
    }
    .btn-map i { font-size:.85rem; }

    /* =====================================================
       MODALE CARTE (s'ouvre PAR DESSUS la modale livraison)
       ===================================================== */
    .map-modal {
      display    : none;
      position   : fixed;
      inset      : 0;
      background : rgba(0,0,0,.92);
      z-index    : 3000;    /* z-index supérieur à la modale principale */
      justify-content: center;
      align-items    : center;
      backdrop-filter: blur(4px);
    }
    .map-modal.active { display: flex; }

    .map-modal-content {
      background   : var(--anthracite2);
      border       : 1px solid var(--cyan-border);
      width        : 94%;
      max-width    : 860px;
      border-radius: 8px;
      overflow     : hidden;
      animation    : modalFadeIn .3s ease;
      display      : flex;
      flex-direction: column;
    }

    /* En-tête modale carte */
    .map-modal-header {
      padding      : .9rem 1.2rem;
      background   : var(--anthracite3);
      border-bottom: 1px solid var(--cyan-border);
      display      : flex;
      align-items  : center;
      gap          : .8rem;
    }
    .map-modal-header h4 {
      flex       : 1;
      font-family: 'Barlow Condensed', sans-serif;
      font-size  : 1.05rem;
      color      : var(--white);
      letter-spacing: .04em;
    }
    .map-modal-header h4 i { color:var(--cyan); margin-right:.35rem; }
    .map-modal-header .map-modal-close {
      background : none;
      border     : none;
      color      : var(--grey);
      font-size  : 1.5rem;
      cursor     : pointer;
      transition : color .2s;
    }
    .map-modal-header .map-modal-close:hover { color: var(--cyan); }

    /* Instruction au-dessus de la carte */
    .map-instruction {
      padding    : .6rem 1.2rem;
      font-size  : .8rem;
      color      : var(--grey-light);
      background : var(--anthracite);
      border-bottom: 1px solid var(--anthracite3);
      display    : flex;
      align-items: center;
      gap        : .5rem;
    }
    .map-instruction i { color: var(--cyan); font-size:.85rem; }

    /* Conteneur de la carte Leaflet */
    #leaflet-map {
      width  : 100%;
      height : 420px;
    }
    @media (max-width:600px) { #leaflet-map { height: 300px; } }

    /* Barre de coordonnées sous la carte */
    .map-coords-bar {
      padding    : .75rem 1.2rem;
      background : var(--anthracite3);
      border-top : 1px solid var(--anthracite3);
      display    : flex;
      align-items: center;
      gap        : 1rem;
      flex-wrap  : wrap;
    }
    .map-coords-item {
      display    : flex;
      flex-direction: column;
      gap        : .15rem;
    }
    .map-coords-label {
      font-size  : .65rem;
      text-transform: uppercase;
      letter-spacing:.08em;
      color      : var(--grey);
      font-weight: 600;
    }
    .map-coords-value {
      font-family: 'Courier New', monospace;
      font-size  : .9rem;
      font-weight: 700;
      color      : var(--cyan);
    }
    .map-coords-place {
      flex       : 1;
      font-size  : .83rem;
      color      : var(--grey-light);
      font-style : italic;
    }
    /* Bouton "Valider ce point" */
    .btn-validate-point {
      margin-left: auto;
      padding    : .55rem 1.2rem;
      background : var(--cyan);
      color      : var(--anthracite);
      border     : none;
      border-radius: 3px;
      font-family: 'Barlow Condensed', sans-serif;
      font-size  : .85rem;
      font-weight: 700;
      letter-spacing:.08em;
      cursor     : pointer;
      transition : all .25s;
      display    : flex;
      align-items: center;
      gap        : .4rem;
    }
    .btn-validate-point:hover { background: #00b8cc; transform: translateY(-1px); }
    .btn-validate-point:disabled {
      opacity: .45;
      cursor : not-allowed;
      transform: none;
    }

    /* ---- Prix estimé ---- */
    .price-display {
      background : var(--cyan-dim);
      border     : 1px solid var(--cyan-border);
      padding    : .9rem 1rem;
      text-align : center;
      margin     : .8rem 0;
      border-radius: 4px;
    }
    .price-display span {
      font-size  : 1.45rem;
      font-weight: 700;
      color      : var(--cyan);
    }

    /* ---- Messages succès/erreur dans la modale livraison ---- */
    .form-message {
      padding      : .75rem .95rem;
      margin-bottom: 1rem;
      border-radius: 4px;
      font-size    : .84rem;
      display      : none;
    }
    .form-message.success {
      background: rgba(16,185,129,.14);
      color     : #10B981;
      border    : 1px solid rgba(16,185,129,.3);
    }
    .form-message.error {
      background: rgba(239,68,68,.14);
      color     : #EF4444;
      border    : 1px solid rgba(239,68,68,.3);
    }
    .form-message.visible { display:block; }

    /* ---- Pied de modale livraison ---- */
    .modal-footer {
      padding : 1rem 1.5rem 1.4rem;
      display : flex;
      justify-content: flex-end;
      gap     : .9rem;
    }
    .btn-primary {
      background : var(--cyan);
      color      : var(--anthracite);
      border     : none;
      padding    : .65rem 1.7rem;
      font-family: 'Barlow Condensed', sans-serif;
      font-size  : .83rem;
      letter-spacing:.1em;
      text-transform: uppercase;
      cursor     : pointer;
      border-radius: 3px;
      transition : all .25s;
      display    : flex;
      align-items: center;
      gap        : .4rem;
    }
    .btn-primary:hover  { background:#00b8cc; transform:translateY(-2px); }
    .btn-primary:disabled { opacity:.5; cursor:not-allowed; transform:none; }

    .btn-secondary {
      background : var(--anthracite3);
      color      : var(--grey-light);
      border     : none;
      padding    : .65rem 1.4rem;
      font-family: 'Barlow Condensed', sans-serif;
      font-size  : .83rem;
      letter-spacing:.1em;
      cursor     : pointer;
      border-radius: 3px;
      transition : all .25s;
    }
    .btn-secondary:hover { background:var(--anthracite); color:var(--white); }

    /* ---- Spinner de chargement ---- */
    .loading-spinner {
      display  : none;
      width    : 16px; height: 16px;
      border   : 2px solid var(--anthracite);
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin .75s linear infinite;
    }
    .loading-spinner.active { display:inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

  <!-- ====================================================
       EN-TÊTE DE NAVIGATION
       ==================================================== -->
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
          <a href="us_livraison.php">Mes livraisons</a>
          <a href="us_catalogue.php" class="active">Catalogue</a>
          <a href="../views/us_profil.php">Profil</a>
          <a href="contact.php">Contact</a>
          <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['mail'] ?? 'Utilisateur'); ?></span>
            <button class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i></button>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- ====================================================
       CONTENU PRINCIPAL — LISTE DES LIVREURS
       ==================================================== -->
  <main class="main">
    <div class="container">

      <h1 class="page-title">Catalogue des livreurs</h1>
      <p class="page-subtitle">Choisissez le livreur adapté à vos besoins</p>

      <!-- --- Filtres --- -->
      <div class="filters-section">
        <div class="filters-title">
          <i class="fas fa-sliders-h"></i>
          <span>Filtrer les livreurs</span>
        </div>
        <div class="filters-grid">
          <div class="filter-group">
            <label><i class="fas fa-motorcycle"></i> Type de véhicule</label>
            <select id="filterVehicle">
              <option value="all">Tous les types</option>
              <option value="moto">🏍️ Moto</option>
              <option value="tricycle">🛺 Tricycle</option>
              <option value="camionnette">🚐 Camionnette</option>
              <option value="camion">🚛 Camion</option>
            </select>
          </div>
          <div class="filter-group">
            <label><i class="fas fa-weight-hanging"></i> Poids min du colis (kg)</label>
            <input type="number" id="filterWeight" placeholder="Capacité min requise" min="0" step="10" />
          </div>
          <button class="reset-btn" id="resetBtn">
            <i class="fas fa-undo-alt"></i> Réinitialiser
          </button>
        </div>
      </div>

      <div class="stats">
        <div class="result-count">
          <i class="fas fa-truck"></i>
          <span id="driverCount">0</span> livreur(s) disponible(s)
        </div>
      </div>

      <!-- Grille des cartes livreurs (remplie en JS) -->
      <div id="driversGrid" class="drivers-grid"></div>

    </div>
  </main>

  <!-- ====================================================
       MODALE — FORMULAIRE DE LIVRAISON
       ==================================================== -->
  <div class="modal" id="livraisonModal">
    <div class="modal-content">

      <!-- En-tête -->
      <div class="modal-header">
        <h3 class="modal-title"><i class="fas fa-box"></i> Nouvelle livraison</h3>
        <button class="modal-close" onclick="closeModal()" aria-label="Fermer">&times;</button>
      </div>

      <!-- Corps -->
      <div class="modal-body">

        <!-- Message retour (succès / erreur) -->
        <div class="form-message" id="formMessage"></div>

        <!-- Récap du livreur sélectionné -->
        <div class="driver-info-card" id="driverInfo">
          <p><strong><i class="fas fa-user"></i> Livreur :</strong> <span id="selectedDriverName">—</span></p>
          <p><strong><i class="fas fa-phone-alt"></i> Tél. :</strong> <span id="selectedDriverPhone">—</span></p>
          <p><strong><i class="fas fa-weight-hanging"></i> Capacité :</strong> <span id="selectedDriverWeight">—</span> kg</p>
          <p><strong><i class="fas fa-tag"></i> Véhicule :</strong> <span id="selectedDriverVehicle">—</span></p>
        </div>

        <form id="livraisonForm" novalidate>

          <!-- ─── ADRESSE DE RAMASSAGE ─── -->
          <div class="form-group">
            <label><i class="fas fa-map-marker-alt"></i> Adresse de ramassage</label>
            <div class="addr-group">
              <!--
                Champ en lecture seule : les coordonnées GPS seront injectées
                ici automatiquement après sélection sur la carte.
              -->
              <input
                type="text"
                id="pickupAddress"
                class="addr-readonly"
                placeholder="Cliquez sur « Choisir sur la carte »"
                readonly
              />
              <!--
                Bouton qui ouvre la modale-carte en mode "ramassage"
              -->
              <button type="button" class="btn-map" onclick="openMapModal('pickup')">
                <i class="fas fa-map-marked-alt"></i> Carte
              </button>
            </div>
            <!-- Coordonnées brutes envoyées en BD (lat,lng) -->
            <input type="hidden" id="pickupCoords" />
            <div class="error-message" id="errorPickup">Veuillez sélectionner l'adresse de ramassage sur la carte.</div>
          </div>

          <!-- ─── ADRESSE DE DÉPÔT ─── -->
          <div class="form-group">
            <label><i class="fas fa-flag-checkered"></i> Adresse de dépôt</label>
            <div class="addr-group">
              <input
                type="text"
                id="dropoffAddress"
                class="addr-readonly"
                placeholder="Cliquez sur « Choisir sur la carte »"
                readonly
              />
              <button type="button" class="btn-map" onclick="openMapModal('dropoff')">
                <i class="fas fa-map-marked-alt"></i> Carte
              </button>
            </div>
            <input type="hidden" id="dropoffCoords" />
            <div class="error-message" id="errorDropoff">Veuillez sélectionner l'adresse de dépôt sur la carte.</div>
          </div>

          <!-- ─── POIDS + TYPE VÉHICULE ─── -->
          <div class="form-row">
            <div class="form-group">
              <label><i class="fas fa-weight-hanging"></i> Poids du colis (kg)</label>
              <input type="number" id="poids" step="0.1" min="0.1" placeholder="Ex : 5.5" oninput="calculatePrice()" />
              <div class="error-message" id="errorPoids">Veuillez entrer un poids valide (> 0).</div>
              <div class="error-message" id="errorPoidsCapacite">Le poids dépasse la capacité du livreur.</div>
            </div>
            <div class="form-group">
              <label><i class="fas fa-truck"></i> Type de véhicule</label>
              <!--
                Ce champ est rempli automatiquement avec le véhicule
                du livreur sélectionné — non modifiable par l'utilisateur.
              -->
              <select id="typeVehicule" disabled></select>
            </div>
          </div>

          <!-- ─── INSTRUCTIONS SPÉCIALES ─── -->
          <div class="form-group">
            <label><i class="fas fa-sticky-note"></i> Instructions spéciales <span style="color:var(--grey);font-weight:400;">(optionnel)</span></label>
            <textarea id="instructions" rows="2" placeholder="Code porte, étage, fragilité du colis…"></textarea>
          </div>

          <!-- ─── PRIX ESTIMÉ ─── -->
          <div class="price-display">
            <i class="fas fa-money-bill-wave"></i> Prix estimé :
            <span id="estimatedPrice">0</span> FCFA
          </div>

          <!-- Champs cachés envoyés au serveur -->
          <input type="hidden" id="livreurId"  value="" />
          <input type="hidden" id="distance"   value="0" />

        </form>
      </div><!-- /.modal-body -->

      <!-- Pied de modale -->
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal()">Annuler</button>
        <button class="btn-primary" id="submitBtn" onclick="submitLivraison()">
          <i class="fas fa-check-circle"></i> Confirmer la livraison
          <span class="loading-spinner" id="loadingSpinner"></span>
        </button>
      </div>

    </div><!-- /.modal-content -->
  </div><!-- /#livraisonModal -->

  <!-- ====================================================
       MODALE CARTE LEAFLET
       Ouverte au clic sur "Carte" pour ramassage OU dépôt.
       ==================================================== -->
  <div class="map-modal" id="mapModal">
    <div class="map-modal-content">

      <!-- En-tête -->
      <div class="map-modal-header">
        <h4 id="mapModalTitle"><i class="fas fa-map-marked-alt"></i> Sélectionner un point</h4>
        <button class="map-modal-close" onclick="closeMapModal()" aria-label="Fermer la carte">&times;</button>
      </div>

      <!-- Instruction -->
      <div class="map-instruction">
        <i class="fas fa-hand-pointer"></i>
        Cliquez sur l'endroit souhaité sur la carte pour placer le marqueur.
        Vous pouvez aussi <strong style="color:var(--cyan);">faire glisser</strong> le marqueur après l'avoir posé.
      </div>

      <!-- Barre de recherche de lieu (utilise Nominatim via proxy PHP) -->
      <div style="padding:.6rem 1rem;background:var(--anthracite);border-bottom:1px solid var(--anthracite3);display:flex;gap:.5rem;">
        <input
          type="text"
          id="mapSearchInput"
          placeholder="Rechercher un lieu au Cameroun…"
          autocomplete="off"
          style="flex:1;padding:.55rem .9rem;background:var(--anthracite2);border:1px solid var(--anthracite3);
                 color:var(--white);font-size:.85rem;border-radius:3px;"
          onkeydown="if(event.key==='Enter') mapSearch()"
        />
        <button onclick="mapSearch()" style="padding:.55rem 1rem;background:var(--cyan);color:var(--anthracite);
                border:none;border-radius:3px;font-weight:700;cursor:pointer;font-size:.82rem;">
          <i class="fas fa-search"></i>
        </button>
      </div>

      <!-- Carte Leaflet -->
      <div id="leaflet-map"></div>

      <!-- Barre de coordonnées + bouton validation -->
      <div class="map-coords-bar">
        <div class="map-coords-item">
          <span class="map-coords-label">Latitude</span>
          <span class="map-coords-value" id="mapLat">—</span>
        </div>
        <div class="map-coords-item">
          <span class="map-coords-label">Longitude</span>
          <span class="map-coords-value" id="mapLng">—</span>
        </div>
        <div class="map-coords-place" id="mapPlaceName">
          Aucun point sélectionné
        </div>
        <!--
          Bouton désactivé jusqu'à ce qu'un point soit sélectionné.
          Au clic, il injecte les coordonnées dans le bon champ du formulaire.
        -->
        <button class="btn-validate-point" id="btnValidatePoint" onclick="validateMapPoint()" disabled>
          <i class="fas fa-check"></i> Valider ce point
        </button>
      </div>

    </div><!-- /.map-modal-content -->
  </div><!-- /#mapModal -->


  <!-- ====================================================
       SCRIPTS
       ==================================================== -->

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
  /* ============================================================
     SECTION 1 — DONNÉES LIVREURS (injectées depuis PHP)
     ============================================================ */

  /** @type {Array} Tableau de tous les livreurs depuis la BD */
  const driversData = <?php echo json_encode(array_values($drivers)); ?>;

  /** Labels d'affichage pour les types de véhicule */
  const vehicleLabels = {
    moto       : 'Moto',
    tricycle   : 'Tricycle',
    camionnette: 'Camionnette',
    camion     : 'Camion'
  };

  /** Icônes Font Awesome par type de véhicule */
  const vehicleIcons = {
    moto       : 'fa-motorcycle',
    tricycle   : 'fa-truck-pickup',
    camionnette: 'fa-truck',
    camion     : 'fa-truck-moving'
  };

  /**
   * Tarifs en FCFA par km selon le type de véhicule.
   * Ces valeurs servent à l'estimation côté client uniquement ;
   * le prix définitif est recalculé côté serveur.
   */
  const tarifs = {
    moto       : 300,
    tricycle   : 400,
    camionnette: 500,
    camion     : 700
  };

  /** Livreur actuellement sélectionné dans la modale */
  let selectedDriver = null;


  /* ============================================================
     SECTION 2 — RENDU DES CARTES LIVREURS
     ============================================================ */

  /**
   * Génère le HTML des étoiles de notation.
   * @param {number} note  Note entre 0 et 5
   * @returns {string}     HTML des icônes d'étoiles
   */
  function renderStars(note) {
    if (!note) return '<i class="far fa-star"></i>'.repeat(5);
    const full  = Math.floor(note);
    const half  = (note % 1) >= 0.5;
    const empty = 5 - full - (half ? 1 : 0);
    return '<i class="fas fa-star"></i>'.repeat(full)
         + (half ? '<i class="fas fa-star-half-alt"></i>' : '')
         + '<i class="far fa-star"></i>'.repeat(empty);
  }

  /**
   * Échappe les caractères HTML dangereux.
   * @param {string} str
   * @returns {string}
   */
  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m])
    );
  }

  /**
   * Construit le HTML d'une carte livreur.
   * @param {Object} driver  Données d'un livreur
   * @returns {string}       HTML de la carte
   */
  function createDriverCard(driver) {
    const icon     = vehicleIcons[driver.type_vehicule]  || 'fa-truck';
    const label    = vehicleLabels[driver.type_vehicule] || driver.type_vehicule;
    const note     = driver.note    || 0;
    const nbAvis   = driver.nb_avis || 0;
    const capacite = driver.capacite_poids || 500;

    return `
      <div class="driver-card" data-id="${driver.id_livreur}">
        <div class="card-header">
          <i class="fas ${icon} driver-icon"></i>
          <div class="driver-name">${escapeHtml(driver.prenom)} ${escapeHtml(driver.nom)}</div>
        </div>
        <div class="card-body">
          <div class="info-row">
            <span class="info-label"><i class="fas fa-tag"></i> Véhicule</span>
            <span class="info-value">
              <span class="vehicle-badge"><i class="fas ${icon}"></i> ${label}</span>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-weight-hanging"></i> Capacité max</span>
            <span class="info-value">${capacite} kg</span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-star"></i> Note</span>
            <span class="info-value rating">
              ${renderStars(note)}
              <span class="rating-value">${note}</span>
              <span class="rating-count">(${nbAvis} avis)</span>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-phone-alt"></i> Contact</span>
            <span class="info-value">${escapeHtml(driver.numero)}</span>
          </div>
        </div>
        <div class="card-footer">
          <button class="select-btn" onclick="openModal(${driver.id_livreur})">
            <i class="fas fa-check-circle"></i> Sélectionner ce livreur
          </button>
        </div>
      </div>`;
  }

  /**
   * Filtre et affiche les livreurs dans la grille selon les critères actifs.
   */
  function filterDrivers() {
    const vehicleFilter = document.getElementById('filterVehicle').value;
    const weightFilter  = parseFloat(document.getElementById('filterWeight').value) || 0;

    const filtered = driversData.filter(d => {
      const cap = d.capacite_poids || 500;
      if (vehicleFilter !== 'all' && d.type_vehicule !== vehicleFilter) return false;
      if (cap < weightFilter) return false;
      return true;
    });

    document.getElementById('driverCount').textContent = filtered.length;

    const grid = document.getElementById('driversGrid');
    grid.innerHTML = filtered.length === 0
      ? `<div class="empty-state" style="grid-column:1/-1;">
           <i class="fas fa-search"></i>
           <h3>Aucun livreur trouvé</h3>
           <p>Essayez de modifier vos critères de recherche</p>
         </div>`
      : filtered.map(createDriverCard).join('');
  }


  /* ============================================================
     SECTION 3 — MODALE LIVRAISON (ouverture / fermeture)
     ============================================================ */

  /**
   * Ouvre la modale de livraison pour le livreur donné.
   * Remplit les infos récap et réinitialise le formulaire.
   * @param {number} driverId  id_livreur du livreur choisi
   */
  function openModal(driverId) {
    selectedDriver = driversData.find(d => d.id_livreur == driverId);
    if (!selectedDriver) return;

    const cap = selectedDriver.capacite_poids || 500;

    // Remplissage du récap livreur
    document.getElementById('selectedDriverName').textContent    = `${selectedDriver.prenom} ${selectedDriver.nom}`;
    document.getElementById('selectedDriverPhone').textContent   = selectedDriver.numero;
    document.getElementById('selectedDriverWeight').textContent  = cap;
    document.getElementById('selectedDriverVehicle').textContent = vehicleLabels[selectedDriver.type_vehicule] || selectedDriver.type_vehicule;

    // Champs cachés
    document.getElementById('livreurId').value = selectedDriver.id_livreur;

    // Type de véhicule (auto, non modifiable)
    const sel = document.getElementById('typeVehicule');
    sel.innerHTML = `<option value="${selectedDriver.type_vehicule}">
      ${vehicleLabels[selectedDriver.type_vehicule] || selectedDriver.type_vehicule}
      (capacité : ${cap} kg)
    </option>`;

    // Remise à zéro des champs adresse et coords
    resetAddressField('pickup');
    resetAddressField('dropoff');
    document.getElementById('poids').value        = '';
    document.getElementById('instructions').value = '';
    document.getElementById('estimatedPrice').textContent = '0';
    document.getElementById('distance').value     = '0';

    clearErrors();
    hideMessage();

    document.getElementById('livraisonModal').classList.add('active');
  }

  /** Ferme la modale livraison et remet tout à zéro. */
  function closeModal() {
    document.getElementById('livraisonModal').classList.remove('active');
    selectedDriver = null;
    clearErrors();
    hideMessage();
  }

  /**
   * Réinitialise l'affichage et les valeurs d'un champ adresse.
   * @param {'pickup'|'dropoff'} type
   */
  function resetAddressField(type) {
    document.getElementById(type + 'Address').value = '';
    document.getElementById(type + 'Coords').value  = '';
  }


  /* ============================================================
     SECTION 4 — MODALE CARTE LEAFLET
     ============================================================ */

  /**
   * Type d'adresse en cours de sélection ('pickup' ou 'dropoff').
   * Détermine dans quel champ les coordonnées seront injectées.
   */
  let currentMapTarget = null;

  /**
   * Objet carte Leaflet (initialisé une seule fois, puis réutilisé).
   * @type {L.Map|null}
   */
  let leafletMap = null;

  /** Marqueur courant sur la carte. @type {L.Marker|null} */
  let leafletMarker = null;

  /**
   * Dernier point sélectionné sur la carte.
   * @type {{lat: number, lng: number, name: string}|null}
   */
  let pendingPoint = null;

  /**
   * Ouvre la modale-carte pour sélectionner une adresse.
   * @param {'pickup'|'dropoff'} target  Champ à remplir après sélection
   */
  function openMapModal(target) {
    currentMapTarget = target;
    pendingPoint     = null;

    // Mise à jour du titre de la modale carte
    const titles = {
      pickup : '📍 Sélectionner l\'adresse de ramassage',
      dropoff: '🏁 Sélectionner l\'adresse de dépôt'
    };
    document.getElementById('mapModalTitle').innerHTML =
      `<i class="fas fa-map-marked-alt"></i> ${titles[target]}`;

    // Remise à zéro des affichages de coordonnées
    document.getElementById('mapLat').textContent       = '—';
    document.getElementById('mapLng').textContent       = '—';
    document.getElementById('mapPlaceName').textContent = 'Aucun point sélectionné';
    document.getElementById('btnValidatePoint').disabled = true;
    document.getElementById('mapSearchInput').value    = '';

    // Ouverture de la modale
    document.getElementById('mapModal').classList.add('active');

    // Initialisation de la carte (une seule fois)
    if (!leafletMap) {
      initLeafletMap();
    } else {
      // La carte existe déjà : forcer le recalcul de taille
      // (nécessaire si elle était dans un élément caché au premier chargement)
      setTimeout(() => leafletMap.invalidateSize(), 100);
    }

    // Pré-positionner le marqueur si une coordonnée existe déjà pour ce champ
    const existingCoords = document.getElementById(target + 'Coords').value;
    if (existingCoords) {
      const [lat, lng] = existingCoords.split(',').map(Number);
      placeLeafletMarker(lat, lng, document.getElementById(target + 'Address').value);
      leafletMap.setView([lat, lng], 14, { animate: false });
    }
  }

  /** Ferme la modale-carte sans valider. */
  function closeMapModal() {
    document.getElementById('mapModal').classList.remove('active');
    currentMapTarget = null;
    pendingPoint     = null;
  }

  /**
   * Initialise la carte Leaflet avec les tuiles OpenStreetMap,
   * les limites géographiques du Cameroun et les événements.
   */
  function initLeafletMap() {
    // Limites du Cameroun [SW, NE]
    const cmBounds = [[1.65, 8.4], [13.08, 16.19]];

    leafletMap = L.map('leaflet-map', {
      center          : [5.5, 12.3],
      zoom            : 6,
      minZoom         : 5,
      maxZoom         : 18,
      maxBounds       : cmBounds,
      maxBoundsViscosity: 1.0
    });

    // Fond de carte OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom    : 19
    }).addTo(leafletMap);

    // Clic sur la carte → placer le marqueur + géocodage inverse
    leafletMap.on('click', function(e) {
      const { lat, lng } = e.latlng;
      placeLeafletMarker(lat, lng, null);
      updateCoordDisplay(lat, lng, 'Géolocalisation en cours…');
      reverseGeocodeLeaflet(lat, lng, name => {
        updateCoordDisplay(lat, lng, name);
        pendingPoint = { lat, lng, name };
      });
    });
  }

  /**
   * Icône SVG personnalisée pour le marqueur Leaflet
   * (couleur cyan du thème, forme pin classique).
   */
  const customIcon = L.divIcon({
    className: '',
    html: `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="38" viewBox="0 0 28 38">
             <path d="M14 0C6.27 0 0 6.27 0 14c0 9.87 14 24 14 24S28 23.87 28 14C28 6.27 21.73 0 14 0z"
                   fill="#00D4E8" stroke="#008fa0" stroke-width="1.5"/>
             <circle cx="14" cy="14" r="6" fill="#1C1F24"/>
           </svg>`,
    iconSize  : [28, 38],
    iconAnchor: [14, 38],
    popupAnchor: [0, -40]
  });

  /**
   * Place ou déplace le marqueur draggable sur la carte Leaflet.
   * @param {number}      lat        Latitude
   * @param {number}      lng        Longitude
   * @param {string|null} placeName  Nom du lieu (affiché dans le popup)
   */
  function placeLeafletMarker(lat, lng, placeName) {
    if (leafletMarker) leafletMap.removeLayer(leafletMarker);

    leafletMarker = L.marker([lat, lng], {
      icon     : customIcon,
      draggable: true
    }).addTo(leafletMap);

    // Popup avec coordonnées
    const updatePopup = (la, ln, name) => {
      leafletMarker.bindPopup(
        `<b style="color:#007A5E;">${name || 'Point sélectionné'}</b><br>
         Lat : ${la.toFixed(6)}<br>Lng : ${ln.toFixed(6)}`
      ).openPopup();
    };
    updatePopup(lat, lng, placeName);

    // Événement : déplacement du marqueur à la souris
    leafletMarker.on('dragend', function(e) {
      const pos = e.target.getLatLng();
      const la  = pos.lat, ln = pos.lng;
      updateCoordDisplay(la, ln, 'Géolocalisation en cours…');
      reverseGeocodeLeaflet(la, ln, name => {
        updatePopup(la, ln, name);
        updateCoordDisplay(la, ln, name);
        pendingPoint = { lat: la, lng: ln, name };
      });
    });
  }

  /**
   * Met à jour l'affichage des coordonnées dans la barre sous la carte.
   * Active aussi le bouton "Valider ce point".
   * @param {number} lat
   * @param {number} lng
   * @param {string} placeName
   */
  function updateCoordDisplay(lat, lng, placeName) {
    document.getElementById('mapLat').textContent       = lat.toFixed(6);
    document.getElementById('mapLng').textContent       = lng.toFixed(6);
    document.getElementById('mapPlaceName').textContent = placeName || '—';
    document.getElementById('btnValidatePoint').disabled = false;
  }

  /**
   * Géocodage inverse via le proxy PHP (Nominatim).
   * Appelle le même proxy PHP que la carte d'origine du projet.
   * @param {number}   lat
   * @param {number}   lng
   * @param {Function} callback  Reçoit le nom du lieu (string)
   */
  function reverseGeocodeLeaflet(lat, lng, callback) {
    const url = `../models/proxy.php?action=reverse&lat=${lat}&lon=${lng}`;
    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data && data.display_name) {
          // Garde les 3 premiers segments : ville, région, pays
          const short = data.display_name.split(',').slice(0, 3).join(', ').trim();
          callback(short);
        } else {
          callback('Lieu inconnu');
        }
      })
      .catch(() => callback('Erreur de géolocalisation'));
  }

  /**
   * Recherche un lieu dans la carte via Nominatim (proxy PHP).
   * Appelée quand l'utilisateur saisit un terme dans le champ de recherche.
   */
  function mapSearch() {
    const q = document.getElementById('mapSearchInput').value.trim();
    if (!q) return;

    const url = `../models/proxy.php?action=search&q=${encodeURIComponent(q)}&limit=1`;
    fetch(url)
      .then(r => r.json())
      .then(results => {
        if (!results.length) {
          alert('Aucun résultat trouvé pour : ' + q);
          return;
        }
        const item = results[0];
        const lat  = parseFloat(item.lat);
        const lng  = parseFloat(item.lon);
        const name = item.display_name.split(',').slice(0, 3).join(', ').trim();

        leafletMap.setView([lat, lng], 14, { animate: true });
        placeLeafletMarker(lat, lng, name);
        updateCoordDisplay(lat, lng, name);
        pendingPoint = { lat, lng, name };
      })
      .catch(() => alert('Erreur lors de la recherche.'));
  }

  /**
   * Valide le point sélectionné sur la carte et l'injecte dans
   * le champ adresse correspondant du formulaire de livraison.
   *
   * Ce sont les COORDONNÉES GPS (lat,lng) qui sont enregistrées
   * dans la base de données (champs adresse_ramassage / adresse_depot).
   */
  function validateMapPoint() {
    if (!pendingPoint || !currentMapTarget) return;

    const { lat, lng, name } = pendingPoint;

    // Format stocké en BD : "lat,lng"  (ex : "4.061536,9.774678")
    const coordsStr = `${lat.toFixed(6)},${lng.toFixed(6)}`;

    // Champ caché (valeur envoyée au serveur)
    document.getElementById(currentMapTarget + 'Coords').value = coordsStr;

    // Champ visible (lecture seule — affiche nom du lieu + coordonnées)
    document.getElementById(currentMapTarget + 'Address').value =
      name ? `${name} (${coordsStr})` : coordsStr;

    // Effacer l'erreur éventuelle sur ce champ
    document.getElementById(currentMapTarget + 'Address').classList.remove('error');
    document.getElementById('error' + capitalize(currentMapTarget)).classList.remove('visible');

    // Recalculer le prix si les deux points sont maintenant définis
    calculatePrice();

    // Fermer la modale carte
    closeMapModal();
  }

  /** Met en majuscule la première lettre d'une chaîne. */
  function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }


  /* ============================================================
     SECTION 5 — CALCUL DU PRIX ESTIMÉ
     ============================================================ */

  /**
   * Calcule et affiche le prix estimé de la livraison.
   *
   * La distance est calculée à partir des coordonnées GPS
   * des deux points (formule de Haversine).
   * Le prix = distance(km) × tarif(FCFA/km) du type de véhicule.
   */
  function calculatePrice() {
    const pickupCoordsVal  = document.getElementById('pickupCoords').value;
    const dropoffCoordsVal = document.getElementById('dropoffCoords').value;
    const poids            = parseFloat(document.getElementById('poids').value) || 0;

    // Impossible de calculer sans les deux points
    if (!pickupCoordsVal || !dropoffCoordsVal || !selectedDriver) {
      document.getElementById('estimatedPrice').textContent = '0';
      document.getElementById('distance').value = '0';
      return;
    }

    // Vérification capacité
    const cap = selectedDriver.capacite_poids || 500;
    if (poids > cap) {
      document.getElementById('estimatedPrice').textContent = 'Dépassement capacité';
      return;
    }

    // Décodage des coordonnées
    const [lat1, lng1] = pickupCoordsVal.split(',').map(Number);
    const [lat2, lng2] = dropoffCoordsVal.split(',').map(Number);

    // Calcul distance (Haversine, résultat en km)
    const distKm = haversineKm(lat1, lng1, lat2, lng2);
    document.getElementById('distance').value = distKm.toFixed(2);

    const tarif = tarifs[selectedDriver.type_vehicule] || 300;
    const prix  = Math.round(distKm * tarif);

    document.getElementById('estimatedPrice').textContent = prix.toLocaleString('fr-FR');
  }

  /**
   * Formule de Haversine : calcule la distance à vol d'oiseau
   * entre deux points GPS en kilomètres.
   * @param {number} lat1
   * @param {number} lon1
   * @param {number} lat2
   * @param {number} lon2
   * @returns {number} Distance en km
   */
  function haversineKm(lat1, lon1, lat2, lon2) {
    const R    = 6371; // Rayon de la Terre en km
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a    = Math.sin(dLat/2) ** 2
               + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
               * Math.sin(dLon/2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  /** Convertit des degrés en radians. */
  function toRad(deg) { return deg * Math.PI / 180; }


  /* ============================================================
     SECTION 6 — VALIDATION DU FORMULAIRE
     ============================================================ */

  /** Masque tous les messages d'erreur et retire la classe error. */
  function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => el.classList.remove('visible'));
    document.querySelectorAll('.form-group input, .form-group select, .form-group textarea')
            .forEach(el => el.classList.remove('error'));
  }

  /**
   * Affiche l'erreur liée à un champ.
   * @param {string} fieldId  ID de l'input
   * @param {string} errId    ID du message d'erreur
   */
  function showError(fieldId, errId) {
    document.getElementById(fieldId)?.classList.add('error');
    document.getElementById(errId)?.classList.add('visible');
  }

  /** Masque le message retour (succès/erreur) dans la modale. */
  function hideMessage() {
    const el = document.getElementById('formMessage');
    el.classList.remove('visible', 'success', 'error');
    el.textContent = '';
  }

  /**
   * Affiche un message dans la modale et la ferme si succès.
   * @param {string} text  Texte du message
   * @param {'success'|'error'} type
   */
  function showMessage(text, type) {
    const el = document.getElementById('formMessage');
    el.textContent = text;
    el.classList.add('visible', type);
    if (type === 'success') {
      setTimeout(() => closeModal(), 2000);
    }
  }

  /**
   * Valide tous les champs du formulaire de livraison.
   * @returns {boolean} true si tout est valide
   */
  function validateForm() {
    let ok = true;
    clearErrors();

    // Adresse de ramassage (vérification du champ caché coords)
    if (!document.getElementById('pickupCoords').value) {
      showError('pickupAddress', 'errorPickup');
      ok = false;
    }

    // Adresse de dépôt
    if (!document.getElementById('dropoffCoords').value) {
      showError('dropoffAddress', 'errorDropoff');
      ok = false;
    }

    // Poids
    const poids = parseFloat(document.getElementById('poids').value) || 0;
    if (poids <= 0) {
      showError('poids', 'errorPoids');
      ok = false;
    }

    // Dépassement capacité
    const cap = selectedDriver?.capacite_poids || 500;
    if (poids > cap && poids > 0) {
      showError('poids', 'errorPoidsCapacite');
      ok = false;
    }

    return ok;
  }


  /* ============================================================
     SECTION 7 — SOUMISSION DU FORMULAIRE (AJAX → traiter_livraison.php)
     ============================================================ */

  /**
   * Soumet le formulaire de livraison via fetch.
   *
   * Les champs envoyés en BD :
   *  - livreur_id       → id_livreur
   *  - adresse_ramassage → coordonnées GPS "lat,lng" du point de ramassage
   *  - adresse_depot    → coordonnées GPS "lat,lng" du point de dépôt
   *  - poids            → poids du colis (kg)
   *  - distance         → distance calculée (km, Haversine)
   *  - prix             → prix estimé (FCFA)
   *  - type_vehicule    → type du véhicule du livreur
   *  - instructions     → instructions spéciales (optionnel)
   */
  function submitLivraison() {
    if (!validateForm()) return;

    const priceText = document.getElementById('estimatedPrice').textContent;
    if (priceText === 'Dépassement capacité' || priceText === '0') return;

    // Valeur numérique du prix (enlever les espaces de formatage)
    const prix = priceText.replace(/\s/g, '');

    // Désactivation bouton + spinner
    const btn     = document.getElementById('submitBtn');
    const spinner = document.getElementById('loadingSpinner');
    btn.disabled = true;
    spinner.classList.add('active');

    const formData = new FormData();
    formData.append('livreur_id',        document.getElementById('livreurId').value);
    formData.append('adresse_ramassage', document.getElementById('pickupCoords').value);   // coords GPS
    formData.append('adresse_depot',     document.getElementById('dropoffCoords').value);  // coords GPS
    formData.append('poids',             document.getElementById('poids').value);
    formData.append('distance',          document.getElementById('distance').value);
    formData.append('prix',              prix);
    formData.append('type_vehicule',     selectedDriver.type_vehicule);
    formData.append('instructions',      document.getElementById('instructions').value);

    fetch('../models/traiter_livraison.php', {
      method: 'POST',
      body  : formData
    })
    // Lire d'abord la réponse brute (texte) pour pouvoir afficher
    // le contenu même si ce n'est pas du JSON valide (ex: warning PHP)
    .then(r => r.text())
    .then(rawText => {
      btn.disabled = false;
      spinner.classList.remove('active');

      let data;
      try {
        data = JSON.parse(rawText);
      } catch(e) {
        // La réponse n'est pas du JSON → afficher le texte brut (warning PHP, etc.)
        showMessage('❌ Réponse serveur invalide (non-JSON) :\n' + rawText, 'error');
        console.error('Réponse brute du serveur :', rawText);
        return;
      }

      if (data.success) {
        showMessage('✅ Livraison créée avec succès !', 'success');
      } else {
        // Afficher le message précis retourné par le serveur
        let msg = '❌ ' + data.message;

        // En mode debug : afficher aussi les données POST et session reçues
        if (data.debug_post || data.debug_session) {
          console.group('🔍 Debug traiter_livraison.php');
          console.log('POST reçu :', data.debug_post);
          console.log('Session reçue :', data.debug_session);
          console.groupEnd();
          // Ajouter un résumé dans la modale
          msg += '\n\n[DEBUG] Ouvrez la console (F12) pour voir les détails POST/session.';
        }

        showMessage(msg, 'error');
      }
    })
    .catch(err => {
      btn.disabled = false;
      spinner.classList.remove('active');
      console.error('Erreur réseau fetch :', err);
      showMessage('❌ Erreur réseau : impossible de contacter traiter_livraison.php. Vérifiez le chemin du fichier.', 'error');
    });
  }


  /* ============================================================
     SECTION 8 — ÉVÉNEMENTS ET INITIALISATION
     ============================================================ */

  document.addEventListener('DOMContentLoaded', function() {

    // Affichage initial des livreurs
    filterDrivers();

    // Filtres
    document.getElementById('filterVehicle').addEventListener('change', filterDrivers);
    document.getElementById('filterWeight').addEventListener('input',  filterDrivers);
    document.getElementById('resetBtn').addEventListener('click', function() {
      document.getElementById('filterVehicle').value = 'all';
      document.getElementById('filterWeight').value  = '';
      filterDrivers();
    });

    // Recalcul du prix quand le poids change
    document.getElementById('poids').addEventListener('input', calculatePrice);

    // Menu hamburger
    const toggle = document.getElementById('menuToggle');
    const nav    = document.getElementById('navLinks');
    toggle?.addEventListener('click', () => {
      toggle.classList.toggle('active');
      nav.classList.toggle('active');
    });

    // Déconnexion
    document.getElementById('logoutBtn')?.addEventListener('click', function() {
      if (confirm('Voulez-vous vous déconnecter ?')) {
        window.location.href = 'logout.php';
      }
    });
  });

  // Fermeture des modales en cliquant sur le fond
  window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('livraisonModal')) closeModal();
    if (e.target === document.getElementById('mapModal'))       closeMapModal();
  });

  // Fermeture des modales avec la touche Échap
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (document.getElementById('mapModal').classList.contains('active'))       closeMapModal();
      else if (document.getElementById('livraisonModal').classList.contains('active')) closeModal();
    }
  });

  </script>
</body>
</html>