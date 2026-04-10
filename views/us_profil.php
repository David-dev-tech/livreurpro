<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Mon Profil</title>
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

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--anthracite);
            color: var(--white);
            -webkit-tap-highlight-color: transparent;
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

        /* ── Profile Layout ── */
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        /* ── Sidebar ── */
        .profile-sidebar {
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
            position: relative;
            overflow: hidden;
        }

        .profile-sidebar::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 3px; height: 100%;
            background: var(--cyan);
        }

        .profile-avatar {
            text-align: center;
            padding: 2rem;
            border-bottom: 1px solid var(--anthracite3);
        }

        .avatar-icon { font-size: 5rem; color: var(--cyan); }

        .profile-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 1rem;
            color: var(--white);
        }

        .profile-email { color: var(--grey-light); font-size: 0.85rem; margin-top: 0.3rem; }

        .profile-stats { padding: 1.5rem; }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--anthracite3);
        }

        .stat-item:last-child { border-bottom: none; }
        .stat-label { color: var(--grey); font-size: 0.85rem; }
        .stat-value { color: var(--cyan); font-weight: 700; font-size: 1.1rem; }

        /* ── Profile Main ── */
        .profile-main {
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
            position: relative;
            overflow: hidden;
        }

        .profile-main::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 3px; height: 100%;
            background: var(--cyan);
        }

        .profile-tabs {
            display: flex;
            border-bottom: 1px solid var(--anthracite3);
            background: var(--anthracite3);
        }

        .tab-btn {
            flex: 1;
            background: none;
            border: none;
            padding: 1rem;
            color: var(--grey-light);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 0.9rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .tab-btn:hover { color: var(--cyan); }

        .tab-btn.active {
            color: var(--cyan);
            background: var(--anthracite2);
            border-bottom: 2px solid var(--cyan);
        }

        .tab-content { padding: 2rem; display: none; }
        .tab-content.active { display: block; }

        /* ── Form ── */
        .info-group { margin-bottom: 1.5rem; }

        .info-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--grey);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .info-group input,
        .info-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            background: var(--anthracite);
            border: 1px solid var(--anthracite3);
            font-family: 'Barlow', sans-serif;
            font-size: 0.9rem;
            color: var(--white);
            transition: all 0.3s;
        }

        .info-group input:focus,
        .info-group textarea:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 2px var(--cyan-dim);
        }

        .row-2cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-save {
            background: var(--cyan);
            color: var(--anthracite);
            border: none;
            padding: 0.8rem 2rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn-save:hover { opacity: 0.85; }

        /* ── Historique ── */
        .history-list { display: flex; flex-direction: column; gap: 1rem; }

        .history-item {
            background: var(--anthracite);
            padding: 1rem;
            border-left: 3px solid var(--cyan);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .history-info h4 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }

        .history-info p { color: var(--grey-light); font-size: 0.8rem; }

        .history-status {
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .1em;
        }

        .status-livre {
            background: rgba(0,212,232,0.15);
            color: var(--cyan);
            border: 1px solid var(--cyan-border);
        }

        .status-encours {
            background: rgba(255,193,7,0.15);
            color: #ffc107;
            border: 1px solid rgba(255,193,7,0.3);
        }

        /* ── Paiement ── */
        .payment-methods { display: flex; flex-direction: column; gap: 1rem; }

        .payment-card {
            background: var(--anthracite);
            padding: 1rem;
            border: 1px solid var(--anthracite3);
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .payment-icon {
            width: 50px; height: 50px;
            background: var(--anthracite3);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .payment-icon i { font-size: 1.8rem; color: var(--cyan); }
        .payment-details { flex: 1; }
        .payment-details h4 { font-size: 1rem; margin-bottom: 0.2rem; }
        .payment-details p { color: var(--grey-light); font-size: 0.8rem; }

        .payment-default {
            background: var(--cyan-dim);
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            color: var(--cyan);
            border-radius: 4px;
        }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 3rem; background: var(--anthracite); }
        .empty-state i { font-size: 3rem; color: var(--grey); margin-bottom: 1rem; }
        .empty-state p { color: var(--grey-light); }

        /* ══════════════════════════════
           RESPONSIVE
        ══════════════════════════════ */

        @media (max-width: 1024px) and (min-width: 769px) {
            .container { padding: 0 20px; }
            .profile-container { grid-template-columns: 280px 1fr; gap: 1.5rem; }
        }

        @media (max-width: 768px) {
            .container { padding: 0 16px; }

            /* Afficher le hamburger */
            .menu-toggle { display: flex; }

            /* Menu latéral — fermé par défaut */
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
                /* Désactiver les interactions quand le panneau est hors écran */
                pointer-events: none;
            }

            /* Menu ouvert */
            .nav-links.active {
                left: 0;
                pointer-events: all;
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

            .nav-container { justify-content: space-between; padding: 0.8rem 0; }

            .profile-container { grid-template-columns: 1fr; gap: 1rem; }
            .row-2cols { grid-template-columns: 1fr; gap: 1rem; }
            .tab-content { padding: 1.5rem; }
            .profile-avatar { padding: 1.5rem; }
            .avatar-icon { font-size: 4rem; }
            .profile-name { font-size: 1.3rem; }
            .history-item { flex-direction: column; align-items: flex-start; }
        }

        @media (max-width: 480px) {
            .container { padding: 0 12px; }
            .tab-btn { font-size: 0.7rem; padding: 0.8rem; }
            .tab-content { padding: 1rem; }
            .info-group input,
            .info-group textarea { padding: 0.6rem 0.8rem; font-size: 0.85rem; }
            .btn-save { width: 100%; padding: 0.7rem; }
        }

        @media (max-width: 768px) and (orientation: landscape) {
            .nav-links { width: 50%; padding: 60px 1.5rem 1.5rem; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">

                <!-- Hamburger — SEUL bouton qui ouvre/ferme le menu -->
                <div class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <!-- Logo -->
                <a href="../index.html" style="text-decoration: none;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
                </a>

                <!-- Liens — aucun JS de fermeture, navigation libre -->
                <div class="nav-links" id="navLinks">
                    <a href="../index.html">Accueil</a>
                    <a href="catalogue.php">Catalogue</a>
                    <a href="us_profil.php" class="active">Profil</a>
                    <a href="../contact.php">Contact</a>
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span id="userPhone">+237 6XX XX XX XX</span>
                        <button class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i></button>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- PAS D'OVERLAY — le menu se ferme uniquement via le hamburger -->

    <main class="main">
        <div class="container">
            <div class="profile-container">

                <!-- Sidebar -->
                <aside class="profile-sidebar">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle avatar-icon"></i>
                        <h2 class="profile-name" id="profileName">Jean Kamga</h2>
                        <p class="profile-email" id="profileEmail">jean.kamga@email.com</p>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-truck"></i> Livraisons</span>
                            <span class="stat-value" id="statLivraisons">24</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-star"></i> Note moyenne</span>
                            <span class="stat-value" id="statNote">4.8</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-calendar"></i> Membre depuis</span>
                            <span class="stat-value" id="statMembre">Jan 2024</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-check-circle"></i> Taux succès</span>
                            <span class="stat-value" id="statTaux">98%</span>
                        </div>
                    </div>
                </aside>

                <!-- Contenu principal -->
                <div class="profile-main">
                    <div class="profile-tabs">
                        <button class="tab-btn active" data-tab="info">Informations</button>
                        <button class="tab-btn" data-tab="history">Historique</button>
                        <button class="tab-btn" data-tab="payment">Paiement</button>
                    </div>

                    <!-- Onglet Informations -->
                    <div class="tab-content active" id="tab-info">
                        <div class="row-2cols">
                            <div class="info-group">
                                <label><i class="fas fa-user"></i> Nom</label>
                                <input type="text" id="nom" value="Kamga" placeholder="Votre nom">
                            </div>
                            <div class="info-group">
                                <label><i class="fas fa-user"></i> Prénom</label>
                                <input type="text" id="prenom" value="Jean" placeholder="Votre prénom">
                            </div>
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" value="jean.kamga@email.com" placeholder="votre@email.com">
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-phone"></i> Téléphone</label>
                            <input type="tel" id="telephone" value="697123456" placeholder="+237 6XX XX XX XX">
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-map-marker-alt"></i> Adresse</label>
                            <textarea id="adresse" rows="3" placeholder="Votre adresse complète">Douala, Cameroun</textarea>
                        </div>
                        <button class="btn-save" id="saveBtn">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>

                    <!-- Onglet Historique -->
                    <div class="tab-content" id="tab-history">
                        <div class="history-list" id="historyList"></div>
                    </div>

                    <!-- Onglet Paiement -->
                    <div class="tab-content" id="tab-payment">
                        <div class="payment-methods" id="paymentMethods"></div>
                        <button class="btn-save" id="addPaymentBtn" style="margin-top: 1.5rem;">
                            <i class="fas fa-plus"></i> Ajouter une méthode de paiement
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        /* ══════════════════════════════
           MENU HAMBURGER
           Règle unique : SEUL le clic sur le bouton hamburger
           ouvre ou ferme le panneau. Rien d'autre.
        ══════════════════════════════ */
        var menuToggle = document.getElementById('menuToggle');
        var navLinks   = document.getElementById('navLinks');

        menuToggle.addEventListener('click', function () {
            menuToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        /* Les liens <a> n'ont AUCUN listener de fermeture.
           Le navigateur les suit normalement. */

        /* ══════════════════════════════
           ONGLETS
        ══════════════════════════════ */
        document.querySelectorAll('.tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
                document.querySelectorAll('.tab-content').forEach(function (c) { c.classList.remove('active'); });
                btn.classList.add('active');
                document.getElementById('tab-' + btn.getAttribute('data-tab')).classList.add('active');
            });
        });

        /* ══════════════════════════════
           DONNÉES
        ══════════════════════════════ */
        var userData = {
            nom: 'Kamga', prenom: 'Jean',
            email: 'jean.kamga@email.com',
            telephone: '697123456',
            adresse: 'Douala, Cameroun',
            livraisons: 24, note: 4.8,
            membre: 'Jan 2024', taux: 98
        };

        var historyData = [
            { date: '15/03/2024', montant: '5 000 FCFA', statut: 'livre',   adresse: 'Douala, Bonapriso' },
            { date: '10/03/2024', montant: '12 500 FCFA', statut: 'livre',  adresse: 'Douala, Akwa' },
            { date: '05/03/2024', montant: '3 200 FCFA', statut: 'livre',   adresse: 'Douala, Makepe' },
            { date: '28/02/2024', montant: '8 000 FCFA', statut: 'encours', adresse: 'Douala, Bonaberi' },
            { date: '20/02/2024', montant: '15 000 FCFA', statut: 'livre',  adresse: 'Douala, Village' }
        ];

        var paymentMethods = [
            { type: 'Orange Money',      numero: '697 12 34 56', defaut: true  },
            { type: 'MTN Mobile Money',  numero: '678 90 12 34', defaut: false }
        ];

        /* ── Charger profil ── */
        function loadUserData() {
            document.getElementById('profileName').textContent     = userData.prenom + ' ' + userData.nom;
            document.getElementById('profileEmail').textContent    = userData.email;
            document.getElementById('statLivraisons').textContent  = userData.livraisons;
            document.getElementById('statNote').textContent        = userData.note;
            document.getElementById('statMembre').textContent      = userData.membre;
            document.getElementById('statTaux').textContent        = userData.taux + '%';
            document.getElementById('nom').value        = userData.nom;
            document.getElementById('prenom').value     = userData.prenom;
            document.getElementById('email').value      = userData.email;
            document.getElementById('telephone').value  = userData.telephone;
            document.getElementById('adresse').value    = userData.adresse;
            document.getElementById('userPhone').textContent = userData.telephone;
        }

        /* ── Sauvegarder ── */
        document.getElementById('saveBtn').addEventListener('click', function () {
            userData.nom       = document.getElementById('nom').value;
            userData.prenom    = document.getElementById('prenom').value;
            userData.email     = document.getElementById('email').value;
            userData.telephone = document.getElementById('telephone').value;
            userData.adresse   = document.getElementById('adresse').value;
            document.getElementById('profileName').textContent  = userData.prenom + ' ' + userData.nom;
            document.getElementById('profileEmail').textContent = userData.email;
            alert('Profil mis à jour avec succès !');
        });

        /* ── Historique ── */
        function loadHistory() {
            var list = document.getElementById('historyList');
            if (historyData.length === 0) {
                list.innerHTML = '<div class="empty-state"><i class="fas fa-history"></i><p>Aucune livraison pour le moment</p></div>';
                return;
            }
            list.innerHTML = historyData.map(function (item) {
                var isLivre = item.statut === 'livre';
                return '<div class="history-item">' +
                    '<div class="history-info">' +
                        '<h4>Livraison du ' + item.date + '</h4>' +
                        '<p><i class="fas fa-map-marker-alt"></i> ' + item.adresse + '</p>' +
                        '<p><i class="fas fa-money-bill-wave"></i> ' + item.montant + '</p>' +
                    '</div>' +
                    '<div class="history-status ' + (isLivre ? 'status-livre' : 'status-encours') + '">' +
                        (isLivre ? 'Livré' : 'En cours') +
                    '</div>' +
                '</div>';
            }).join('');
        }

        /* ── Paiement ── */
        function loadPaymentMethods() {
            var container = document.getElementById('paymentMethods');
            if (paymentMethods.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-credit-card"></i><p>Aucune méthode de paiement enregistrée</p></div>';
                return;
            }
            container.innerHTML = paymentMethods.map(function (m) {
                return '<div class="payment-card">' +
                    '<div class="payment-icon"><i class="fas fa-mobile-alt"></i></div>' +
                    '<div class="payment-details"><h4>' + m.type + '</h4><p>' + m.numero + '</p></div>' +
                    (m.defaut ? '<span class="payment-default">Par défaut</span>' : '') +
                '</div>';
            }).join('');
        }

        document.getElementById('addPaymentBtn').addEventListener('click', function () {
            var type   = prompt('Type (Orange Money, MTN Mobile Money…) :');
            var numero = prompt('Numéro :');
            if (type && numero) {
                paymentMethods.push({ type: type, numero: numero, defaut: false });
                loadPaymentMethods();
                alert('Méthode de paiement ajoutée !');
            }
        });

        /* ── Déconnexion ── */
        document.getElementById('logoutBtn').addEventListener('click', function () {
            if (confirm('Voulez-vous vous déconnecter ?')) {
                window.location.href = '../index.html';
            }
        });

        /* ── Init ── */
        document.addEventListener('DOMContentLoaded', function () {
            loadUserData();
            loadHistory();
            loadPaymentMethods();
        });
    </script>
</body>
</html>