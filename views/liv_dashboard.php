<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Espace Livreur</title>
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
            --success:     #10B981;
            --warning:     #F59E0B;
            --danger:      #EF4444;
            --info:        #3B82F6;
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

        /* Header simplifié */
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
            padding: 0.8rem 0;
            gap: 1rem;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 42px;
            background: var(--cyan);
            border: 2px solid var(--cyan);
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-style: italic;
            font-size: 1.3rem;
            color: var(--anthracite);
            letter-spacing: -.02em;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--anthracite3);
            padding: 0.4rem 1rem;
            border-radius: 4px;
            border-left: 2px solid var(--cyan);
        }

        .user-info i {
            color: var(--cyan);
            font-size: 1rem;
        }

        .user-info span {
            color: var(--grey-light);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--grey);
            cursor: pointer;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .logout-btn:hover {
            color: var(--cyan);
        }

        /* Main Content */
        .main {
            padding: 20px 0 40px;
        }

        /* En-tête avec stats rapides */
        .welcome-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(1.5rem, 4vw, 2rem);
            letter-spacing: -.01em;
            text-transform: uppercase;
            color: var(--white);
        }

        .date-info {
            color: var(--grey-light);
            font-size: 0.85rem;
            margin-top: 0.2rem;
        }

        /* Stats rapides (mini cartes) */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .quick-card {
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .quick-card:hover {
            border-color: var(--cyan-border);
            transform: translateY(-2px);
        }

        .quick-info h4 {
            font-size: 0.7rem;
            color: var(--grey);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .quick-info .value {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--white);
            line-height: 1;
        }

        .quick-icon {
            font-size: 1.8rem;
            color: var(--cyan);
            opacity: 0.5;
        }

        /* Section principale - Livraisons du jour */
        .section-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--cyan);
        }

        .deliveries-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Carte de livraison */
        .delivery-card {
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
            border-left: 3px solid var(--cyan);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .delivery-card:hover {
            border-color: var(--cyan-border);
        }

        .delivery-card.completed {
            opacity: 0.7;
            border-left-color: var(--success);
        }

        .delivery-card .card-header {
            padding: 1rem 1.2rem;
            background: var(--anthracite3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .delivery-id {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            color: var(--cyan);
        }

        .delivery-status {
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(245,158,11,0.15);
            color: var(--warning);
            border: 1px solid rgba(245,158,11,0.3);
        }

        .status-progress {
            background: rgba(59,130,246,0.15);
            color: var(--info);
            border: 1px solid rgba(59,130,246,0.3);
        }

        .status-completed {
            background: rgba(16,185,129,0.15);
            color: var(--success);
            border: 1px solid rgba(16,185,129,0.3);
        }

        .card-body {
            padding: 1rem 1.2rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
        }

        .delivery-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
        }

        .info-row i {
            width: 20px;
            color: var(--cyan);
            font-size: 0.85rem;
        }

        .info-row .label {
            color: var(--grey);
            font-weight: 500;
        }

        .info-row .value {
            color: var(--white);
        }

        .delivery-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--cyan);
            color: var(--anthracite);
        }

        .btn-primary:hover {
            opacity: 0.85;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--cyan-border);
            color: var(--cyan);
        }

        .btn-outline:hover {
            background: var(--cyan-dim);
        }

        .btn-success {
            background: var(--success);
            color: var(--anthracite);
        }

        .btn-success:hover {
            opacity: 0.85;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
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
            max-width: 450px;
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
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--anthracite3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.1rem;
            color: var(--white);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--grey);
            font-size: 1.3rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 1.2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--grey);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.6rem 0.8rem;
            background: var(--anthracite);
            border: 1px solid var(--anthracite3);
            font-family: 'Barlow', sans-serif;
            font-size: 0.9rem;
            color: var(--white);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--cyan);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }

        .modal-footer {
            padding: 1rem 1.2rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.8rem;
        }

        .btn-secondary {
            background: var(--anthracite3);
            color: var(--grey-light);
            border: none;
            padding: 0.5rem 1rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 0.8rem;
            cursor: pointer;
        }

        /* Notification */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--anthracite2);
            border-left: 3px solid var(--cyan);
            padding: 0.8rem 1.2rem;
            border-radius: 4px;
            display: none;
            z-index: 400;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification.show {
            display: block;
        }

        .notification.success {
            border-left-color: var(--success);
        }

        .notification.error {
            border-left-color: var(--danger);
        }

        /* Footer */
        .footer {
            background: var(--anthracite2);
            border-top: 1px solid var(--anthracite3);
            padding: 1rem 0;
            text-align: center;
            font-size: 0.7rem;
            color: var(--grey);
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .main {
            padding-bottom: 70px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }

            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
            }

            .card-body {
                grid-template-columns: 1fr;
            }

            .delivery-actions {
                flex-direction: row;
                justify-content: flex-start;
            }

            .quick-info .value {
                font-size: 1.3rem;
            }

            .quick-icon {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .quick-stats {
                gap: 0.5rem;
            }

            .quick-card {
                padding: 0.6rem;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="logo">L.Pro</div>
                <div class="user-info">
                    <i class="fas fa-motorcycle"></i>
                    <span>Jean Dupont</span>
                    <button class="logout-btn" onclick="showNotification('Déconnexion...', 'success')"><i class="fas fa-sign-out-alt"></i></button>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <!-- En-tête -->
            <div class="welcome-header">
                <h1 class="page-title">Mes livraisons</h1>
                <div class="date-info" id="currentDate"></div>
            </div>

            <!-- Stats rapides -->
            <div class="quick-stats">
                <div class="quick-card">
                    <div class="quick-info">
                        <h4>À livrer</h4>
                        <div class="value" id="pendingCount">0</div>
                    </div>
                    <i class="fas fa-clock quick-icon"></i>
                </div>
                <div class="quick-card">
                    <div class="quick-info">
                        <h4>En cours</h4>
                        <div class="value" id="progressCount">0</div>
                    </div>
                    <i class="fas fa-spinner fa-pulse quick-icon"></i>
                </div>
                <div class="quick-card">
                    <div class="quick-info">
                        <h4>Livrées</h4>
                        <div class="value" id="completedCount">0</div>
                    </div>
                    <i class="fas fa-check-circle quick-icon"></i>
                </div>
                <div class="quick-card">
                    <div class="quick-info">
                        <h4>Note</h4>
                        <div class="value" id="ratingValue">4.8</div>
                    </div>
                    <i class="fas fa-star quick-icon"></i>
                </div>
            </div>

            <!-- Liste des livraisons -->
            <div class="section-title">
                <i class="fas fa-list"></i>
                <span>Livraisons du jour</span>
            </div>

            <div class="deliveries-grid" id="deliveriesList">
                <!-- Les livraisons seront chargées ici -->
            </div>
        </div>
    </main>

    <div class="footer">
        <i class="fas fa-motorcycle"></i> LivreurPro - Votre partenaire livraison
    </div>

    <!-- Modal de confirmation -->
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-check-circle"></i> Confirmer livraison</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 1rem;">Confirmez la livraison de la commande <strong id="confirmDeliveryId"></strong></p>
                <div class="form-group">
                    <label>Code de confirmation</label>
                    <input type="text" id="confirmCode" placeholder="Code reçu par SMS" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Photo de livraison (optionnel)</label>
                    <input type="file" id="deliveryPhoto" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Commentaire</label>
                    <textarea id="deliveryComment" placeholder="Signature, problème, etc..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Annuler</button>
                <button class="btn-primary" onclick="confirmDelivery()">Confirmer</button>
            </div>
        </div>
    </div>

    <!-- Modal appel client -->
    <div class="modal" id="callModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-phone"></i> Contacter client</h3>
                <button class="modal-close" onclick="closeCallModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="text-align: center; margin-bottom: 1rem;">
                    <i class="fas fa-user" style="color: var(--cyan); font-size: 2rem;"></i>
                </p>
                <p style="text-align: center; margin-bottom: 1rem;"><strong id="callClientName"></strong></p>
                <p style="text-align: center; margin-bottom: 1rem;" id="callClientPhone"></p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="btn-primary" onclick="makeCall()"><i class="fas fa-phone"></i> Appeler</button>
                    <button class="btn-secondary" onclick="sendSMS()"><i class="fas fa-envelope"></i> SMS</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification">
        <i class="fas fa-info-circle"></i> <span id="notificationMessage"></span>
    </div>

    <script>
        // Données des livraisons
        let deliveries = [
            {
                id: 'CMD-001',
                client: 'Marie Lambert',
                address: '12 rue de Paris, 75001 Paris',
                phone: '06 12 34 56 78',
                amount: '45.90 €',
                status: 'pending', // pending, progress, completed
                time: '10:30 - 11:30'
            },
            {
                id: 'CMD-002',
                client: 'Thomas Bernard',
                address: '8 avenue Victor Hugo, 69002 Lyon',
                phone: '06 23 45 67 89',
                amount: '32.50 €',
                status: 'pending',
                time: '11:45 - 12:45'
            },
            {
                id: 'CMD-003',
                client: 'Sophie Martin',
                address: '25 boulevard Stalingrad, 44000 Nantes',
                phone: '06 34 56 78 90',
                amount: '78.30 €',
                status: 'progress',
                time: '14:00 - 15:00'
            },
            {
                id: 'CMD-004',
                client: 'Nicolas Petit',
                address: '15 rue des Lilas, 13008 Marseille',
                phone: '06 45 67 89 01',
                amount: '52.00 €',
                status: 'completed',
                time: '09:00 - 10:00'
            }
        ];

        let currentDeliveryId = null;

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            displayCurrentDate();
            renderDeliveries();
            updateStats();
        });

        // Afficher la date
        function displayCurrentDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const date = new Date().toLocaleDateString('fr-FR', options);
            document.getElementById('currentDate').innerHTML = `<i class="fas fa-calendar-alt"></i> ${date}`;
        }

        // Rendre la liste des livraisons
        function renderDeliveries() {
            const container = document.getElementById('deliveriesList');
            
            if (deliveries.filter(d => d.status !== 'completed').length === 0 && deliveries.filter(d => d.status === 'completed').length > 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 3rem; background: var(--anthracite2); border: 1px solid var(--anthracite3);">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;"></i>
                        <p>Toutes vos livraisons du jour sont terminées !</p>
                        <p style="color: var(--grey-light); font-size: 0.85rem;">Bonne journée, reposez-vous bien.</p>
                    </div>
                `;
                return;
            }
            
            if (deliveries.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 3rem; background: var(--anthracite2); border: 1px solid var(--anthracite3);">
                        <i class="fas fa-truck" style="font-size: 3rem; color: var(--grey); margin-bottom: 1rem;"></i>
                        <p>Aucune livraison programmée pour aujourd'hui</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = deliveries.map(delivery => {
                let statusText = '';
                let statusClass = '';
                let actions = '';
                
                if (delivery.status === 'pending') {
                    statusText = 'À livrer';
                    statusClass = 'status-pending';
                    actions = `
                        <button class="btn-primary" onclick="startDelivery('${delivery.id}')">
                            <i class="fas fa-play"></i> Démarrer
                        </button>
                        <button class="btn-outline" onclick="openCallModal('${delivery.client}', '${delivery.phone}')">
                            <i class="fas fa-phone"></i>
                        </button>
                    `;
                } else if (delivery.status === 'progress') {
                    statusText = 'En cours';
                    statusClass = 'status-progress';
                    actions = `
                        <button class="btn-success" onclick="openConfirmModal('${delivery.id}')">
                            <i class="fas fa-check"></i> Livré
                        </button>
                        <button class="btn-outline" onclick="openCallModal('${delivery.client}', '${delivery.phone}')">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="btn-outline" onclick="showRoute('${delivery.address}')">
                            <i class="fas fa-directions"></i>
                        </button>
                    `;
                } else {
                    statusText = 'Livrée';
                    statusClass = 'status-completed';
                    actions = `<span style="color: var(--success);"><i class="fas fa-check"></i> Terminée</span>`;
                }
                
                return `
                    <div class="delivery-card ${delivery.status === 'completed' ? 'completed' : ''}">
                        <div class="card-header">
                            <span class="delivery-id"><i class="fas fa-hashtag"></i> ${delivery.id}</span>
                            <span class="delivery-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="card-body">
                            <div class="delivery-info">
                                <div class="info-row">
                                    <i class="fas fa-user"></i>
                                    <span class="label">Client :</span>
                                    <span class="value">${delivery.client}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="label">Adresse :</span>
                                    <span class="value">${delivery.address}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-clock"></i>
                                    <span class="label">Créneau :</span>
                                    <span class="value">${delivery.time}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-euro-sign"></i>
                                    <span class="label">Montant :</span>
                                    <span class="value">${delivery.amount}</span>
                                </div>
                            </div>
                            <div class="delivery-actions">
                                ${actions}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Statistiques
        function updateStats() {
            const pending = deliveries.filter(d => d.status === 'pending').length;
            const progress = deliveries.filter(d => d.status === 'progress').length;
            const completed = deliveries.filter(d => d.status === 'completed').length;
            
            document.getElementById('pendingCount').innerText = pending;
            document.getElementById('progressCount').innerText = progress;
            document.getElementById('completedCount').innerText = completed;
        }

        // Démarrer une livraison
        function startDelivery(id) {
            const index = deliveries.findIndex(d => d.id === id);
            if (index !== -1 && deliveries[index].status === 'pending') {
                deliveries[index].status = 'progress';
                renderDeliveries();
                updateStats();
                showNotification(`Livraison ${id} démarrée. Bonne route !`, 'success');
            }
        }

        // Ouvrir modal de confirmation
        function openConfirmModal(id) {
            currentDeliveryId = id;
            document.getElementById('confirmDeliveryId').innerText = id;
            document.getElementById('confirmCode').value = '';
            document.getElementById('deliveryComment').value = '';
            document.getElementById('confirmModal').classList.add('active');
        }

        // Confirmer livraison
        function confirmDelivery() {
            const code = document.getElementById('confirmCode').value;
            
            if (!code) {
                showNotification('Veuillez entrer le code de confirmation', 'error');
                return;
            }
            
            // Simuler vérification (code = 1234 pour la démo)
            if (code === '1234') {
                const index = deliveries.findIndex(d => d.id === currentDeliveryId);
                if (index !== -1 && deliveries[index].status === 'progress') {
                    deliveries[index].status = 'completed';
                    renderDeliveries();
                    updateStats();
                    closeModal();
                    showNotification(`Livraison ${currentDeliveryId} confirmée avec succès !`, 'success');
                }
            } else {
                showNotification('Code invalide. Vérifiez auprès du client.', 'error');
            }
        }

        // Modal appel
        function openCallModal(clientName, phone) {
            document.getElementById('callClientName').innerText = clientName;
            document.getElementById('callClientPhone').innerHTML = `<i class="fas fa-phone-alt"></i> ${phone}`;
            document.getElementById('callModal').classList.add('active');
        }

        function closeCallModal() {
            document.getElementById('callModal').classList.remove('active');
        }

        function makeCall() {
            showNotification('Appel en cours...', 'info');
            closeCallModal();
        }

        function sendSMS() {
            showNotification('SMS envoyé au client', 'success');
            closeCallModal();
        }

        // Itinéraire
        function showRoute(address) {
            showNotification(`Ouverture de l'itinéraire vers : ${address}`, 'info');
            // Dans une vraie app, ouvrir Google Maps ou Waze
        }

        // Fermer modals
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        // Notification
        function showNotification(message, type = 'info') {
            const notification = document.getElementById('notification');
            const messageSpan = document.getElementById('notificationMessage');
            messageSpan.innerHTML = message;
            
            notification.className = `notification show ${type}`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Fermer modals en cliquant en dehors
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>