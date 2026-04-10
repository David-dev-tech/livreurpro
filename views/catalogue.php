<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Catalogue des livreurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --anthracite:  #1C1F24;
            --anthracite2: #252930;
            --anthracite3: #2F343C;
            --cyan:        #00D4E8;
            --cyan-dim:    rgba(0,212,232,0.10);
            --cyan-border: rgba(0,212,232,0.22);
            --white:       #F2F4F7;
            --grey:        #6B7280;
            --grey-light:  #9CA3AF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--anthracite);
            color: var(--white);
        }

        /* ── Header ── */
        .header {
            background: var(--anthracite2);
            border-bottom: 1px solid var(--anthracite3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* ── Hamburger ── */
        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 22px;
            cursor: pointer;
            z-index: 200;
        }

        .menu-toggle span {
            width: 100%;
            height: 3px;
            background: var(--cyan);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .menu-toggle.active span:nth-child(1) { transform: translateY(9.5px) rotate(45deg); }
        .menu-toggle.active span:nth-child(2) { opacity: 0; }
        .menu-toggle.active span:nth-child(3) { transform: translateY(-9.5px) rotate(-45deg); }

        /* ── Nav links ── */
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--grey-light);
            transition: color .2s;
            cursor: pointer;
        }

        .nav-links a:hover { color: var(--cyan); }
        .nav-links a.active { color: var(--cyan); }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--anthracite3);
            padding: 0.5rem 1.2rem;
            border-radius: 4px;
            border-left: 2px solid var(--cyan);
        }

        .user-info i { color: var(--cyan); font-size: 1.1rem; }
        .user-info span { color: var(--grey-light); font-weight: 500; }

        .logout-btn {
            background: none;
            border: none;
            color: var(--grey);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .logout-btn:hover { color: var(--cyan); }



        /* ── Main ── */
        .main { padding: 40px 0 60px; }

        .page-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            letter-spacing: -.01em;
            text-transform: uppercase;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--grey-light);
            margin-bottom: 2rem;
            border-left: 3px solid var(--cyan);
            padding-left: 1rem;
            font-weight: 300;
        }

        /* ── Filtres ── */
        .filters-section {
            background: var(--anthracite2);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--anthracite3);
            position: relative;
        }

        .filters-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 3px; height: 100%;
            background: var(--cyan);
        }

        .filters-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Barlow Condensed', sans-serif;
            letter-spacing: .1em;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .filters-title i { color: var(--cyan); }

        .filters-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--grey);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            background: var(--anthracite);
            border: 1px solid var(--anthracite3);
            font-family: 'Barlow', sans-serif;
            font-size: 0.9rem;
            color: var(--white);
            transition: all 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 2px var(--cyan-dim);
        }

        .filter-group option { background: var(--anthracite2); }

        .reset-btn {
            background: var(--anthracite3);
            border: 1px solid var(--anthracite3);
            padding: 0.7rem 1.5rem;
            color: var(--grey-light);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Barlow', sans-serif;
            text-transform: uppercase;
            letter-spacing: .1em;
            font-size: 0.75rem;
        }

        .reset-btn:hover {
            background: var(--cyan);
            color: var(--anthracite);
            border-color: var(--cyan);
        }

        /* ── Stats ── */
        .stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .result-count { color: var(--grey-light); font-size: 0.9rem; }
        .result-count span { color: var(--cyan); font-weight: 700; }

        /* ── Grille ── */
        .drivers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
        }

        /* ── Carte livreur ── */
        .driver-card {
            background: var(--anthracite2);
            transition: all 0.3s ease;
            border: 1px solid var(--anthracite3);
            position: relative;
            overflow: hidden;
        }

        .driver-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 3px; height: 0;
            background: var(--cyan);
            transition: height 0.3s ease;
        }

        .driver-card:hover::before { height: 100%; }

        .driver-card:hover {
            transform: translateY(-6px);
            border-color: var(--cyan-border);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.3);
        }

        .card-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--anthracite3);
            position: relative;
        }

        .driver-icon { font-size: 4rem; color: var(--cyan); }

        .driver-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-top: 0.75rem;
            color: var(--white);
            letter-spacing: .05em;
        }

        .card-body { padding: 1.2rem 1.5rem; }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }

        .info-label { color: var(--grey); font-weight: 500; }
        .info-value { font-weight: 600; color: var(--white); }

        .vehicle-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--anthracite);
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
            border-left: 2px solid var(--cyan);
        }

        .vehicle-badge i { color: var(--cyan); }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            flex-wrap: wrap;
        }

        .rating i { color: #fbbf24; font-size: 0.9rem; }
        .rating-value { font-weight: 700; color: var(--white); }
        .rating-count { color: var(--grey); font-size: 0.8rem; }

        .card-footer {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--anthracite3);
            background: rgba(37,41,48,0.5);
        }

        .select-btn {
            width: 100%;
            background: transparent;
            border: 1px solid var(--cyan-border);
            padding: 0.8rem;
            color: var(--cyan);
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Barlow Condensed', sans-serif;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .select-btn:hover {
            background: var(--cyan);
            color: var(--anthracite);
            border-color: var(--cyan);
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
        }

        .empty-state i { font-size: 4rem; color: var(--grey); margin-bottom: 1rem; }
        .empty-state h3 { color: var(--grey-light); margin-bottom: 0.5rem; }
        .empty-state p { color: var(--grey); }

        /* ══════════════════════════════
           RESPONSIVE
        ══════════════════════════════ */

        /* Tablette */
        @media (max-width: 1024px) and (min-width: 769px) {
            .container { padding: 0 20px; }
            .drivers-grid { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
            .nav-links { gap: 1.5rem; }
            .filter-group { min-width: 160px; }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .container { padding: 0 16px; }

            .menu-toggle { display: flex; }

            .nav-links {
                position: fixed;
                top: 0; left: -100%;
                width: 70%;
                max-width: 300px;
                height: 100vh;
                background: var(--anthracite2);
                flex-direction: column;
                justify-content: flex-start;
                align-items: flex-start;
                padding: 80px 2rem 2rem;
                gap: 1.5rem;
                transition: left 0.3s ease;
                z-index: 150;
                box-shadow: 2px 0 20px rgba(0,0,0,0.3);
                border-right: 1px solid var(--cyan-border);
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links a {
                font-size: 1rem;
                padding: 0.5rem 0;
                width: 100%;
                border-bottom: 1px solid var(--anthracite3);
            }

            .user-info {
                width: 100%;
                justify-content: space-between;
                margin-top: 0.5rem;
            }

            .nav-container {
                justify-content: space-between;
                padding: 0.8rem 0;
            }

            .main { padding: 25px 0 40px; }

            .page-title {
                font-size: 1.6rem;
                text-align: center;
            }

            .page-subtitle {
                font-size: 0.9rem;
                text-align: center;
                border-left: none;
                border-top: 2px solid var(--cyan);
                border-bottom: 2px solid var(--cyan);
                padding: 0.8rem 0;
                margin-bottom: 1.5rem;
            }

            .filters-section { padding: 1rem; }

            .filters-grid { flex-direction: column; gap: 1rem; }

            .filter-group { width: 100%; min-width: auto; }
            .filter-group label { font-size: 0.7rem; }
            .filter-group select,
            .filter-group input { padding: 0.6rem 0.8rem; font-size: 0.85rem; }

            .reset-btn { width: 100%; padding: 0.6rem; margin-top: 0.5rem; }

            .stats { flex-direction: column; text-align: center; gap: 0.5rem; }
            .result-count { font-size: 0.85rem; }

            .drivers-grid { grid-template-columns: 1fr; gap: 1.2rem; }
            .driver-card { max-width: 100%; }
            .driver-card:hover { transform: translateY(-3px); }

            .card-header { padding: 1rem; }
            .driver-icon { font-size: 3rem; }
            .driver-name { font-size: 1.1rem; }
            .card-body { padding: 1rem; }
            .info-row { font-size: 0.85rem; flex-wrap: wrap; gap: 0.5rem; }
            .rating { gap: 0.2rem; }
            .rating i { font-size: 0.8rem; }
            .card-footer { padding: 0.8rem 1rem 1rem; }
            .select-btn { padding: 0.7rem; font-size: 0.85rem; }

            .empty-state { padding: 2.5rem 1rem; }
            .empty-state i { font-size: 3rem; }
            .empty-state h3 { font-size: 1.1rem; }
            .empty-state p { font-size: 0.85rem; }
        }

        /* Très petits mobiles */
        @media (max-width: 480px) {
            .container { padding: 0 12px; }
            .page-title { font-size: 1.4rem; }
            .page-subtitle { font-size: 0.8rem; }
            .filters-title { font-size: 0.8rem; }
            .filter-group label { font-size: 0.65rem; }
            .filter-group select,
            .filter-group input { padding: 0.5rem 0.7rem; font-size: 0.8rem; }
            .reset-btn { font-size: 0.7rem; padding: 0.5rem; }
            .driver-name { font-size: 1rem; }
            .info-row { font-size: 0.8rem; }
            .vehicle-badge { font-size: 0.7rem; padding: 0.2rem 0.6rem; }
            .rating-value { font-size: 0.85rem; }
            .rating-count { font-size: 0.7rem; }
            .select-btn { font-size: 0.8rem; padding: 0.6rem; }
        }

        /* Paysage mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .nav-links { width: 50%; padding: 60px 1.5rem 1.5rem; }
            .drivers-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">

                <!-- Bouton Hamburger -->
                <div class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <!-- Logo -->
                <a href="index.html" style="text-decoration: none;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
                </a>

                <!-- Liens de navigation -->
                <div class="nav-links" id="navLinks">
                    <a href="index.html">Accueil</a>
                    <a href="catalogue.php" class="active">Catalogue</a>
                    <a href="views/us_profil.php">Profil</a>
                    <a href="contact.php">Contact</a>
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span id="userPhone">+237 6XX XX XX XX</span>
                        <button class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i></button>
                    </div>
                </div>

            </div>
        </div>
    </header>



    <main class="main">
        <div class="container">
            <h1 class="page-title">Catalogue des livreurs</h1>
            <p class="page-subtitle">Choisissez le livreur adapté à vos besoins</p>

            <!-- Filtres -->
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
                        <label><i class="fas fa-weight-hanging"></i> Poids max (kg)</label>
                        <input type="number" id="filterWeight" placeholder="Poids min requis" min="0" step="10">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-star"></i> Note minimale</label>
                        <select id="filterRating">
                            <option value="0">Toutes les notes</option>
                            <option value="4">⭐ 4 étoiles +</option>
                            <option value="3">⭐⭐⭐ 3 étoiles +</option>
                            <option value="2">⭐⭐ 2 étoiles +</option>
                            <option value="1">⭐ 1 étoile +</option>
                        </select>
                    </div>
                    <button class="reset-btn" id="resetBtn">
                        <i class="fas fa-undo-alt"></i> Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats">
                <div class="result-count">
                    <i class="fas fa-truck"></i> <span id="driverCount">0</span> livreur(s) disponible(s)
                </div>
            </div>

            <!-- Grille des livreurs -->
            <div id="driversGrid" class="drivers-grid"></div>
        </div>
    </main>

    <script>
        /* ══════════════════════════════
           MENU HAMBURGER — CORRIGÉ
        ══════════════════════════════ */
        const menuToggle = document.getElementById('menuToggle');
        const navLinks   = document.getElementById('navLinks');

        /* Seul le bouton hamburger ouvre ET ferme le menu.
           Les liens naviguent normalement, sans interférence. */
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        /* Bouton déconnexion */
        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('Voulez-vous vous déconnecter ?')) {
                window.location.href = 'logout.php';
            }
        });

        /* ══════════════════════════════
           DONNÉES & CATALOGUE
        ══════════════════════════════ */
        const driversData = [
            { id: 1, nom: "Kamga",      prenom: "Jean",      type_vehicule: "moto",        poids_max: 30,   note: 4.8, nb_avis: 127, disponible: true, telephone: "697 12 34 56" },
            { id: 2, nom: "Ndi",        prenom: "Marie",     type_vehicule: "tricycle",    poids_max: 150,  note: 4.5, nb_avis: 89,  disponible: true, telephone: "698 23 45 67" },
            { id: 3, nom: "Fotso",      prenom: "Paul",      type_vehicule: "camionnette", poids_max: 1000, note: 4.9, nb_avis: 203, disponible: true, telephone: "699 34 56 78" },
            { id: 4, nom: "Abega",      prenom: "Claire",    type_vehicule: "moto",        poids_max: 30,   note: 4.2, nb_avis: 56,  disponible: true, telephone: "690 45 67 89" },
            { id: 5, nom: "Tchatchoua", prenom: "Eric",      type_vehicule: "camion",      poids_max: 5000, note: 4.7, nb_avis: 112, disponible: true, telephone: "691 56 78 90" },
            { id: 6, nom: "Mballa",     prenom: "Sandrine",  type_vehicule: "tricycle",    poids_max: 150,  note: 4.3, nb_avis: 78,  disponible: true, telephone: "692 67 89 01" },
            { id: 7, nom: "Essomba",    prenom: "David",     type_vehicule: "camionnette", poids_max: 1000, note: 4.6, nb_avis: 94,  disponible: true, telephone: "693 78 90 12" },
            { id: 8, nom: "Ngono",      prenom: "François",  type_vehicule: "moto",        poids_max: 30,   note: 4.1, nb_avis: 34,  disponible: true, telephone: "694 89 01 23" },
            { id: 9, nom: "Mvondo",     prenom: "Stéphane",  type_vehicule: "camion",      poids_max: 5000, note: 4.9, nb_avis: 156, disponible: true, telephone: "695 90 12 34" }
        ];

        const vehicleIcons = {
            moto:        "fa-motorcycle",
            tricycle:    "fa-truck-pickup",
            camionnette: "fa-truck",
            camion:      "fa-truck-moving"
        };

        const vehicleLabels = {
            moto:        "Moto",
            tricycle:    "Tricycle",
            camionnette: "Camionnette",
            camion:      "Camion"
        };

        function renderStars(note) {
            var full  = Math.floor(note);
            var half  = (note % 1) >= 0.5;
            var empty = 5 - full - (half ? 1 : 0);
            var s = '';
            for (var i = 0; i < full;  i++) s += '<i class="fas fa-star"></i>';
            if (half)                        s += '<i class="fas fa-star-half-alt"></i>';
            for (var i = 0; i < empty; i++) s += '<i class="far fa-star"></i>';
            return s;
        }

        function createDriverCard(driver) {
            var icon  = vehicleIcons[driver.type_vehicule]  || 'fa-truck';
            var label = vehicleLabels[driver.type_vehicule] || driver.type_vehicule;

            return '<div class="driver-card" data-id="' + driver.id + '">' +
                '<div class="card-header">' +
                    '<i class="fas ' + icon + ' driver-icon"></i>' +
                    '<div class="driver-name">' + driver.prenom + ' ' + driver.nom + '</div>' +
                '</div>' +
                '<div class="card-body">' +
                    '<div class="info-row">' +
                        '<span class="info-label"><i class="fas fa-tag"></i> Véhicule</span>' +
                        '<span class="info-value"><span class="vehicle-badge"><i class="fas ' + icon + '"></i> ' + label + '</span></span>' +
                    '</div>' +
                    '<div class="info-row">' +
                        '<span class="info-label"><i class="fas fa-weight-hanging"></i> Capacité max</span>' +
                        '<span class="info-value">' + driver.poids_max + ' kg</span>' +
                    '</div>' +
                    '<div class="info-row">' +
                        '<span class="info-label"><i class="fas fa-star"></i> Note</span>' +
                        '<span class="info-value rating">' +
                            renderStars(driver.note) +
                            '<span class="rating-value">' + driver.note + '</span>' +
                            '<span class="rating-count">(' + driver.nb_avis + ' avis)</span>' +
                        '</span>' +
                    '</div>' +
                    '<div class="info-row">' +
                        '<span class="info-label"><i class="fas fa-phone-alt"></i> Contact</span>' +
                        '<span class="info-value">' + driver.telephone + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="card-footer">' +
                    '<button class="select-btn" onclick="selectDriver(' + driver.id + ', \'' + driver.prenom + ' ' + driver.nom + '\')">' +
                        '<i class="fas fa-check-circle"></i> Sélectionner ce livreur' +
                    '</button>' +
                '</div>' +
            '</div>';
        }

        function filterDrivers() {
            var vehicleFilter = document.getElementById('filterVehicle').value;
            var weightFilter  = parseFloat(document.getElementById('filterWeight').value) || 0;
            var ratingFilter  = parseFloat(document.getElementById('filterRating').value) || 0;

            var filtered = driversData.filter(function(d) {
                if (!d.disponible) return false;
                if (vehicleFilter !== 'all' && d.type_vehicule !== vehicleFilter) return false;
                if (d.poids_max < weightFilter) return false;
                if (d.note < ratingFilter) return false;
                return true;
            });

            document.getElementById('driverCount').textContent = filtered.length;

            var grid = document.getElementById('driversGrid');
            if (filtered.length === 0) {
                grid.innerHTML =
                    '<div class="empty-state" style="grid-column:1/-1;">' +
                        '<i class="fas fa-search"></i>' +
                        '<h3>Aucun livreur trouvé</h3>' +
                        '<p>Essayez de modifier vos critères de recherche</p>' +
                    '</div>';
            } else {
                grid.innerHTML = filtered.map(createDriverCard).join('');
            }
        }

        function selectDriver(driverId, driverName) {
            sessionStorage.setItem('selectedDriverId',   driverId);
            sessionStorage.setItem('selectedDriverName', driverName);
            window.location.href = 'creer_livraison.php?livreur_id=' + driverId;
        }

        /* ── Init ── */
        document.addEventListener('DOMContentLoaded', function() {
            var phoneSpan = document.getElementById('userPhone');
            if (phoneSpan) phoneSpan.textContent = '697 12 34 56';

            filterDrivers();

            document.getElementById('filterVehicle').addEventListener('change', filterDrivers);
            document.getElementById('filterWeight').addEventListener('input',  filterDrivers);
            document.getElementById('filterRating').addEventListener('change', filterDrivers);
            document.getElementById('resetBtn').addEventListener('click', function() {
                document.getElementById('filterVehicle').value = 'all';
                document.getElementById('filterWeight').value  = '';
                document.getElementById('filterRating').value  = '0';
                filterDrivers();
            });
        });
    </script>
</body>
</html>