<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivreurPro — Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,900;1,900&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--anthracite);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
        }

        /* ══ LEFT — décoratif ══ */
        .left {
            position: relative;
            background: var(--anthracite2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.8rem 3.2rem;
            overflow: hidden;
        }

        /* coin coupé cyan */
        .left::before {
            content: '';
            position: absolute;
            top: -60px; right: -40px;
            width: 200px; height: 200px;
            background: var(--cyan-dim);
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
            pointer-events: none;
        }

        /* bordure droite cyan */
        .left::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 2px; height: 100%;
            background: linear-gradient(to bottom, transparent, var(--cyan), transparent);
        }

        .logo {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 1.5rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--white);
            position: relative;
            z-index: 1;
        }
        .logo-dot { color: var(--cyan); }

        .left-body {
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.6rem;
        }
        .eyebrow-line { width: 36px; height: 1.5px; background: var(--cyan); }
        .eyebrow-text {
            font-size: .72rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--cyan);
            font-weight: 500;
        }

        .left h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(3.5rem, 5.5vw, 6rem);
            line-height: .92;
            text-transform: uppercase;
            color: var(--white);
            margin-bottom: 2rem;
        }
        .left h1 .outline {
            -webkit-text-stroke: 2px var(--cyan);
            color: transparent;
            display: block;
        }
        .left h1 .block { display: block; }

        .left-desc {
            font-size: .92rem;
            line-height: 1.8;
            color: var(--grey-light);
            font-weight: 300;
            border-left: 2px solid var(--cyan);
            padding-left: 1.1rem;
            max-width: 320px;
        }

        /* stats en bas */
        .stats {
            display: flex;
            gap: 0;
            position: relative;
            z-index: 1;
        }
        .stat { flex: 1; padding: 1.1rem 0; border-top: 1px solid var(--anthracite3); position: relative; }
        .stat + .stat { margin-left: 1.5rem; padding-left: 1.5rem; }
        .stat + .stat::before {
            content: '';
            position: absolute;
            left: 0; top: 1.1rem; bottom: 0;
            width: 1px; background: var(--anthracite3);
        }
        .stat-num {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 2rem;
            color: var(--white);
            letter-spacing: -.02em;
        }
        .stat-num sup { font-size: .9rem; color: var(--cyan); }
        .stat-label { font-size: .68rem; letter-spacing: .1em; text-transform: uppercase; color: var(--grey); margin-top: .25rem; }

        /* ══ RIGHT — formulaire ══ */
        .right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 3.5rem;
            background: var(--anthracite);
        }

        .form-card {
            width: 100%;
            max-width: 380px;
        }

        .form-header {
            margin-bottom: 2.8rem;
        }

        .form-index {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .68rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--cyan);
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1.2rem;
        }
        .form-index::after { content: ''; width: 24px; height: 1px; background: var(--cyan); opacity: .5; }

        .form-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-style: italic;
            font-size: 2.4rem;
            text-transform: uppercase;
            color: var(--white);
            letter-spacing: -.01em;
            line-height: 1;
        }
        .form-title span { color: var(--cyan); }

        .form-sub {
            font-size: .85rem;
            color: var(--grey-light);
            font-weight: 300;
            margin-top: .6rem;
        }

        /* champ */
        .form-group { margin-bottom: 1.6rem; }

        label {
            display: block;
            font-size: .72rem;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--grey-light);
            font-weight: 500;
            margin-bottom: .7rem;
        }

        input[type="number"] {
            width: 100%;
            padding: 1rem 1.2rem;
            font-family: 'Barlow', sans-serif;
            font-size: 1rem;
            font-weight: 400;
            background: var(--anthracite2);
            border: 1px solid var(--anthracite3);
            border-bottom: 2px solid var(--anthracite3);
            color: var(--white);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            appearance: textfield;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; }

        input[type="number"]:focus {
            border-color: var(--cyan);
            box-shadow: 0 2px 0 0 var(--cyan);
            background: var(--anthracite2);
        }
        input[type="number"]::placeholder { color: var(--grey); }

        /* bouton */
        button[type="submit"] {
            width: 100%;
            padding: .95rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            background: var(--cyan);
            color: var(--anthracite);
            border: none;
            cursor: pointer;
            clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px));
            transition: opacity .2s, transform .2s;
            margin-top: .4rem;
        }
        button[type="submit"]:hover { opacity: .85; transform: translateY(-1px); }

        /* lien bas */
        .form-footer {
            margin-top: 1.8rem;
            text-align: center;
            font-size: .8rem;
            color: var(--grey);
        }
        .form-footer a { color: var(--cyan); text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }

        /* bracket déco coin bas-droit */
        .bracket-br {
            position: fixed;
            bottom: 1.8rem; right: 1.8rem;
            width: 22px; height: 22px;
            border-bottom: 2px solid var(--cyan);
            border-right: 2px solid var(--cyan);
            opacity: .4;
        }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 780px) {
            body { grid-template-columns: 1fr; overflow: auto; }
            .left { display: none; }
            .right { min-height: 100vh; padding: 2.5rem 1.8rem; }
        }
    </style>
</head>
<body>

    <!-- ═══ GAUCHE ═══ -->
    <div class="left">
        <div class="logo">Livreur<span class="logo-dot">.</span>Pro</div>

        <div class="left-body">
            <div class="eyebrow">
                <span class="eyebrow-line"></span>
                <span class="eyebrow-text">Livraison Nouvelle Génération</span>
            </div>
            <h1>
                <span class="block">Livrer</span>
                <span class="outline">Vite.</span>
                <span class="block">Bien.</span>
            </h1>
            <p class="left-desc">
                Rejoignez LivreurPro et profitez d'un service de livraison rapide, fiable et moderne.
            </p>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-num">98<sup>%</sup></div>
                <div class="stat-label">À temps</div>
            </div>
            <div class="stat">
                <div class="stat-num">12<sup>k</sup></div>
                <div class="stat-label">Clients</div>
            </div>
            <div class="stat">
                <div class="stat-num">24<sup>h</sup></div>
                <div class="stat-label">Express</div>
            </div>
        </div>
    </div>

    <!-- ═══ DROITE — FORMULAIRE ═══ -->
    <div class="right">
        <div class="form-card">

            <div class="form-header">
                <div class="form-index">01 / Inscription</div>
                <div class="form-title">Entrez votre<br/><span>numéro</span></div>
                <p class="form-sub">Nous vous enverrons un code de confirmation.</p>
            </div>

            <form action="../models/traitement_inscription.php" method="POST">
                <div class="form-group">
                    <label for="numero">Numéro de téléphone</label>
                    <input
                        type="number"
                        id="numero"
                        name="numero"
                        placeholder="Ex: 6XX XXX XXX"
                        required
                        autofocus
                    >
                </div>

                <button type="submit">Valider &rarr;</button>
            </form>

            <div class="form-footer">
                Déjà inscrit ? <a href="#">Se connecter</a>
            </div>

        </div>
    </div>

    <div class="bracket-br"></div>

</body>
</html>