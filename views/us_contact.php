<?php
// ============================================================
// contact.php — Liste des livreurs contactés par l'utilisateur
// avec liens WhatsApp directs
// ============================================================
session_start();
require_once '../config/config.php';

// Redirection si non connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.html');
    exit;
}

$id_user = $_SESSION['id_user'];

// Récupération de tous les livreurs avec lesquels l'utilisateur a interagi
// On ne récupère que les colonnes qui existent SÛREMENT
$stmt = $pdo->prepare("
    SELECT DISTINCT
        liv.id_livreur,
        liv.nom,
        liv.prenom,
        liv.numero,
        liv.type_vehicule,
        COUNT(l.id_livraison) as nb_livraisons,
        MAX(l.date_creation) as derniere_livraison
    FROM livreur liv
    INNER JOIN livraison l ON liv.id_livreur = l.id_livreur
    WHERE l.id_user = ?
    GROUP BY liv.id_livreur, liv.nom, liv.prenom, liv.numero, liv.type_vehicule
    ORDER BY derniere_livraison DESC
");
$stmt->execute([$id_user]);
$livreurs_contactes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des stats globales
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT liv.id_livreur) as total_livreurs,
        COUNT(l.id_livraison) as total_livraisons
    FROM livreur liv
    INNER JOIN livraison l ON liv.id_livreur = l.id_livreur
    WHERE l.id_user = ?
");
$stmt_stats->execute([$id_user]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Initialiser les stats à 0 si aucun résultat
if (!$stats) {
    $stats = ['total_livreurs' => 0, 'total_livraisons' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <title>LivreurPro | Mes Contacts Livreurs</title>

  <link rel="stylesheet" href="../css/dark/us_css.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    :root {
      --cyan        : #00D4E8;
      --cyan-border : rgba(0, 212, 232, 0.30);
      --cyan-dim    : rgba(0, 212, 232, 0.08);
      --cyan-glow   : rgba(0, 212, 232, 0.18);
      --bg          : #13151A;
      --card        : #1C1F26;
      --card2       : #232830;
      --card3       : #2A303A;
      --white       : #EFF3F8;
      --grey        : #7A8694;
      --grey-light  : #B8C4D0;
      --whatsapp    : #25D366;
      --whatsapp-dim: rgba(37, 211, 102, 0.15);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background : var(--bg);
      color      : var(--white);
      font-family: 'Barlow', 'Segoe UI', sans-serif;
      min-height : 100vh;
    }

    /* ─── HERO BANNER ─── */
    .page-hero {
      background : linear-gradient(135deg, var(--card) 0%, #0e1117 100%);
      border-bottom: 1px solid var(--cyan-border);
      padding    : 2.5rem 0 2rem;
      position   : relative;
      overflow   : hidden;
    }
    .page-hero::before {
      content  : '';
      position : absolute;
      top: -60px; right: -60px;
      width: 280px; height: 280px;
      background: radial-gradient(circle, var(--cyan-glow) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-inner {
      max-width : 1200px;
      margin    : 0 auto;
      padding   : 0 1.5rem;
      display   : flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .hero-title {
      font-family : 'Barlow Condensed', sans-serif;
      font-size   : 2rem;
      font-weight : 800;
      letter-spacing: .04em;
      color       : var(--white);
    }
    .hero-title i { color: var(--cyan); margin-right: .5rem; }
    .hero-subtitle {
      font-size : .9rem;
      color     : var(--grey-light);
      margin-top: .3rem;
    }
    .hero-stat-group {
      display: flex;
      gap: 1.2rem;
      flex-wrap: wrap;
    }
    .hero-stat {
      background : var(--card2);
      border     : 1px solid var(--cyan-border);
      padding    : .7rem 1.2rem;
      border-radius: 6px;
      text-align : center;
      min-width  : 90px;
    }
    .hero-stat-val {
      font-family : 'Barlow Condensed', sans-serif;
      font-size   : 1.6rem;
      font-weight : 800;
      color       : var(--cyan);
      line-height : 1;
    }
    .hero-stat-label {
      font-size  : .65rem;
      color      : var(--grey);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-top : .25rem;
    }

    /* ─── MAIN CONTAINER ─── */
    .main-wrap {
      max-width : 1200px;
      margin    : 0 auto;
      padding   : 2rem 1.5rem 4rem;
    }

    /* ─── BARRE DE RECHERCHE ─── */
    .search-bar {
      margin-bottom: 2rem;
      position: relative;
    }
    .search-input {
      width: 100%;
      padding: 0.9rem 1rem 0.9rem 3rem;
      background: var(--card2);
      border: 1px solid var(--card3);
      border-radius: 8px;
      color: var(--white);
      font-size: 0.9rem;
      transition: all 0.22s;
    }
    .search-input:focus {
      outline: none;
      border-color: var(--cyan);
      box-shadow: 0 0 0 3px var(--cyan-dim);
    }
    .search-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--grey);
    }

    /* ─── ÉTAT VIDE ─── */
    .empty-wrap {
      text-align : center;
      padding    : 5rem 2rem;
    }
    .empty-wrap i { font-size: 4rem; color: var(--card3); margin-bottom: 1.2rem; display: block; }
    .empty-wrap h3 { color: var(--grey-light); font-size: 1.2rem; margin-bottom: .5rem; }
    .empty-wrap p  { color: var(--grey); font-size: .88rem; }
    .btn-go {
      display    : inline-flex;
      align-items: center;
      gap        : .4rem;
      margin-top : 1.5rem;
      padding    : .7rem 1.8rem;
      background : var(--cyan);
      color      : #111;
      border-radius: 4px;
      font-weight : 700;
      text-decoration: none;
      font-size  : .85rem;
      transition : all .22s;
    }
    .btn-go:hover { background: #00b8cc; transform: translateY(-2px); }

    /* ─── GRILLE CARTES LIVREURS ─── */
    .livreurs-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.5rem;
    }

    /* ─── CARTE LIVREUR ─── */
    .livreur-card {
      background: var(--card);
      border: 1px solid var(--card3);
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.25s ease;
      position: relative;
    }
    .livreur-card:hover {
      transform: translateY(-4px);
      border-color: var(--cyan-border);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    }

    /* Avatar par défaut (pas de photo) */
    .card-avatar {
      position: relative;
      height: 140px;
      background: linear-gradient(135deg, var(--cyan-dim) 0%, var(--card3) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .default-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: var(--card2);
      border: 3px solid var(--cyan);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      color: var(--cyan);
    }
    .online-status {
      position: absolute;
      bottom: 10px;
      right: 20px;
      width: 16px;
      height: 16px;
      background: #10B981;
      border-radius: 50%;
      border: 2px solid var(--card);
    }

    /* Corps carte */
    .card-body {
      padding: 1.2rem;
    }
    .livreur-name {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 0.3rem;
    }
    .livreur-vehicle {
      font-size: 0.8rem;
      color: var(--cyan);
      margin-bottom: 0.8rem;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }
    .stats {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
      padding: 0.6rem 0;
      border-top: 1px solid var(--card3);
      border-bottom: 1px solid var(--card3);
    }
    .stat-item {
      flex: 1;
      text-align: center;
    }
    .stat-number {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--cyan);
    }
    .stat-label {
      font-size: 0.65rem;
      color: var(--grey);
      text-transform: uppercase;
    }
    .last-delivery {
      font-size: 0.75rem;
      color: var(--grey-light);
      margin-bottom: 1rem;
    }
    .contact-info {
      background: var(--card2);
      padding: 0.6rem;
      border-radius: 6px;
      margin-bottom: 1rem;
      font-size: 0.8rem;
    }
    .contact-info i {
      color: var(--cyan);
      width: 24px;
    }

    /* Boutons d'action */
    .action-buttons {
      display: flex;
      gap: 0.8rem;
    }
    .btn-whatsapp {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.7rem;
      background: var(--whatsapp-dim);
      border: 1px solid var(--whatsapp);
      color: var(--whatsapp);
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.22s;
    }
    .btn-whatsapp:hover {
      background: var(--whatsapp);
      color: white;
      transform: translateY(-2px);
    }
    .btn-call {
      padding: 0.7rem;
      background: var(--card3);
      border: 1px solid var(--card3);
      color: var(--grey-light);
      text-decoration: none;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.22s;
    }
    .btn-call:hover {
      border-color: var(--cyan);
      color: var(--cyan);
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .livreurs-grid {
        grid-template-columns: 1fr;
      }
      .hero-inner {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

  <!-- ══════════════ HEADER ══════════════ -->
  <header class="header">
    <div class="container">
      <div class="nav-container">
        <div class="menu-toggle" id="menuToggle">
          <span></span><span></span><span></span>
        </div>
        <a href="../index.html" style="text-decoration:none;">
          <div style="display:inline-flex;align-items:center;justify-content:center;
                      width:75px;height:48px;background:#00D4E8;border:2px solid #00D4E8;
                      clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
                      font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;
                      font-size:1.3rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
        </a>
        <div class="nav-links" id="navLinks">
          <a href="../index.html">Accueil</a>
          <a href="us_catalogue.php">Catalogue</a>
          <a href="us_profil.php">Profil</a>
          <a href="mes_livraisons.php">Mes Commandes</a>
          <a href="contact.php" class="active">Contact</a>
          <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['mail'] ?? 'Utilisateur'); ?></span>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ══════════════ HERO BANNER ══════════════ -->
  <section class="page-hero">
    <div class="hero-inner">
      <div>
        <h1 class="hero-title"><i class="fas fa-address-book"></i> Mes Contacts</h1>
        <p class="hero-subtitle">Retrouvez tous les livreurs avec qui vous avez collaboré</p>
      </div>
      <div class="hero-stat-group">
        <div class="hero-stat">
          <div class="hero-stat-val"><?= $stats['total_livreurs'] ?? 0 ?></div>
          <div class="hero-stat-label">Livreurs</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-val"><?= $stats['total_livraisons'] ?? 0 ?></div>
          <div class="hero-stat-label">Livraisons</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════ CONTENU PRINCIPAL ══════════════ -->
  <main class="main-wrap">

    <?php if (empty($livreurs_contactes)): ?>
      <!-- État vide -->
      <div class="empty-wrap">
        <i class="fas fa-user-friends"></i>
        <h3>Aucun contact pour le moment</h3>
        <p>Vous n'avez pas encore interagi avec des livreurs. Effectuez une livraison pour commencer.</p>
        <a href="us_catalogue.php" class="btn-go">
          <i class="fas fa-search"></i> Trouver un livreur
        </a>
      </div>

    <?php else: ?>

      <!-- Barre de recherche -->
      <div class="search-bar">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Rechercher un livreur par nom, prénom ou véhicule...">
      </div>

      <!-- Grille des livreurs -->
      <div class="livreurs-grid" id="livreursGrid">
        <?php foreach ($livreurs_contactes as $livreur):
          $nom_complet = htmlspecialchars($livreur['prenom'] . ' ' . $livreur['nom']);
          $numero = htmlspecialchars($livreur['numero']);
          // Nettoyer le numéro pour WhatsApp (garder seulement chiffres et +)
          $numero_propre = preg_replace('/[^0-9+]/', '', $numero);
          // Si le numéro commence par 0, on le convertit au format international (ex: +237)
          if (preg_match('/^0/', $numero_propre)) {
            $numero_propre = '+237' . substr($numero_propre, 1);
          }
          $whatsapp_link = "https://wa.me/" . ltrim($numero_propre, '+');
          
          $vehicleLabel = match($livreur['type_vehicule']) {
            'moto'        => '🏍️ Moto',
            'tricycle'    => '🛺 Tricycle',
            'camionnette' => '🚐 Camionnette',
            'camion'      => '🚛 Camion',
            default       => '🚗 Véhicule'
          };
          
          $derniere_date = !empty($livreur['derniere_livraison'])
            ? (new DateTime($livreur['derniere_livraison']))->format('d/m/Y')
            : 'Date inconnue';
        ?>
        <div class="livreur-card" data-searchable="<?= strtolower($nom_complet . ' ' . $vehicleLabel) ?>">
          <div class="card-avatar">
            <div class="default-avatar">
              <i class="fas fa-user-tie"></i>
            </div>
            <div class="online-status"></div>
          </div>
          
          <div class="card-body">
            <div class="livreur-name"><?= $nom_complet ?></div>
            <div class="livreur-vehicle">
              <i class="fas fa-motorcycle"></i> <?= $vehicleLabel ?>
            </div>
            
            <div class="stats">
              <div class="stat-item">
                <div class="stat-number"><?= $livreur['nb_livraisons'] ?></div>
                <div class="stat-label">Livraisons</div>
              </div>
              <div class="stat-item">
                <div class="stat-number"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-label">Dernière</div>
              </div>
            </div>
            
            <div class="last-delivery">
              <i class="fas fa-history"></i> Dernière livraison : <?= $derniere_date ?>
            </div>
            
            <div class="contact-info">
              <div><i class="fas fa-phone-alt"></i> <?= $numero ?></div>
            </div>
            
            <div class="action-buttons">
              <a href="<?= $whatsapp_link ?>" target="_blank" class="btn-whatsapp">
                <i class="fab fa-whatsapp"></i> WhatsApp
              </a>
              <a href="tel:<?= $numero_propre ?>" class="btn-call">
                <i class="fas fa-phone"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </main>

  <script>
  // Recherche de livreurs
  const searchInput = document.getElementById('searchInput');
  
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const cards = document.querySelectorAll('.livreur-card');
      
      cards.forEach(card => {
        const searchable = card.dataset.searchable || '';
        if (searchable.includes(searchTerm) || searchTerm === '') {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }

  // Menu mobile
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');
  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      menuToggle.classList.toggle('active');
      navLinks.classList.toggle('active');
    });
  }
  </script>

</body>
</html>