<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.html');
    exit;
}

$id_user = $_SESSION['id_user'];
$mail    = $_SESSION['mail'] ?? '';

// Récupération du nom actuel depuis la BD
$stmt = $pdo->prepare("SELECT nom_utilisateur FROM utilisateur WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$nom_actuel = $user['nom'] ?? '';

// Traitement du formulaire
$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $nouveau_nom = trim($_POST['nom']);
    if ($nouveau_nom === '') {
        $message = 'Le nom ne peut pas être vide.';
        $type_message = 'error';
    } else {
        $upd = $pdo->prepare("UPDATE utilisateur SET nom_utilisateur = ? WHERE id_user = ?");
        $upd->execute([$nouveau_nom, $id_user]);
        $nom_actuel = $nouveau_nom;
        $_SESSION['nom'] = $nouveau_nom;
        $message = 'Nom enregistré avec succès.';
        $type_message = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>LivreurPro | Mon Profil</title>
    <link rel="stylesheet" href="../css/dark/us_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --cyan       : #00D4E8;
            --cyan-border: rgba(0,212,232,.3);
            --cyan-dim   : rgba(0,212,232,.08);
            --bg         : #13151A;
            --card       : #1C1F26;
            --card2      : #23272E;
            --card3      : #2A303A;
            --white      : #EFF3F8;
            --grey       : #7A8694;
            --grey-light : #B8C4D0;
        }

        .profil-wrap {
            max-width : 480px;
            margin    : 3rem auto;
            padding   : 0 1.2rem 4rem;
        }

        /* ── Titre page ── */
        .profil-hero {
            text-align   : center;
            margin-bottom: 2.2rem;
        }
        .profil-hero .avatar {
            width        : 72px; height: 72px;
            border-radius: 50%;
            background   : var(--card2);
            border       : 2px solid var(--cyan-border);
            display      : flex; align-items: center; justify-content: center;
            margin       : 0 auto 1rem;
            font-size    : 2rem;
            color        : var(--cyan);
        }
        .profil-hero h1 {
            font-family   : 'Barlow Condensed', sans-serif;
            font-size     : 1.6rem;
            font-weight   : 800;
            color         : var(--white);
            letter-spacing: .04em;
            margin-bottom : .3rem;
        }
        .profil-hero .mail {
            font-size: .82rem;
            color    : var(--grey);
        }

        /* ── Carte formulaire ── */
        .profil-card {
            background   : var(--card);
            border       : 1px solid var(--card3);
            border-radius: 10px;
            padding      : 1.8rem 1.6rem 1.4rem;
            position     : relative;
        }
        .profil-card::before {
            content : '';
            position: absolute;
            top: 0; left: 0;
            width: 3px; height: 100%;
            background   : var(--cyan);
            border-radius: 10px 0 0 10px;
        }

        .field-label {
            display       : block;
            font-size     : .68rem;
            font-weight   : 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color         : var(--grey);
            margin-bottom : .45rem;
        }
        .field-label i { color: var(--cyan); margin-right: .3rem; }

        .field-input {
            width        : 100%;
            padding      : .7rem 1rem;
            background   : var(--card2);
            border       : 1px solid var(--card3);
            border-radius: 6px;
            color        : var(--white);
            font-size    : .95rem;
            font-family  : inherit;
            transition   : border-color .2s, box-shadow .2s;
            box-sizing   : border-box;
        }
        .field-input:focus {
            outline     : none;
            border-color: var(--cyan);
            box-shadow  : 0 0 0 3px rgba(0,212,232,.12);
        }
        .field-input[readonly] {
            background: var(--card3);
            color     : var(--grey-light);
            cursor    : not-allowed;
        }

        /* ── Actions ── */
        .action-row {
            display : flex;
            gap     : .7rem;
            margin-top: 1.4rem;
        }
        .btn-save {
            flex        : 1;
            padding     : .7rem;
            background  : var(--cyan);
            color       : #111;
            border      : none;
            border-radius: 6px;
            font-family : inherit;
            font-size   : .85rem;
            font-weight : 700;
            cursor      : pointer;
            display     : flex; align-items: center; justify-content: center; gap: .4rem;
            transition  : background .2s, transform .2s;
        }
        .btn-save:hover { background: #00b8cc; transform: translateY(-1px); }

        .btn-edit {
            padding     : .7rem 1.1rem;
            background  : var(--card2);
            color       : var(--grey-light);
            border      : 1px solid var(--card3);
            border-radius: 6px;
            font-family : inherit;
            font-size   : .85rem;
            font-weight : 600;
            cursor      : pointer;
            display     : flex; align-items: center; gap: .4rem;
            transition  : all .2s;
        }
        .btn-edit:hover { border-color: var(--cyan-border); color: var(--cyan); }

        /* ── Message retour ── */
        .feedback {
            padding      : .65rem 1rem;
            border-radius: 6px;
            font-size    : .82rem;
            margin-bottom: 1.2rem;
            display      : flex;
            align-items  : center;
            gap          : .5rem;
        }
        .feedback.success { background: rgba(16,185,129,.12); color: #10B981; border: 1px solid rgba(16,185,129,.25); }
        .feedback.error   { background: rgba(239,68,68,.12);  color: #EF4444; border: 1px solid rgba(239,68,68,.25);  }

        /* ── Bouton suppression ── */
        .btn-delete {
            display      : flex;
            align-items  : center;
            justify-content: center;
            gap          : .5rem;
            width        : 100%;
            margin-top   : 1.4rem;
            padding      : .7rem;
            background   : transparent;
            border       : 1px solid rgba(239,68,68,.3);
            border-radius: 6px;
            color        : #EF4444;
            font-family  : inherit;
            font-size    : .82rem;
            font-weight  : 600;
            cursor       : pointer;
            text-decoration: none;
            transition   : all .2s;
        }
        .btn-delete:hover {
            background  : rgba(239,68,68,.1);
            border-color: #EF4444;
        }
    </style>
</head>
<body>

    <!-- ══════ HEADER ══════ -->
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></div>
                <a href="../index.html" style="text-decoration:none;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;
                                width:75px;height:48px;background:#00D4E8;border:2px solid #00D4E8;
                                clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
                                font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;
                                font-size:1.3rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>
                </a>
                <div class="nav-links" id="navLinks">
                    <a href="../index.html">Accueil</a>
                    <a href="us_livraison.php">Mes Commandes</a>
                    <a href="us_catalogue.php">Catalogue</a>
                    <a href="us_profil.php" class="active">Profil</a>
                    <a href="us_contact.php">Contact</a>
                    <button id="themeToggle" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.2rem; padding:0 12px;">
    <i class="fas fa-moon"></i>
</button>
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($mail) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ══════ CONTENU ══════ -->
    <main class="main">
        <div class="profil-wrap">

            <!-- Avatar + mail -->
            <div class="profil-hero">
                <div class="avatar"><i class="fas fa-user"></i></div>
                <h1>Mon Profil</h1>
                <span class="mail"><?= htmlspecialchars($mail) ?></span>
            </div>

            <!-- Message retour -->
            <?php if ($message): ?>
            <div class="feedback <?= $type_message ?>">
                <i class="fas <?= $type_message === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Carte formulaire -->
            <div class="profil-card">
                <form method="POST" id="profilForm">

                    <label class="field-label" for="nom">
                        <i class="fas fa-user"></i> Nom
                    </label>
                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        class="field-input"
                        value="<?= htmlspecialchars($nom_actuel) ?>"
                        placeholder="Entrez votre nom"
                        <?= $nom_actuel !== '' ? 'readonly' : '' ?>
                        required
                    />

                    <div class="action-row">
                        <?php if ($nom_actuel !== ''): ?>
                            <!-- Mode affichage : bouton Modifier -->
                            <button type="button" class="btn-edit" id="editBtn">
                                <i class="fas fa-pen"></i> Modifier
                            </button>
                            <button type="submit" class="btn-save" id="saveBtn" style="display:none;">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        <?php else: ?>
                            <!-- Aucun nom : bouton Enregistrer direct -->
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        <?php endif; ?>
                    </div>

                </form>

                <!-- Bouton suppression compte -->
                <a href="../models/us_sup.php"
                   class="btn-delete"
                   onclick="return confirm('Supprimer définitivement votre compte ? Cette action est irréversible.');">
                    <i class="fas fa-trash-alt"></i> Supprimer mon compte
                </a>
            </div>

        </div>
    </main>

    <script>
        // ── Hamburger ──
        document.getElementById('menuToggle').addEventListener('click', function () {
            this.classList.toggle('active');
            document.getElementById('navLinks').classList.toggle('active');
        });

        // ── Bouton Modifier : repasse le champ en éditable ──
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const nomInput = document.getElementById('nom');

        if (editBtn) {
            editBtn.addEventListener('click', function () {
                nomInput.removeAttribute('readonly');
                nomInput.focus();
                nomInput.select();
                editBtn.style.display = 'none';
                saveBtn.style.display = 'flex';
            });
        }
    </script>
</body>
</html>