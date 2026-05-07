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
                    <a href="liv_dashboard.php">Dashboard</a>
                    <a href="liv_livraison.php" >Mes Livraisons</a>
                    <a href="liv_note.php"  class="active">Note</a>
                    <a href="liv_compte.php">Compte</a>
                    <div class="user-info">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="menu-overlay" id="menuOverlay"></div>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">?????</h1>
                <p class="page-subtitle">Analysez les performances de votre activité</p>
            </div>

            <!-- Content Area - Empty -->
            <!-- Stats Cards Section -->
            <div class="stats-grid">
                <!-- Empty stat cards area -->
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- Empty charts area -->
            </div>

            <!-- Reports Table Section -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-chart-bar"></i> Rapports disponibles</div>
                    <button class="btn-export" onclick="exportReport()"><i class="fas fa-download"></i> Exporter</button>
                </div>
                <table>
                    <thead>
                        <tr><th>Rapport</th><th>Période</th><th>Type</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="reportTableBody">
                        <!-- Empty work zone -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>


</body>
</html>
