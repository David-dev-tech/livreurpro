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

  <link rel="stylesheet" href="../css/dark/us_css.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>












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
      max-width    : 780px;
      max-height   : 94vh;
      overflow-y   : auto;
      position     : relative;
      border-radius: 6px;
      animation    : modalFadeIn .3s ease;
    }
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

    .modal-body { padding: 1.4rem 1.5rem; }

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

    .addr-readonly {
      background : var(--anthracite3) !important;
      cursor     : not-allowed;
      color      : var(--grey-light) !important;
    }

    .error-message {
      color      : #EF4444;
      font-size  : .68rem;
      margin-top : .28rem;
      display    : none;
    }
    .error-message.visible { display: block; }

    .addr-group {
      display  : flex;
      gap      : .5rem;
      align-items: stretch;
    }
    .addr-group input { flex: 1; min-width: 0; }

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
    .btn-map:hover { background: var(--cyan); color: var(--anthracite); }
    .btn-map i { font-size:.85rem; }

    /* ── Modale carte ── */
    .map-modal {
      display    : none;
      position   : fixed;
      inset      : 0;
      background : rgba(0,0,0,.92);
      z-index    : 3000;
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

    /* Loader itinéraire dans la modale carte */
    .route-loader {
      display    : none;
      align-items: center;
      gap        : .5rem;
      padding    : .4rem 1.2rem;
      background : var(--anthracite3);
      font-size  : .72rem;
      color      : var(--grey-light);
      border-bottom: 1px solid var(--anthracite3);
    }
    .route-loader.active { display: flex; }
    .route-loader-dot {
      width: 5px; height: 5px;
      border-radius: 50%;
      background: var(--cyan);
      animation: pulse-dot .8s ease-in-out infinite;
    }
    .route-loader-dot:nth-child(2) { animation-delay: .16s; }
    .route-loader-dot:nth-child(3) { animation-delay: .32s; }
    @keyframes pulse-dot {
      0%,100% { opacity:.3; transform:scale(.8); }
      50%     { opacity:1;  transform:scale(1.2); }
    }

    #leaflet-map { width:100%; height:420px; }
    @media (max-width:600px) { #leaflet-map { height:300px; } }

    .map-coords-bar {
      padding    : .75rem 1.2rem;
      background : var(--anthracite3);
      border-top : 1px solid var(--anthracite3);
      display    : flex;
      align-items: center;
      gap        : 1rem;
      flex-wrap  : wrap;
    }
    .map-coords-item { display:flex; flex-direction:column; gap:.15rem; }
    .map-coords-label {
      font-size:.65rem; text-transform:uppercase; letter-spacing:.08em;
      color:var(--grey); font-weight:600;
    }
    .map-coords-value {
      font-family:'Courier New',monospace; font-size:.9rem;
      font-weight:700; color:var(--cyan);
    }
    .map-coords-place { flex:1; font-size:.83rem; color:var(--grey-light); font-style:italic; }

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
    .btn-validate-point:hover { background:#00b8cc; transform:translateY(-1px); }
    .btn-validate-point:disabled { opacity:.45; cursor:not-allowed; transform:none; }

    .price-display {
      background : var(--cyan-dim);
      border     : 1px solid var(--cyan-border);
      padding    : .9rem 1rem;
      text-align : center;
      margin     : .8rem 0;
      border-radius: 4px;
    }
    .price-display span { font-size:1.45rem; font-weight:700; color:var(--cyan); }

    .form-message {
      padding      : .75rem .95rem;
      margin-bottom: 1rem;
      border-radius: 4px;
      font-size    : .84rem;
      display      : none;
    }
    .form-message.success { background:rgba(16,185,129,.14); color:#10B981; border:1px solid rgba(16,185,129,.3); }
    .form-message.error   { background:rgba(239,68,68,.14);  color:#EF4444; border:1px solid rgba(239,68,68,.3);  }
    .form-message.visible { display:block; }

    .modal-footer {
      padding : 1rem 1.5rem 1.4rem;
      display : flex;
      justify-content: flex-end;
      gap     : .9rem;
    }
    .btn-primary {
      background : var(--cyan); color:var(--anthracite); border:none;
      padding    : .65rem 1.7rem;
      font-family: 'Barlow Condensed', sans-serif; font-size:.83rem;
      letter-spacing:.1em; text-transform:uppercase;
      cursor:pointer; border-radius:3px; transition:all .25s;
      display:flex; align-items:center; gap:.4rem;
    }
    .btn-primary:hover    { background:#00b8cc; transform:translateY(-2px); }
    .btn-primary:disabled { opacity:.5; cursor:not-allowed; transform:none; }

    .btn-secondary {
      background:var(--anthracite3); color:var(--grey-light); border:none;
      padding:.65rem 1.4rem;
      font-family:'Barlow Condensed',sans-serif; font-size:.83rem;
      letter-spacing:.1em; cursor:pointer; border-radius:3px; transition:all .25s;
    }
    .btn-secondary:hover { background:var(--anthracite); color:var(--white); }

    .loading-spinner {
      display:none; width:16px; height:16px;
      border:2px solid var(--anthracite); border-top-color:transparent;
      border-radius:50%; animation:spin .75s linear infinite;
    }
    .loading-spinner.active { display:inline-block; }
    @keyframes spin { to { transform:rotate(360deg); } }
  </style>
</head>
<body>

  <!-- ══════════════ HEADER ══════════════ -->
  <header class="header">
    <div class="container">
      <div class="nav-container">
        <div class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></div>
        <a href="../index.html" style="text-decoration:none;">
          <div style="display:inline-flex;align-items:center;justify-content:center;
                      width:75px;height:48px;background:#00D4E8;border:2px solid #00D4E8;
                      clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
                      font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;
                      font-size:1.3rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
        </a>
        <div class="nav-links" id="navLinks">
          <a href="../index.html">Accueil</a>
          <a href="us_livraison.php">Mes Commandes</a>
          <a href="us_catalogue.php" class="active">Catalogue</a>
          <a href="../views/us_profil.php">Profil</a>
          <a href="us_contact.php">Contact</a>
          <button id="themeToggle" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.2rem; padding:0 12px;">
    <i class="fas fa-moon"></i>
</button>
          <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['mail'] ?? 'Utilisateur'); ?></span>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ══════════════ CONTENU PRINCIPAL ══════════════ -->
  <main class="main">
    <div class="container">
      <h1 class="page-title">Catalogue des livreurs</h1>
      <p class="page-subtitle">Choisissez le livreur adapté à vos besoins</p>

      <div class="filters-section">
        <div class="filters-title"><i class="fas fa-sliders-h"></i><span>Filtrer les livreurs</span></div>
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
          <button class="reset-btn" id="resetBtn"><i class="fas fa-undo-alt"></i> Réinitialiser</button>
        </div>
      </div>

      <div class="stats">
        <div class="result-count">
          <i class="fas fa-truck"></i>
          <span id="driverCount">0</span> livreur(s) disponible(s)
        </div>
      </div>

      <div id="driversGrid" class="drivers-grid"></div>
    </div>
  </main>

  <!-- ══════════════ MODALE LIVRAISON ══════════════ -->
  <div class="modal" id="livraisonModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title"><i class="fas fa-box"></i> Nouvelle livraison</h3>
        <button class="modal-close" onclick="closeModal()" aria-label="Fermer">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-message" id="formMessage"></div>
        <div class="driver-info-card" id="driverInfo">
          <p><strong><i class="fas fa-user"></i> Livreur :</strong> <span id="selectedDriverName">—</span></p>
          <p><strong><i class="fas fa-phone-alt"></i> Tél. :</strong> <span id="selectedDriverPhone">—</span></p>
          <p><strong><i class="fas fa-weight-hanging"></i> Capacité :</strong> <span id="selectedDriverWeight">—</span> kg</p>
          <p><strong><i class="fas fa-tag"></i> Véhicule :</strong> <span id="selectedDriverVehicle">—</span></p>
        </div>

        <form id="livraisonForm" novalidate>
          <div class="form-group">
            <label><i class="fas fa-map-marker-alt"></i> Adresse de ramassage</label>
            <div class="addr-group">
              <input type="text" id="pickupAddress" class="addr-readonly" placeholder="Cliquez sur « Choisir sur la carte »" readonly />
              <button type="button" class="btn-map" onclick="openMapModal('pickup')">
                <i class="fas fa-map-marked-alt"></i> Carte
              </button>
            </div>
            <input type="hidden" id="pickupCoords" />
            <div class="error-message" id="errorPickup">Veuillez sélectionner l'adresse de ramassage sur la carte.</div>
          </div>

          <div class="form-group">
            <label><i class="fas fa-flag-checkered"></i> Adresse de dépôt</label>
            <div class="addr-group">
              <input type="text" id="dropoffAddress" class="addr-readonly" placeholder="Cliquez sur « Choisir sur la carte »" readonly />
              <button type="button" class="btn-map" onclick="openMapModal('dropoff')">
                <i class="fas fa-map-marked-alt"></i> Carte
              </button>
            </div>
            <input type="hidden" id="dropoffCoords" />
            <div class="error-message" id="errorDropoff">Veuillez sélectionner l'adresse de dépôt sur la carte.</div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label><i class="fas fa-weight-hanging"></i> Poids du colis (kg)</label>
              <input type="number" id="poids" step="0.1" min="0.1" placeholder="Ex : 5.5" oninput="calculatePrice()" />
              <div class="error-message" id="errorPoids">Veuillez entrer un poids valide (> 0).</div>
              <div class="error-message" id="errorPoidsCapacite">Le poids dépasse la capacité du livreur.</div>
            </div>
            <div class="form-group">
              <label><i class="fas fa-truck"></i> Type de véhicule</label>
              <select id="typeVehicule" disabled></select>
            </div>
          </div>

          <div class="form-group">
            <label><i class="fas fa-sticky-note"></i> Instructions spéciales <span style="color:var(--grey);font-weight:400;">(optionnel)</span></label>
            <textarea id="instructions" rows="2" placeholder="Code porte, étage, fragilité du colis…"></textarea>
          </div>

          <div class="price-display">
            <i class="fas fa-money-bill-wave"></i> Prix estimé :
            <span id="estimatedPrice">0</span> FCFA
          </div>

          <input type="hidden" id="livreurId" value="" />
          <input type="hidden" id="distance"  value="0" />
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal()">Annuler</button>
        <button class="btn-primary" id="submitBtn" onclick="submitLivraison()">
          <i class="fas fa-check-circle"></i> Confirmer la livraison
          <span class="loading-spinner" id="loadingSpinner"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- ══════════════ MODALE CARTE LEAFLET ══════════════ -->
  <div class="map-modal" id="mapModal">
    <div class="map-modal-content">

      <div class="map-modal-header">
        <h4 id="mapModalTitle"><i class="fas fa-map-marked-alt"></i> Sélectionner un point</h4>
        <button class="map-modal-close" onclick="closeMapModal()" aria-label="Fermer la carte">&times;</button>
      </div>

      <!-- <div class="map-instruction">
        <i class="fas fa-hand-pointer"></i>
        Cliquez sur l'endroit souhaité sur la carte pour placer le marqueur.
        Vous pouvez aussi <strong style="color:var(--cyan);">faire glisser</strong> le marqueur après l'avoir posé.
      </div> -->

      <!-- Barre de recherche avec autocomplétion + géolocalisation -->
      <div style="padding:.6rem 1rem;background:var(--anthracite);border-bottom:1px solid var(--anthracite3);position:relative;">
        <div style="display:flex;gap:.5rem;align-items:center;">

          <!-- Champ de recherche + suggestions -->
          <div style="position:relative;flex:1;">
            <input
              type="text"
              id="mapSearchInput"
              placeholder="Rechercher un lieu à Douala…"
              autocomplete="off"
              style="width:100%;padding:.55rem .9rem;background:var(--anthracite2);border:1px solid var(--anthracite3);
                     color:var(--white);font-size:.85rem;border-radius:3px;box-sizing:border-box;"
              oninput="mapSearchAutocomplete()"
              onkeydown="mapSearchKeydown(event)"
            />
            <ul id="mapSuggestions" style="
              display:none;position:absolute;top:100%;left:0;right:0;
              background:var(--anthracite2);border:1px solid var(--cyan-border);
              border-top:none;border-radius:0 0 6px 6px;list-style:none;
              margin:0;padding:0;z-index:9999;max-height:220px;overflow-y:auto;
              box-shadow:0 8px 24px rgba(0,0,0,.5);
            "></ul>
          </div>

          <!-- Bouton loupe -->
          <button onclick="mapSearchFirst()" title="Rechercher"
                  style="padding:.55rem 1rem;background:var(--cyan);color:var(--anthracite);
                         border:none;border-radius:3px;font-weight:700;cursor:pointer;font-size:.82rem;flex-shrink:0;">
            <i class="fas fa-search"></i>
          </button>

          <!-- Bouton géolocalisation -->
          <button onclick="locateMe()" id="locateBtn" title="Localiser ma position"
                  style="padding:.55rem .9rem;background:var(--anthracite2);color:var(--cyan);
                         border:1px solid var(--cyan-border);border-radius:3px;
                         cursor:pointer;font-size:.82rem;flex-shrink:0;
                         display:flex;align-items:center;gap:.35rem;
                         transition:background .2s,color .2s;white-space:nowrap;"
                  onmouseover="this.style.background='var(--cyan)';this.style.color='var(--anthracite)';"
                  onmouseout="this.style.background='var(--anthracite2)';this.style.color='var(--cyan)';">
            <i class="fas fa-location-arrow"></i>
            <span style="font-size:.78rem;font-weight:600;">Ma position</span>
          </button>

        </div>
      </div>

      <!-- Loader itinéraire -->
      <div class="route-loader" id="routeLoader">
        <div class="route-loader-dot"></div>
        <div class="route-loader-dot"></div>
        <div class="route-loader-dot"></div>
        <span>Calcul de l'itinéraire en cours…</span>
      </div>

      <div id="leaflet-map"></div>

      <div class="map-coords-bar">
        <div class="map-coords-item">
          <span class="map-coords-label">Latitude</span>
          <span class="map-coords-value" id="mapLat">—</span>
        </div>
        <div class="map-coords-item">
          <span class="map-coords-label">Longitude</span>
          <span class="map-coords-value" id="mapLng">—</span>
        </div>
        <div class="map-coords-place" id="mapPlaceName">Aucun point sélectionné</div>
        <button class="btn-validate-point" id="btnValidatePoint" onclick="validateMapPoint()" disabled>
          <i class="fas fa-check"></i> Valider ce point
        </button>
      </div>

    </div>
  </div>


  <!-- ══════════════ SCRIPTS ══════════════ -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
  /* ============================================================
     SECTION 1 — DONNÉES LIVREURS
     ============================================================ */
  const driversData = <?php echo json_encode(array_values($drivers)); ?>;

  const vehicleLabels = { moto:'Moto', tricycle:'Tricycle', camionnette:'Camionnette', camion:'Camion' };
  const vehicleIcons  = { moto:'fa-motorcycle', tricycle:'fa-truck-pickup', camionnette:'fa-truck', camion:'fa-truck-moving' };
  const tarifs        = { moto:300, tricycle:400, camionnette:500, camion:700 };

  let selectedDriver = null;

  // ── Centre et limites de Douala ──
  const DOUALA_CENTER = [4.0511, 9.7679];
  const DOUALA_ZOOM   = 13;
  // Bounding box autour de Douala (~30 km de rayon)
  const DOUALA_BOUNDS = [[3.85, 9.55], [4.25, 9.95]];

  /* ============================================================
     SECTION 2 — RENDU DES CARTES LIVREURS
     ============================================================ */
  function renderStars(note) {
    if (!note) return '<i class="far fa-star"></i>'.repeat(5);
    const full = Math.floor(note), half = (note % 1) >= 0.5, empty = 5 - full - (half ? 1 : 0);
    return '<i class="fas fa-star"></i>'.repeat(full)
         + (half ? '<i class="fas fa-star-half-alt"></i>' : '')
         + '<i class="far fa-star"></i>'.repeat(empty);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;' }[m]));
  }

  function createDriverCard(driver) {
    const icon = vehicleIcons[driver.type_vehicule] || 'fa-truck';
    const label = vehicleLabels[driver.type_vehicule] || driver.type_vehicule;
    const cap   = driver.capacite_poids || 500;
    return `
      <div class="driver-card" data-id="${driver.id_livreur}">
        <div class="card-header">
          <i class="fas ${icon} driver-icon"></i>
          <div class="driver-name">${escapeHtml(driver.prenom)} ${escapeHtml(driver.nom)}</div>
        </div>
        <div class="card-body">
          <div class="info-row">
            <span class="info-label"><i class="fas fa-tag"></i> Véhicule</span>
            <span class="info-value"><span class="vehicle-badge"><i class="fas ${icon}"></i> ${label}</span></span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-weight-hanging"></i> Capacité max</span>
            <span class="info-value">${cap} kg</span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-star"></i> Note</span>
            <span class="info-value rating">
              ${renderStars(driver.note)}
              <span class="rating-value">${driver.note}</span>
              <span class="rating-count">(${driver.nb_avis} avis)</span>
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

  function filterDrivers() {
    const vf = document.getElementById('filterVehicle').value;
    const wf = parseFloat(document.getElementById('filterWeight').value) || 0;
    const filtered = driversData.filter(d => {
      if (vf !== 'all' && d.type_vehicule !== vf) return false;
      if ((d.capacite_poids || 500) < wf) return false;
      return true;
    });
    document.getElementById('driverCount').textContent = filtered.length;
    const grid = document.getElementById('driversGrid');
    grid.innerHTML = filtered.length === 0
      ? `<div class="empty-state" style="grid-column:1/-1;">
           <i class="fas fa-search"></i><h3>Aucun livreur trouvé</h3>
           <p>Essayez de modifier vos critères de recherche</p>
         </div>`
      : filtered.map(createDriverCard).join('');
  }

  /* ============================================================
     SECTION 3 — MODALE LIVRAISON
     ============================================================ */
  function openModal(driverId) {
    selectedDriver = driversData.find(d => d.id_livreur == driverId);
    if (!selectedDriver) return;
    const cap = selectedDriver.capacite_poids || 500;
    document.getElementById('selectedDriverName').textContent    = `${selectedDriver.prenom} ${selectedDriver.nom}`;
    document.getElementById('selectedDriverPhone').textContent   = selectedDriver.numero;
    document.getElementById('selectedDriverWeight').textContent  = cap;
    document.getElementById('selectedDriverVehicle').textContent = vehicleLabels[selectedDriver.type_vehicule] || selectedDriver.type_vehicule;
    document.getElementById('livreurId').value = selectedDriver.id_livreur;
    const sel = document.getElementById('typeVehicule');
    sel.innerHTML = `<option value="${selectedDriver.type_vehicule}">
      ${vehicleLabels[selectedDriver.type_vehicule] || selectedDriver.type_vehicule} (capacité : ${cap} kg)
    </option>`;
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

  function closeModal() {
    document.getElementById('livraisonModal').classList.remove('active');
    selectedDriver = null;
    clearErrors();
    hideMessage();
  }

  function resetAddressField(type) {
    document.getElementById(type + 'Address').value = '';
    document.getElementById(type + 'Coords').value  = '';
  }

  /* ============================================================
     SECTION 4 — MODALE CARTE LEAFLET
     ============================================================ */
  let currentMapTarget = null;
  let leafletMap       = null;
  let leafletMarker    = null;
  let pendingPoint     = null;

  // Couches itinéraire à nettoyer entre deux sélections
  let routeLayers = [];

  function openMapModal(target) {
    currentMapTarget = target;
    pendingPoint     = null;

    const titles = {
      pickup : '📍 Sélectionner l\'adresse de ramassage',
      dropoff: '🏁 Sélectionner l\'adresse de dépôt'
    };
    document.getElementById('mapModalTitle').innerHTML =
      `<i class="fas fa-map-marked-alt"></i> ${titles[target]}`;

    document.getElementById('mapLat').textContent        = '—';
    document.getElementById('mapLng').textContent        = '—';
    document.getElementById('mapPlaceName').textContent  = 'Aucun point sélectionné';
    document.getElementById('btnValidatePoint').disabled = true;
    document.getElementById('mapSearchInput').value      = '';
    document.getElementById('routeLoader').classList.remove('active');

    document.getElementById('mapModal').classList.add('active');

    if (!leafletMap) {
      initLeafletMap();
    } else {
      setTimeout(() => leafletMap.invalidateSize(), 100);
    }

    // Pré-positionner si coordonnées existantes
    const existingCoords = document.getElementById(target + 'Coords').value;
    if (existingCoords) {
      const [lat, lng] = existingCoords.split(',').map(Number);
      placeLeafletMarker(lat, lng, document.getElementById(target + 'Address').value);
      leafletMap.setView([lat, lng], 15, { animate: false });
    }

    // Redessiner l'itinéraire si les deux points existent déjà
    drawRouteIfBothPoints();
  }

  function closeMapModal() {
    document.getElementById('mapModal').classList.remove('active');
    currentMapTarget = null;
    pendingPoint     = null;
  }

  /**
   * Initialise la carte Leaflet centrée sur Douala.
   * La carte et les recherches sont restreintes à Douala.
   */
  function initLeafletMap() {
    leafletMap = L.map('leaflet-map', {
      center            : DOUALA_CENTER,
      zoom              : DOUALA_ZOOM,
      minZoom           : 11,          // on ne peut pas trop dézoomer
      maxZoom           : 19,
      maxBounds         : DOUALA_BOUNDS,
      maxBoundsViscosity: 0.9
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(leafletMap);

    leafletMap.on('click', function(e) {
      const { lat, lng } = e.latlng;
      placeLeafletMarker(lat, lng, null);
      updateCoordDisplay(lat, lng, 'Géolocalisation en cours…');
      reverseGeocodeLeaflet(lat, lng, name => {
        updateCoordDisplay(lat, lng, name);
        pendingPoint = { lat, lng, name };
        drawRouteIfBothPoints();
      });
    });
  }

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

  function placeLeafletMarker(lat, lng, placeName) {
    if (leafletMarker) leafletMap.removeLayer(leafletMarker);

    leafletMarker = L.marker([lat, lng], { icon: customIcon, draggable: true }).addTo(leafletMap);

    const updatePopup = (la, ln, name) => {
      leafletMarker.bindPopup(
        `<b style="color:#007A5E;">${name || 'Point sélectionné'}</b><br>
         Lat : ${la.toFixed(6)}<br>Lng : ${ln.toFixed(6)}`
      ).openPopup();
    };
    updatePopup(lat, lng, placeName);

    leafletMarker.on('dragend', function(e) {
      const pos = e.target.getLatLng();
      const la = pos.lat, ln = pos.lng;
      updateCoordDisplay(la, ln, 'Géolocalisation en cours…');
      reverseGeocodeLeaflet(la, ln, name => {
        updatePopup(la, ln, name);
        updateCoordDisplay(la, ln, name);
        pendingPoint = { lat: la, lng: ln, name };
        drawRouteIfBothPoints();
      });
    });
  }

  function updateCoordDisplay(lat, lng, placeName) {
    document.getElementById('mapLat').textContent        = lat.toFixed(6);
    document.getElementById('mapLng').textContent        = lng.toFixed(6);
    document.getElementById('mapPlaceName').textContent  = placeName || '—';
    document.getElementById('btnValidatePoint').disabled = false;
  }

  function reverseGeocodeLeaflet(lat, lng, callback) {
    const url = `../models/proxy.php?action=reverse&lat=${lat}&lon=${lng}`;
    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data && data.display_name) {
          callback(data.display_name.split(',').slice(0, 3).join(', ').trim());
        } else {
          callback('Lieu inconnu');
        }
      })
      .catch(() => callback('Erreur de géolocalisation'));
  }

  /* ── Autocomplétion de recherche ── */
  let autocompleteTimer  = null;   // debounce timer
  let autocompleteIndex  = -1;     // index clavier actif dans la liste
  let autocompleteResults = [];    // derniers résultats Nominatim

  /**
   * Appelée à chaque frappe dans le champ de recherche.
   * Lance une requête Nominatim après 300 ms d'inactivité (debounce).
   */
  function mapSearchAutocomplete() {
    clearTimeout(autocompleteTimer);
    autocompleteIndex = -1;
    const q = document.getElementById('mapSearchInput').value.trim();
    if (q.length < 2) { hideSuggestions(); return; }

    autocompleteTimer = setTimeout(() => {
      const query   = `${q}, Douala, Cameroun`;
      const viewbox = `9.55,4.25,9.95,3.85`;
      const url = `../models/proxy.php?action=search&q=${encodeURIComponent(query)}&limit=6&viewbox=${viewbox}&bounded=1`;

      fetch(url)
        .then(r => r.json())
        .then(results => {
          autocompleteResults = results;
          showSuggestions(results);
        })
        .catch(() => hideSuggestions());
    }, 300);
  }

  /**
   * Gestion des touches clavier : flèches pour naviguer, Entrée pour valider, Échap pour fermer.
   */
  function mapSearchKeydown(e) {
    const list = document.getElementById('mapSuggestions');
    const items = list.querySelectorAll('li');

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      autocompleteIndex = Math.min(autocompleteIndex + 1, items.length - 1);
      highlightSuggestion(items);

    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      autocompleteIndex = Math.max(autocompleteIndex - 1, 0);
      highlightSuggestion(items);

    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (autocompleteIndex >= 0 && items[autocompleteIndex]) {
        items[autocompleteIndex].click();
      } else {
        mapSearchFirst();
      }

    } else if (e.key === 'Escape') {
      hideSuggestions();
    }
  }

  /** Met en surbrillance l'item actif dans la liste. */
  function highlightSuggestion(items) {
    items.forEach((li, i) => {
      li.style.background = i === autocompleteIndex
        ? 'rgba(0,212,232,0.15)'
        : '';
      li.style.color = i === autocompleteIndex ? '#00D4E8' : '#C9D1D9';
    });
  }

  /** Affiche la liste de suggestions. */
  function showSuggestions(results) {
    const list = document.getElementById('mapSuggestions');
    if (!results.length) { hideSuggestions(); return; }

    list.innerHTML = results.map((item, idx) => {
      const name = item.display_name.split(',').slice(0, 3).join(', ').trim();
      const icon = getPlaceIcon(item.type || item.class || '');
      return `<li data-idx="${idx}" style="
        padding:.55rem .9rem;
        cursor:pointer;
        font-size:.82rem;
        color:#C9D1D9;
        border-bottom:1px solid rgba(255,255,255,.05);
        display:flex; align-items:center; gap:.5rem;
        transition:background .15s;
      " onmouseenter="this.style.background='rgba(0,212,232,0.12)';this.style.color='#00D4E8';"
         onmouseleave="this.style.background='';this.style.color='#C9D1D9';"
         onclick="selectSuggestion(${idx})">
        <i class="fas ${icon}" style="color:#00D4E8;font-size:.75rem;flex-shrink:0;width:14px;text-align:center;"></i>
        <span>${name}</span>
      </li>`;
    }).join('');

    list.style.display = 'block';
  }

  /** Retourne l'icône Font Awesome selon le type de lieu Nominatim. */
  function getPlaceIcon(type) {
    const icons = {
      restaurant: 'fa-utensils', cafe: 'fa-coffee', bar: 'fa-cocktail',
      hospital: 'fa-hospital', school: 'fa-school', university: 'fa-graduation-cap',
      hotel: 'fa-hotel', supermarket: 'fa-shopping-cart', bank: 'fa-university',
      pharmacy: 'fa-pills', fuel: 'fa-gas-pump', parking: 'fa-parking',
      road: 'fa-road', residential: 'fa-home', street: 'fa-road',
      neighbourhood: 'fa-map-signs', suburb: 'fa-map-marker',
    };
    return icons[type] || 'fa-map-marker-alt';
  }

  /** Sélectionne une suggestion et place le marqueur sur la carte. */
  function selectSuggestion(idx) {
    const item = autocompleteResults[idx];
    if (!item) return;

    const lat  = parseFloat(item.lat);
    const lng  = parseFloat(item.lon);
    const name = item.display_name.split(',').slice(0, 3).join(', ').trim();

    document.getElementById('mapSearchInput').value = name;
    hideSuggestions();

    leafletMap.setView([lat, lng], 16, { animate: true });
    placeLeafletMarker(lat, lng, name);
    updateCoordDisplay(lat, lng, name);
    pendingPoint = { lat, lng, name };
    drawRouteIfBothPoints();
  }

  /** Cache la liste de suggestions. */
  function hideSuggestions() {
    const list = document.getElementById('mapSuggestions');
    list.style.display = 'none';
    list.innerHTML = '';
    autocompleteIndex = -1;
  }

  /** Sélectionne le premier résultat disponible (bouton loupe ou Entrée sans sélection). */
  function mapSearchFirst() {
    if (autocompleteResults.length) {
      selectSuggestion(0);
    } else {
      // Lancer une recherche directe si aucun résultat en cache
      const q = document.getElementById('mapSearchInput').value.trim();
      if (!q) return;
      const query   = `${q}, Douala, Cameroun`;
      const viewbox = `9.55,4.25,9.95,3.85`;
      const url = `../models/proxy.php?action=search&q=${encodeURIComponent(query)}&limit=1&viewbox=${viewbox}&bounded=1`;
      fetch(url)
        .then(r => r.json())
        .then(results => {
          if (!results.length) { alert('Aucun résultat trouvé à Douala pour : ' + q); return; }
          autocompleteResults = results;
          selectSuggestion(0);
        })
        .catch(() => alert('Erreur lors de la recherche.'));
    }
  }

  // Fermer les suggestions en cliquant ailleurs
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#mapSearchInput') && !e.target.closest('#mapSuggestions')) {
      hideSuggestions();
    }
  });

  /* ── Géolocalisation : localiser la position de l'utilisateur ── */
  function locateMe() {
    if (!navigator.geolocation) {
      alert('La géolocalisation n\'est pas supportée par votre navigateur.');
      return;
    }

    const btn = document.getElementById('locateBtn');
    // Indicateur visuel de chargement
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span style="font-size:.78rem;font-weight:600;">Localisation…</span>';
    btn.disabled  = true;

    navigator.geolocation.getCurrentPosition(
      function(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        // Restaurer le bouton
        btn.innerHTML = '<i class="fas fa-location-arrow"></i> <span style="font-size:.78rem;font-weight:600;">Ma position</span>';
        btn.disabled  = false;

        // Centrer la carte sur la position
        leafletMap.setView([lat, lng], 16, { animate: true });

        // Placer le marqueur + géocodage inverse
        placeLeafletMarker(lat, lng, 'Ma position');
        updateCoordDisplay(lat, lng, 'Géolocalisation en cours…');

        reverseGeocodeLeaflet(lat, lng, function(name) {
          updateCoordDisplay(lat, lng, name);
          pendingPoint = { lat, lng, name };
          // Mettre à jour le champ de recherche avec le nom du lieu
          document.getElementById('mapSearchInput').value = name;
          drawRouteIfBothPoints();
        });
      },
      function(error) {
        // Restaurer le bouton en cas d'erreur
        btn.innerHTML = '<i class="fas fa-location-arrow"></i> <span style="font-size:.78rem;font-weight:600;">Ma position</span>';
        btn.disabled  = false;

        const messages = {
          1: 'Accès à la localisation refusé. Autorisez la géolocalisation dans les paramètres de votre navigateur.',
          2: 'Position introuvable. Vérifiez votre connexion GPS ou réseau.',
          3: 'Délai dépassé. Veuillez réessayer.'
        };
        alert(messages[error.code] || 'Erreur de géolocalisation.');
      },
      {
        enableHighAccuracy: true,
        timeout           : 10000,
        maximumAge        : 0
      }
    );
  }

  /* ============================================================
     TRACÉ OSRM — vraie route entre les deux points
     ============================================================ */

  /**
   * Efface les couches d'itinéraire précédentes.
   */
  function clearRouteLayers() {
    routeLayers.forEach(l => { if (leafletMap) leafletMap.removeLayer(l); });
    routeLayers = [];
  }

  /**
   * Trace l'itinéraire via OSRM si les deux points (pickup et dropoff)
   * sont déjà enregistrés dans le formulaire.
   * Appelée après chaque sélection d'un point sur la carte.
   */
  function drawRouteIfBothPoints() {
    if (!leafletMap) return;

    const pickupVal  = document.getElementById('pickupCoords').value;
    const dropoffVal = document.getElementById('dropoffCoords').value;

    // Le point en cours de sélection remplace temporairement la valeur du champ
    let pickupCoords  = pickupVal  ? pickupVal.split(',').map(Number)  : null;
    let dropoffCoords = dropoffVal ? dropoffVal.split(',').map(Number) : null;

    // Mise à jour temporaire avec le pendingPoint si on est en train de sélectionner
    if (pendingPoint && currentMapTarget === 'pickup')  pickupCoords  = [pendingPoint.lat, pendingPoint.lng];
    if (pendingPoint && currentMapTarget === 'dropoff') dropoffCoords = [pendingPoint.lat, pendingPoint.lng];

    if (!pickupCoords || !dropoffCoords) return; // pas encore les deux points

    clearRouteLayers();
    document.getElementById('routeLoader').classList.add('active');

    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/`
      + `${pickupCoords[1]},${pickupCoords[0]};`
      + `${dropoffCoords[1]},${dropoffCoords[0]}`
      + `?overview=full&geometries=geojson`;

    fetch(osrmUrl)
      .then(r => r.json())
      .then(data => {
        document.getElementById('routeLoader').classList.remove('active');

        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
          const routeCoords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);

          // Ombre
          const shadow = L.polyline(routeCoords, {
            color: 'rgba(0,0,0,.45)', weight: 7, opacity: .4,
            lineJoin: 'round', lineCap: 'round', interactive: false
          }).addTo(leafletMap);
          shadow.bringToBack();
          routeLayers.push(shadow);

          // Route principale
          const line = L.polyline(routeCoords, {
            color: '#00D4E8', weight: 4, opacity: .95,
            lineJoin: 'round', lineCap: 'round'
          }).addTo(leafletMap);
          routeLayers.push(line);

          // Point milieu avec distance réelle
          const mid = Math.floor(routeCoords.length / 2);
          const distKm = (data.routes[0].distance / 1000).toFixed(2);
          const midMarker = L.circleMarker(routeCoords[mid], {
            radius: 6, fillColor: '#00D4E8', color: '#fff', weight: 2, fillOpacity: 1
          }).bindTooltip(`<b>Distance réelle</b><br>${distKm} km`, { permanent: false, direction: 'top' })
            .addTo(leafletMap);
          routeLayers.push(midMarker);

          // Mettre à jour la distance dans le formulaire
          document.getElementById('distance').value = distKm;
          calculatePrice();

        } else {
          // Fallback : ligne droite si OSRM ne couvre pas la zone
          tracerLigneDroite(pickupCoords, dropoffCoords);
        }
      })
      .catch(() => {
        document.getElementById('routeLoader').classList.remove('active');
        tracerLigneDroite(pickupCoords, dropoffCoords);
      });
  }

  /** Fallback : ligne droite en pointillés */
  function tracerLigneDroite(pickupCoords, dropoffCoords) {
    clearRouteLayers();
    const line = L.polyline([pickupCoords, dropoffCoords], {
      color: '#00D4E8', weight: 3, opacity: .85, dashArray: '10, 6'
    }).addTo(leafletMap);
    routeLayers.push(line);
  }

  /* ============================================================
     SECTION 5 — VALIDATION DU POINT + CALCUL PRIX
     ============================================================ */
  function validateMapPoint() {
    if (!pendingPoint || !currentMapTarget) return;
    const { lat, lng, name } = pendingPoint;
    const coordsStr = `${lat.toFixed(6)},${lng.toFixed(6)}`;

    document.getElementById(currentMapTarget + 'Coords').value   = coordsStr;
    document.getElementById(currentMapTarget + 'Address').value  =
      name ? `${name} (${coordsStr})` : coordsStr;

    document.getElementById(currentMapTarget + 'Address').classList.remove('error');
    document.getElementById('error' + capitalize(currentMapTarget)).classList.remove('visible');

    calculatePrice();
    closeMapModal();
  }

  function capitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

  function calculatePrice() {
    const pv = document.getElementById('pickupCoords').value;
    const dv = document.getElementById('dropoffCoords').value;
    const poids = parseFloat(document.getElementById('poids').value) || 0;

    if (!pv || !dv || !selectedDriver) {
      document.getElementById('estimatedPrice').textContent = '0';
      document.getElementById('distance').value = '0';
      return;
    }

    const cap = selectedDriver.capacite_poids || 500;
    if (poids > cap) {
      document.getElementById('estimatedPrice').textContent = 'Dépassement capacité';
      return;
    }

    // Utiliser la distance OSRM si disponible, sinon Haversine
    let distKm = parseFloat(document.getElementById('distance').value) || 0;
    if (!distKm) {
      const [lat1, lng1] = pv.split(',').map(Number);
      const [lat2, lng2] = dv.split(',').map(Number);
      distKm = haversineKm(lat1, lng1, lat2, lng2);
      document.getElementById('distance').value = distKm.toFixed(2);
    }

    const prix = Math.round(distKm * (tarifs[selectedDriver.type_vehicule] || 300));
    document.getElementById('estimatedPrice').textContent = prix.toLocaleString('fr-FR');
  }

  function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371, dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }
  function toRad(deg) { return deg * Math.PI / 180; }

  /* ============================================================
     SECTION 6 — VALIDATION FORMULAIRE
     ============================================================ */
  function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => el.classList.remove('visible'));
    document.querySelectorAll('.form-group input, .form-group select, .form-group textarea')
            .forEach(el => el.classList.remove('error'));
  }
  function showError(fieldId, errId) {
    document.getElementById(fieldId)?.classList.add('error');
    document.getElementById(errId)?.classList.add('visible');
  }
  function hideMessage() {
    const el = document.getElementById('formMessage');
    el.classList.remove('visible', 'success', 'error');
    el.textContent = '';
  }
  function showMessage(text, type) {
    const el = document.getElementById('formMessage');
    el.textContent = text;
    el.classList.add('visible', type);
    if (type === 'success') setTimeout(() => closeModal(), 2000);
  }
  function validateForm() {
    let ok = true;
    clearErrors();
    if (!document.getElementById('pickupCoords').value)  { showError('pickupAddress',  'errorPickup');         ok = false; }
    if (!document.getElementById('dropoffCoords').value) { showError('dropoffAddress', 'errorDropoff');        ok = false; }
    const poids = parseFloat(document.getElementById('poids').value) || 0;
    if (poids <= 0)                                       { showError('poids', 'errorPoids');                   ok = false; }
    const cap = selectedDriver?.capacite_poids || 500;
    if (poids > cap && poids > 0)                         { showError('poids', 'errorPoidsCapacite');           ok = false; }
    return ok;
  }

  /* ============================================================
     SECTION 7 — SOUMISSION AJAX
     ============================================================ */
  function submitLivraison() {
    if (!validateForm()) return;
    const priceText = document.getElementById('estimatedPrice').textContent;
    if (priceText === 'Dépassement capacité' || priceText === '0') return;
    const prix = priceText.replace(/\s/g, '');

    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('loadingSpinner');
    btn.disabled = true;
    spinner.classList.add('active');

    const formData = new FormData();
    formData.append('livreur_id',        document.getElementById('livreurId').value);
    formData.append('adresse_ramassage', document.getElementById('pickupCoords').value);
    formData.append('adresse_depot',     document.getElementById('dropoffCoords').value);
    formData.append('poids',             document.getElementById('poids').value);
    formData.append('distance',          document.getElementById('distance').value);
    formData.append('prix',              prix);
    formData.append('type_vehicule',     selectedDriver.type_vehicule);
    formData.append('instructions',      document.getElementById('instructions').value);

    fetch('../models/traiter_livraison.php', { method:'POST', body:formData })
      .then(r => r.text())
      .then(rawText => {
        btn.disabled = false;
        spinner.classList.remove('active');
        let data;
        try { data = JSON.parse(rawText); }
        catch(e) {
          showMessage('❌ Réponse serveur invalide :\n' + rawText, 'error');
          console.error('Réponse brute :', rawText);
          return;
        }
        if (data.success) {
          showMessage('✅ Livraison créée avec succès !', 'success');
        } else {
          let msg = '❌ ' + data.message;
          if (data.debug_post || data.debug_session) {
            console.group('🔍 Debug'); console.log('POST :', data.debug_post);
            console.log('Session :', data.debug_session); console.groupEnd();
            msg += '\n\n[DEBUG] Voir la console (F12).';
          }
          showMessage(msg, 'error');
        }
      })
      .catch(err => {
        btn.disabled = false;
        spinner.classList.remove('active');
        showMessage('❌ Erreur réseau : impossible de contacter traiter_livraison.php.', 'error');
        console.error('Fetch error :', err);
      });
  }

  /* ============================================================
     SECTION 8 — ÉVÉNEMENTS ET INITIALISATION
     ============================================================ */
  document.addEventListener('DOMContentLoaded', function() {
    filterDrivers();
    document.getElementById('filterVehicle').addEventListener('change', filterDrivers);
    document.getElementById('filterWeight').addEventListener('input',  filterDrivers);
    document.getElementById('resetBtn').addEventListener('click', function() {
      document.getElementById('filterVehicle').value = 'all';
      document.getElementById('filterWeight').value  = '';
      filterDrivers();
    });
    document.getElementById('poids').addEventListener('input', calculatePrice);
    const toggle = document.getElementById('menuToggle');
    const nav    = document.getElementById('navLinks');
    toggle?.addEventListener('click', () => { toggle.classList.toggle('active'); nav.classList.toggle('active'); });
  });

  window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('livraisonModal')) closeModal();
    if (e.target === document.getElementById('mapModal'))       closeMapModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (document.getElementById('mapModal').classList.contains('active'))            closeMapModal();
      else if (document.getElementById('livraisonModal').classList.contains('active')) closeModal();
    }
  });
  </script>





  <script>
    // Gestion du thème clair/sombre
    (function() {
        const theme = localStorage.getItem('theme');
        if (theme === 'light') {
            document.body.classList.add('light-theme');
            const toggle = document.getElementById('themeToggle');
            if (toggle) {
                const icon = toggle.querySelector('i');
                if (icon) icon.className = 'fas fa-sun';
            }
        }

        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                document.body.classList.toggle('light-theme');
                const isLight = document.body.classList.contains('light-theme');
                localStorage.setItem('theme', isLight ? 'light' : 'dark');
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
                }
            });
        }
    })();
</script>
</body>
</html>