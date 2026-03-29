<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Catalogue des livreurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --orange: #e67e22;
            --orange-deep: #cc711e;
            --mid: #475569;
            --light: #64748b;
            --bg-light: #f8fafc;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-light);
            color: #1e293b;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 2px solid var(--orange);
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

        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: var(--orange);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--mid);
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--orange);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f1f5f9;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
        }

        .user-info i {
            color: var(--orange);
            font-size: 1.1rem;
        }

        .user-info span {
            color: var(--mid);
            font-weight: 500;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--light);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .logout-btn:hover {
            color: var(--orange);
        }

        /* Main Content */
        .main {
            padding: 40px 0 60px;
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--light);
            margin-bottom: 2rem;
            border-left: 3px solid var(--orange);
            padding-left: 1rem;
        }

        /* Filtres */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid var(--border);
        }

        .filters-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filters-title i {
            color: var(--orange);
        }

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
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--mid);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            background: white;
            transition: all 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(230,126,34,0.1);
        }

        .reset-btn {
            background: #f1f5f9;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 12px;
            color: var(--mid);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .reset-btn:hover {
            background: #e2e8f0;
            color: var(--orange);
        }

        /* Stats */
        .stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .result-count {
            color: var(--light);
            font-size: 0.9rem;
        }

        .result-count span {
            color: var(--orange);
            font-weight: 700;
        }

        /* Grille des livreurs */
        .drivers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
        }

        /* Carte livreur */
        .driver-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .driver-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
            border-color: var(--orange);
        }

        .card-header {
            background: linear-gradient(135deg, #fef5e7 0%, #fff 100%);
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .driver-icon {
            font-size: 4rem;
            color: var(--orange);
        }

        .driver-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-top: 0.75rem;
            color: #0f172a;
        }

        .card-body {
            padding: 1.2rem 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }

        .info-label {
            color: var(--light);
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
        }

        .vehicle-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #f1f5f9;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }

        .vehicle-badge i {
            color: var(--orange);
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .rating i {
            color: #fbbf24;
            font-size: 0.9rem;
        }

        .rating-value {
            font-weight: 700;
            color: #0f172a;
        }

        .rating-count {
            color: var(--light);
            font-size: 0.8rem;
        }

        .card-footer {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fefefc;
        }

        .select-btn {
            width: 100%;
            background: var(--orange);
            border: none;
            padding: 0.8rem;
            border-radius: 40px;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .select-btn:hover {
            background: var(--orange-deep);
            transform: translateY(-2px);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--light);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--mid);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--light);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                text-align: center;
            }
            .drivers-grid {
                grid-template-columns: 1fr;
            }
            .filters-grid {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .page-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="logo">LIVREUR<span>PRO</span></div>
                <div class="nav-links">
                    <a href="../index.html">Accueil</a>
                    <a href="../views/catalogue.php" style="color: var(--orange);">Catalogue</a>
                    <a href="#">Mes livraisons</a>
                    <a href="#">Contact</a>
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span id="userPhone">+237 6XX XX XX XX</span>
                        <button class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
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
                    <button class="reset-btn" onclick="resetFilters()">
                        <i class="fas fa-undo-alt"></i> Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Résultats -->
            <div class="stats">
                <div class="result-count">
                    <i class="fas fa-truck"></i> <span id="driverCount">0</span> livreur(s) disponible(s)
                </div>
            </div>

            <!-- Grille des livreurs -->
            <div id="driversGrid" class="drivers-grid">
                <!-- Les cartes seront injectées ici par JavaScript -->
            </div>
        </div>
    </main>

    <script>
        // Données simulées des livreurs
        const driversData = [
            { id: 1, nom: "Kamga", prenom: "Jean", type_vehicule: "moto", poids_max: 30, note: 4.8, nb_avis: 127, disponible: true, telephone: "697123456" },
            { id: 2, nom: "Ndi", prenom: "Marie", type_vehicule: "tricycle", poids_max: 150, note: 4.5, nb_avis: 89, disponible: true, telephone: "698234567" },
            { id: 3, nom: "Fotso", prenom: "Paul", type_vehicule: "camionnette", poids_max: 1000, note: 4.9, nb_avis: 203, disponible: true, telephone: "699345678" },
            { id: 4, nom: "Abega", prenom: "Claire", type_vehicule: "moto", poids_max: 30, note: 4.2, nb_avis: 56, disponible: true, telephone: "690456789" },
            { id: 5, nom: "Tchatchoua", prenom: "Eric", type_vehicule: "camion", poids_max: 5000, note: 4.7, nb_avis: 112, disponible: true, telephone: "691567890" },
            { id: 6, nom: "Mballa", prenom: "Sandrine", type_vehicule: "tricycle", poids_max: 150, note: 4.3, nb_avis: 78, disponible: true, telephone: "692678901" },
            { id: 7, nom: "Essomba", prenom: "David", type_vehicule: "camionnette", poids_max: 1000, note: 4.6, nb_avis: 94, disponible: true, telephone: "693789012" },
            { id: 8, nom: "Ngono", prenom: "François", type_vehicule: "moto", poids_max: 30, note: 4.1, nb_avis: 34, disponible: true, telephone: "694890123" },
            { id: 9, nom: "Mvondo", prenom: "Stéphane", type_vehicule: "camion", poids_max: 5000, note: 4.9, nb_avis: 156, disponible: true, telephone: "695901234" }
        ];

        // Icônes par type de véhicule
        const vehicleIcons = {
            moto: "fa-motorcycle",
            tricycle: "fa-truck-pickup",
            camionnette: "fa-truck",
            camion: "fa-truck-moving"
        };

        const vehicleLabels = {
            moto: "Moto",
            tricycle: "Tricycle",
            camionnette: "Camionnette",
            camion: "Camion"
        };

        // Rendu des étoiles
        function renderStars(note) {
            const fullStars = Math.floor(note);
            const halfStar = note % 1 >= 0.5;
            const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
            
            let stars = '';
            for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star"></i>';
            if (halfStar) stars += '<i class="fas fa-star-half-alt"></i>';
            for (let i = 0; i < emptyStars; i++) stars += '<i class="far fa-star"></i>';
            return stars;
        }

        // Création d'une carte livreur
        function createDriverCard(driver) {
            const icon = vehicleIcons[driver.type_vehicule] || "fa-truck";
            const vehicleLabel = vehicleLabels[driver.type_vehicule] || driver.type_vehicule;
            
            return `
                <div class="driver-card" data-id="${driver.id}">
                    <div class="card-header">
                        <i class="fas ${icon} driver-icon"></i>
                        <div class="driver-name">${driver.prenom} ${driver.nom}</div>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-tag"></i> Véhicule</span>
                            <span class="info-value">
                                <span class="vehicle-badge">
                                    <i class="fas ${icon}"></i> ${vehicleLabel}
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-weight-hanging"></i> Capacité max</span>
                            <span class="info-value">${driver.poids_max} kg</span>
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
                            <span class="info-value">${driver.telephone}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="select-btn" onclick="selectDriver(${driver.id}, '${driver.prenom} ${driver.nom}')">
                            <i class="fas fa-check-circle"></i> Sélectionner ce livreur
                        </button>
                    </div>
                </div>
            `;
        }

        // Filtrage
        function filterDrivers() {
            const vehicleFilter = document.getElementById('filterVehicle').value;
            const weightFilter = parseFloat(document.getElementById('filterWeight').value) || 0;
            const ratingFilter = parseFloat(document.getElementById('filterRating').value) || 0;

            const filtered = driversData.filter(driver => {
                if (!driver.disponible) return false;
                if (vehicleFilter !== 'all' && driver.type_vehicule !== vehicleFilter) return false;
                if (driver.poids_max < weightFilter) return false;
                if (driver.note < ratingFilter) return false;
                return true;
            });

            // Mise à jour du compteur
            document.getElementById('driverCount').textContent = filtered.length;

            // Rendu des cartes
            const grid = document.getElementById('driversGrid');
            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-search"></i>
                        <h3>Aucun livreur trouvé</h3>
                        <p>Essayez de modifier vos critères de recherche</p>
                    </div>
                `;
            } else {
                grid.innerHTML = filtered.map(driver => createDriverCard(driver)).join('');
            }
        }

        // Réinitialiser les filtres
        function resetFilters() {
            document.getElementById('filterVehicle').value = 'all';
            document.getElementById('filterWeight').value = '';
            document.getElementById('filterRating').value = '0';
            filterDrivers();
        }

        // Sélection d'un livreur
        function selectDriver(driverId, driverName) {
            // Stocker en sessionStorage pour la page de création de livraison
            sessionStorage.setItem('selectedDriverId', driverId);
            sessionStorage.setItem('selectedDriverName', driverName);
            // Redirection vers la page de création de livraison
            window.location.href = `creer_livraison.php?livreur_id=${driverId}`;
        }

        // Déconnexion
        function logout() {
            if (confirm('Voulez-vous vous déconnecter ?')) {
                window.location.href = 'logout.php';
            }
        }

        // Simulation de l'affichage du numéro utilisateur
        document.addEventListener('DOMContentLoaded', () => {
            // Simuler un utilisateur connecté (à remplacer par session PHP)
            const userPhoneSpan = document.getElementById('userPhone');
            if (userPhoneSpan) {
                // À remplacer par la valeur de session
                userPhoneSpan.textContent = '697123456';
            }
            
            // Initialiser l'affichage
            filterDrivers();

            // Écouter les changements de filtres
            document.getElementById('filterVehicle').addEventListener('change', filterDrivers);
            document.getElementById('filterWeight').addEventListener('input', filterDrivers);
            document.getElementById('filterRating').addEventListener('change', filterDrivers);
        });
    </script>
</body>
</html>