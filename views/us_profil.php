<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Mon Profil</title>
    <link rel="stylesheet" href="../css/dark/us_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
<div style="display:inline-flex;align-items:center;justify-content:center;width:75px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.3rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>                </a>

                <!-- Liens — aucun JS de fermeture, navigation libre -->
                <div class="nav-links" id="navLinks">
                    <a href="../index.html">Accueil</a>
                    <a href="us_catalogue.php">Catalogue</a>
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