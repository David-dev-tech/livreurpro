<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/dark/ad_css.css">

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
                    <a href="ad_dashboard.php">Dashboard</a>
                    <a href="ad_utilisateur.php" class="active">Utilisateurs</a>
                    <a href="ad_livreur.php">Livreurs</a>
                    <a href="ad_livraison.php">Livraisons</a>
                    <div class="user-info">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                        <button class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="menu-overlay" id="menuOverlay"></div>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Gestion des Utilisateurs</h1>
                <p class="page-subtitle">Gérez tous les utilisateurs enregistrés</p>
            </div>

            <!-- Content Area - Empty -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-users"></i> Liste des utilisateurs</div>
                    <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Ajouter</button>
                </div>
                <table>
                    <thead>
                        <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="mainTableBody">
                        <!-- Empty work zone -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un élément</h3>
                <button class="modal-close" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" id="newItemName" placeholder="Nom">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="newItemEmail" placeholder="email@exemple.com">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeAddModal()">Annuler</button>
                <button class="btn-primary" onclick="addItem()">Ajouter</button>
            </div>
        </div>
    </div>

    <script>
        // Menu Hamburger
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        const menuOverlay = document.getElementById('menuOverlay');

        function toggleMenu() {
            menuToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            if (navLinks.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function closeMenu() {
            if (menuToggle.classList.contains('active')) {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
                menuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        if (menuToggle) {
            menuToggle.addEventListener('click', toggleMenu);
        }

        if (menuOverlay) {
            menuOverlay.addEventListener('click', closeMenu);
        }

        const allNavLinks = document.querySelectorAll('.nav-links a');
        allNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMenu();
                return true;
            });
        });

        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }

        function addItem() {
            alert('Fonction à implémenter');
            closeAddModal();
        }

        function logout() {
            if (confirm('Voulez-vous vous déconnecter ?')) {
                window.location.href = 'index.html';
            }
        }
    </script>
</body>
</html>
