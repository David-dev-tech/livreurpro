<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --success:     #10B981;
            --warning:     #F59E0B;
            --danger:      #EF4444;
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

        /* Header */
        .header {
            background: var(--anthracite2);
            border-bottom: 1px solid var(--anthracite3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1400px;
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

        /* Menu Hamburger */
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

        .menu-toggle.active span:nth-child(1) {
            transform: translateY(9.5px) rotate(45deg);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: translateY(-9.5px) rotate(-45deg);
        }

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
        }

        .nav-links a:hover {
            color: var(--cyan);
        }

        .nav-links a.active {
            color: var(--cyan);
        }

        .admin-badge {
            background: var(--cyan-dim);
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.7rem;
            color: var(--cyan);
            margin-left: 0.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--anthracite3);
            padding: 0.5rem 1.2rem;
            border-radius: 4px;
            border-left: 2px solid var(--cyan);
        }

        .user-info i {
            color: var(--cyan);
            font-size: 1.1rem;
        }

        .user-info span {
            color: var(--grey-light);
            font-weight: 500;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--grey);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .logout-btn:hover {
            color: var(--cyan);
        }

        /* Main Content */
        .main {
            padding: 30px 0 50px;
        }

        /* Page Title */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(2rem, 4vw, 2.8rem);
            letter-spacing: -.01em;
            text-transform: uppercase;
            color: var(--white);
            margin-bottom: 0.3rem;
        }

        .page-subtitle {
            color: var(--grey-light);
            border-left: 3px solid var(--cyan);
            padding-left: 1rem;
            font-weight: 300;
        }

        /* Tables */
        .table-section {
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
            margin-bottom: 1.5rem;
            position: relative;
            overflow-x: auto;
        }

        .table-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--cyan);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--anthracite3);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1rem;
            color: var(--white);
            letter-spacing: 0.05em;
        }

        .table-title i {
            color: var(--cyan);
            margin-right: 0.5rem;
        }

        .btn-add {
            background: var(--cyan);
            color: var(--anthracite);
            border: none;
            padding: 0.5rem 1rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn-add:hover {
            opacity: 0.85;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.2rem;
            text-align: left;
            border-bottom: 1px solid var(--anthracite3);
        }

        th {
            color: var(--cyan);
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        td {
            color: var(--grey-light);
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(16,185,129,0.15);
            color: var(--success);
            border: 1px solid rgba(16,185,129,0.3);
        }

        .status-inactive {
            background: rgba(239,68,68,0.15);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,0.3);
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--grey);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .action-btn:hover {
            color: var(--cyan);
        }

        .action-btn.delete:hover {
            color: var(--danger);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 300;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--anthracite2);
            border: 1px solid var(--cyan-border);
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--cyan);
        }

        .modal-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--anthracite3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.2rem;
            color: var(--white);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--grey);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--grey);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.7rem 1rem;
            background: var(--anthracite);
            border: 1px solid var(--anthracite3);
            font-family: 'Barlow', sans-serif;
            font-size: 0.9rem;
            color: var(--white);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--cyan);
        }

        .modal-footer {
            padding: 1rem 1.5rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn-primary {
            background: var(--cyan);
            color: var(--anthracite);
            border: none;
            padding: 0.6rem 1.5rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            cursor: pointer;
        }

        .btn-secondary {
            background: var(--anthracite3);
            color: var(--grey-light);
            border: none;
            padding: 0.6rem 1.5rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 0.8rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }

            .menu-toggle {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: 0;
                left: -100%;
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

            .menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 140;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }

            .menu-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .nav-container {
                justify-content: space-between;
                padding: 0.8rem 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            th, td {
                padding: 0.8rem;
            }

            .action-btns {
                flex-direction: column;
                gap: 0.3rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 12px;
            }

            .stat-value {
                font-size: 1.8rem;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
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
                    <a href="ad_utilisateur.php">Utilisateurs</a>
                    <a href="ad_livreur.php">Livreurs</a>
                    <a href="ad_livraison.php" class="active">Livraisons</a>
                    <a href="ad_rapport.php">Rapports</a>
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
                <h1 class="page-title">Gestion des Livraisons</h1>
                <p class="page-subtitle">Suivez et gérez toutes les livraisons</p>
            </div>

            <!-- Content Area - Empty -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-box"></i> Liste des livraisons</div>
                    <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Ajouter</button>
                </div>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Client</th><th>Livreur</th><th>Montant</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
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
                <h3 class="modal-title">Ajouter une livraison</h3>
                <button class="modal-close" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Client</label>
                    <input type="text" id="newItemClient" placeholder="Nom du client">
                </div>
                <div class="form-group">
                    <label>Montant</label>
                    <input type="text" id="newItemAmount" placeholder="Montant en FCFA">
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
