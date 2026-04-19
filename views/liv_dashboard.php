<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Livreur</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/dark/liv_css.css">

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
                    <a href="liv_dashboard.php"  class="active">Dashboard</a>
                    <a href="liv_livraison.php" >Mes Livraisons</a>
                    <a href="liv_note.php">Note</a>
                    <a href="liv_compte.php">Compte</a>
                    <div class="user-info">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                        <button class="logout-btn" ><i class="fas fa-sign-out-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="menu-overlay" id="menuOverlay"></div>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Tableau de bord</h1>
                <p class="page-subtitle">Bienvenue, Livreur</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Mes livraisons</span>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div class="stat-value" id="totalUsers">12</div>
                    <div class="stat-change change-positive"><i class="fas fa-arrow-up"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Mes notes</span>
                        <i class="fas fa-truck stat-icon"></i>
                    </div>
                    <div class="stat-value" id="totalDrivers">4</div>
                    <div class="stat-change change-positive"><i class="fas fa-arrow-up"></i> +3 nouveaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Statistique</span>
                        <i class="fas fa-box stat-icon"></i>
                    </div>
                    <div class="stat-value" id="totalDeliveries">22</div>
                    <div class="stat-change change-positive"><i class="fas fa-arrow-up"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Chiffre d'affaires</span>
                        <i class="fas fa-chart-line stat-icon"></i>
                    </div>
                    <div class="stat-value" id="totalRevenue">XXX XXX</div>
                    <div class="stat-change change-positive"><i class="fas fa-arrow-up"></i> +23% ce mois</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="fas fa-chart-line"></i> Évolution des livraisons</h3>
                    <canvas id="deliveriesChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Répartition par véhicule</h3>
                    <canvas id="vehicleChart"></canvas>
                </div>
            </div>

            <!-- Derniers utilisateurs -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-user-plus"></i> Derniers utilisateurs inscrits</div>
                    <button class="btn-add" onclick="openAddUserModal()"><i class="fas fa-plus"></i> Ajouter</button>
                </div>
                <table>
                    <thead>
                        <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Date</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="usersTableBody"></tbody>
                </table>
            </div>

            <!-- Dernières livraisons -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-truck"></i> Dernières livraisons</div>
                </div>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Client</th><th>Livreur</th><th>Montant</th><th>Statut</th><th>Date</th></tr>
                    </thead>
                    <tbody id="deliveriesTableBody"></tbody>
                </table>
            </div>
        </div>
    </main>


</body>
</html>
