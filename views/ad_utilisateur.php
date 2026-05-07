<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Administration - Utilisateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dark/ad_css.css">
    <style>
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        .btn-edit {
            background: #ffc107;
            color: #1C1F24;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 8px;
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active {
            background: #28a745;
            color: white;
        }
        .status-inactive {
            background: #6c757d;
            color: white;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: #2a2f38;
            color: #e0e0e0;
        }
        .search-box input:focus {
            outline: none;
            border-color: #00D4E8;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #00D4E8;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    // Vérifier si l'admin est connecté (adaptez selon votre logique)
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
        // Pour le développement, on peut créer une session admin temporaire
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 'admin';
            $_SESSION['admin_logged_in'] = true;
        }
    }
    
    // Connexion à la base de données avec PDO
    require_once '../config/config.php';
    
    $message = '';
    $error = '';
    
    // Traitement de la suppression d'un utilisateur
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        
        try {
            $pdo->beginTransaction();
            
            // Supprimer l'utilisateur (les autres tables seront affectées par ON DELETE CASCADE)
            $sql = "DELETE FROM utilisateur WHERE id_user = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$delete_id]);
            
            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                $message = "Utilisateur supprimé avec succès !";
            } else {
                throw new Exception("Impossible de supprimer cet utilisateur.");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
    
    // Traitement de l'ajout d'un utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
        $user_id = trim($_POST['user_id']);
        $email = trim($_POST['email']);
        $nom_utilisateur = trim($_POST['nom_utilisateur']);
        
        if (empty($user_id) || empty($email)) {
            $error = "L'ID utilisateur et l'email sont obligatoires.";
        } else {
            try {
                // Vérifier si l'utilisateur existe déjà
                $check_sql = "SELECT id_user FROM utilisateur WHERE id_user = ? OR mail = ?";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$user_id, $email]);
                
                if ($check_stmt->rowCount() > 0) {
                    $error = "Cet ID utilisateur ou cet email existe déjà.";
                } else {
                    $insert_sql = "INSERT INTO utilisateur (id_user, mail, nom_utilisateur, date_creation) VALUES (?, ?, ?, NOW())";
                    $insert_stmt = $pdo->prepare($insert_sql);
                    $insert_stmt->execute([$user_id, $email, $nom_utilisateur]);
                    
                    $message = "Utilisateur ajouté avec succès !";
                }
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage();
            }
        }
    }
    
    // Récupérer la liste des utilisateurs
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    try {
        if (!empty($search)) {
            $sql = "SELECT id_user, mail, nom_utilisateur, date_creation FROM utilisateur 
                    WHERE id_user LIKE ? OR mail LIKE ? OR nom_utilisateur LIKE ?
                    ORDER BY date_creation DESC";
            $stmt = $pdo->prepare($sql);
            $search_param = "%$search%";
            $stmt->execute([$search_param, $search_param, $search_param]);
        } else {
            $sql = "SELECT id_user, mail, nom_utilisateur, date_creation FROM utilisateur ORDER BY date_creation DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
        $users = [];
    }
    ?>
    
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

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="table-section">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-users"></i> Liste des utilisateurs</div>
                    <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Ajouter</button>
                </div>
                
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Rechercher par ID, email ou nom..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn-add" onclick="searchUsers()" style="margin: 0;">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <?php if (!empty($search)): ?>
                        <button class="btn-secondary" onclick="resetSearch()" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Réinitialiser
                        </button>
                    <?php endif; ?>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Utilisateur</th>
                                <th>Nom d'utilisateur</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="mainTableBody">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id_user']); ?></td>
                                        <td><?php echo htmlspecialchars($user['nom_utilisateur'] ?? 'Non renseigné'); ?></td>
                                        <td><?php echo htmlspecialchars($user['mail']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($user['date_creation'])); ?></td>
                                        <td class="action-buttons">
                                            <button class="btn-delete" onclick="deleteUser('<?php echo htmlspecialchars($user['id_user']); ?>')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <p>Aucun utilisateur trouvé</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #2a2f38; border-radius: 8px; text-align: center;">
                    <small>Total : <?php echo count($users); ?> utilisateur(s)</small>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal d'ajout -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-user-plus"></i> Ajouter un utilisateur</h3>
                <button class="modal-close" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> ID Utilisateur *</label>
                        <input type="text" id="user_id" name="user_id" placeholder="Ex: user123" required>
                        <small style="color: #999; display: block; margin-top: 5px;">Identifiant unique pour l'utilisateur</small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email *</label>
                        <input type="email" id="email" name="email" placeholder="email@exemple.com" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nom d'utilisateur</label>
                        <input type="text" id="nom_utilisateur" name="nom_utilisateur" placeholder="Nom d'affichage (optionnel)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeAddModal()">Annuler</button>
                    <button type="submit" name="add_user" class="btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ? Cette action est irréversible.')) {
                window.location.href = 'ad_utilisateur.php?delete_id=' + encodeURIComponent(userId);
            }
        }
        
        function searchUsers() {
            var searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim()) {
                window.location.href = 'ad_utilisateur.php?search=' + encodeURIComponent(searchTerm);
            } else {
                window.location.href = 'ad_utilisateur.php';
            }
        }
        
        function resetSearch() {
            window.location.href = 'ad_utilisateur.php';
        }
        
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
            // Reset form
            document.getElementById('user_id').value = '';
            document.getElementById('email').value = '';
            document.getElementById('nom_utilisateur').value = '';
        }
        
        // Fermer modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target === modal) {
                closeAddModal();
            }
        }
        
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
        
        // Recherche avec la touche Entrée
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchUsers();
                }
            });
        }
    </script>
</body>
</html>