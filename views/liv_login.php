<?php
session_start();

// Si déjà connecté → redirection
if (isset($_SESSION['id_livreur'])) {
    header('Location: liv_dashboard.php');
    exit;
}

// Inclusion du fichier de connexion
require_once '../config/config.php';   // ←←← Important

$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail       = trim($_POST['mail'] ?? '');
    $numero_cni = trim($_POST['numero_cni'] ?? '');

    if (empty($mail) || empty($numero_cni)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM livreur 
                WHERE mail = ? AND numero_cni = ? 
                LIMIT 1
            ");
            $stmt->execute([$mail, $numero_cni]);
            $livreur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($livreur) {
                // Création des sessions
                $_SESSION['id_livreur']       = $livreur['id_livreur'];
                $_SESSION['livreur_nom']      = $livreur['nom'];
                $_SESSION['livreur_prenom']   = $livreur['prenom'];
                $_SESSION['livreur_mail']     = $livreur['mail'];
                $_SESSION['livreur_vehicule'] = $livreur['type_vehicule'];

                header('Location: liv_dashboard.php');
                exit;
            } else {
                $error = 'Email ou numéro CNI incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur serveur : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivreurPro | Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg:       #13151A;
            --surface:  #1C1F24;
            --surface2: #23272E;
            --border:   #2A2D35;
            --accent:   #00D4E8;
            --accent-dim: rgba(0,212,232,.1);
            --accent-glow: 0 0 28px rgba(0,212,232,.2);
            --text:     #E8EAF0;
            --text-muted:#8A8F9A;
            --danger:   #F87171;
            --radius:   10px;
            --font-d:   'Barlow Condensed', sans-serif;
            --font-b:   'Barlow', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: var(--font-b);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Fond animé ── */
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(0,212,232,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,212,232,.04) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
        }
        .bg-glow {
            position: fixed; z-index: 0;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(0,212,232,.08) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            animation: pulse 4s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: .6; transform: translate(-50%,-50%) scale(1); }
            50%       { opacity: 1;  transform: translate(-50%,-50%) scale(1.08); }
        }

        /* ── Carte connexion ── */
        .login-wrap {
            position: relative; z-index: 1;
            width: 100%; max-width: 420px;
            padding: 20px;
            animation: slideUp .4s ease both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: none; }
        }

        /* Logo */
        .logo-block {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 32px;
        }
        .logo-chip {
            display: inline-flex; align-items: center; justify-content: center;
            width: 68px; height: 48px;
            background: var(--accent);
            clip-path: polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
            font-family: var(--font-d); font-weight: 900; font-style: italic;
            font-size: 1.5rem; color: #1C1F24; letter-spacing: -.02em;
            flex-shrink: 0;
        }
        .logo-text {
            font-family: var(--font-d); font-weight: 900; font-style: italic;
            font-size: 1.6rem; letter-spacing: -.02em; line-height: 1;
        }
        .logo-text small {
            display: block; font-size: .72rem; font-style: normal;
            font-weight: 600; letter-spacing: .1em; color: var(--text-muted);
            text-transform: uppercase; margin-top: 2px;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: 0 24px 60px rgba(0,0,0,.4), var(--accent-glow);
        }
        .card-title {
            font-family: var(--font-d); font-weight: 900;
            font-size: 1.65rem; line-height: 1; letter-spacing: -.02em;
            margin-bottom: 6px;
        }
        .card-title span { color: var(--accent); }
        .card-sub {
            font-size: .85rem; color: var(--text-muted);
            margin-bottom: 28px; line-height: 1.5;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-family: var(--font-d); font-weight: 700;
            font-size: .72rem; text-transform: uppercase; letter-spacing: .07em;
            color: var(--text-muted); margin-bottom: 7px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: .9rem;
            pointer-events: none;
            transition: color .2s;
        }
        .input-wrap input {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 7px;
            padding: 11px 12px 11px 38px;
            font-family: var(--font-b); font-size: .9rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap input::placeholder { color: var(--text-muted); opacity: .6; }
        .input-wrap input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0,212,232,.12);
        }
        .input-wrap input:focus + i,
        .input-wrap:focus-within i { color: var(--accent); }

        /* Toggle password */
        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: .85rem; padding: 4px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--accent); }

        /* Error */
        .error-msg {
            display: flex; align-items: center; gap: 9px;
            background: rgba(248,113,113,.1);
            border: 1px solid rgba(248,113,113,.3);
            color: var(--danger);
            border-radius: 7px; padding: 10px 14px;
            font-size: .85rem; font-weight: 500;
            margin-bottom: 20px;
            animation: shake .35s ease;
        }
        @keyframes shake {
            0%,100% { transform: none; }
            20%,60%  { transform: translateX(-5px); }
            40%,80%  { transform: translateX(5px); }
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: var(--accent); color: #1C1F24;
            border: none; border-radius: 7px;
            padding: 13px 20px;
            font-family: var(--font-b); font-weight: 700; font-size: .95rem;
            cursor: pointer;
            transition: opacity .2s, box-shadow .2s, transform .15s;
            margin-top: 8px;
        }
        .btn-submit:hover {
            opacity: .9;
            box-shadow: 0 6px 20px rgba(0,212,232,.3);
            transform: translateY(-1px);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 22px 0 0;
            color: var(--text-muted); font-size: .78rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--border);
        }

        /* Footer note */
        .login-footer {
            text-align: center; margin-top: 20px;
            font-size: .78rem; color: var(--text-muted);
        }
        .login-footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .login-footer a:hover { text-decoration: underline; }

        /* Décoration coins */
        .card::before {
            content: '';
            display: block;
            position: absolute;
            top: -1px; left: -1px;
            width: 40px; height: 40px;
            border-top: 2px solid var(--accent);
            border-left: 2px solid var(--accent);
            border-radius: 10px 0 0 0;
            pointer-events: none;
        }
        .card { position: relative; }
        .card::after {
            content: '';
            display: block;
            position: absolute;
            bottom: -1px; right: -1px;
            width: 40px; height: 40px;
            border-bottom: 2px solid var(--accent);
            border-right: 2px solid var(--accent);
            border-radius: 0 0 10px 0;
            pointer-events: none;
        }
    </style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow"></div>

<div class="login-wrap">

    <!-- Logo -->
    <div class="logo-block">
        <div class="logo-chip">L.Pro</div>
        <div class="logo-text">
            LivreurPro
            <small>Espace Livreur</small>
        </div>
    </div>

    <!-- Carte -->
    <div class="card">
        <div class="card-title">Connexion <span>Livreur</span></div>
        <p class="card-sub">Entrez votre email et votre numéro CNI pour accéder à votre espace.</p>

        <!-- Message d'erreur -->
        <?php if ($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <!-- Email -->
            <div class="form-group">
                <label for="mail">Adresse email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input
                        type="email"
                        id="mail"
                        name="mail"
                        placeholder="exemple@email.com"
                        value="<?php echo htmlspecialchars($_POST['mail'] ?? ''); ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Numéro CNI -->
            <div class="form-group">
                <label for="numero_cni">Numéro CNI</label>
                <div class="input-wrap">
                    <i class="fas fa-id-card"></i>
                    <input
                        type="password"
                        id="numero_cni"
                        name="numero_cni"
                        placeholder="Votre numéro de CNI"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="toggleCNI()" title="Afficher/Masquer">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>

        </form>

        <div class="divider">Accès sécurisé</div>

        <div class="login-footer" style="margin-top:14px;">
            <i class="fas fa-lock" style="color:var(--accent);font-size:.7rem;"></i>
            Vos informations sont utilisées uniquement pour vous identifier.<br>
            <span style="margin-top:6px;display:block;">
                Problème de connexion ? Contactez votre&nbsp;
                <a href="mailto:admin@livpro.cm">administrateur</a>.
            </span>
        </div>
    </div>

</div>

<script>
function toggleCNI() {
    const input   = document.getElementById('numero_cni');
    const icon    = document.getElementById('eyeIcon');
    const visible = input.type === 'text';
    input.type    = visible ? 'password' : 'text';
    icon.className= visible ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>