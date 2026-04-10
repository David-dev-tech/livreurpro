<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Aide — LivreurPro</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,700;0,900;1,900&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
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

    html, body { height: 100%; }

    body {
      background: var(--anthracite);
      color: var(--white);
      font-family: 'Barlow', sans-serif;
    }

    a { text-decoration: none; color: inherit; }

    /* ── HEADER / NAV ── */
    .top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 2rem 3.5rem;
      border-bottom: 1px solid var(--anthracite3);
    }

    .logo {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 900;
      font-size: 1.5rem;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--white);
    }
    .logo-dot { color: var(--cyan); }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: .6rem;
      background: transparent;
      color: var(--cyan);
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 700;
      font-size: .9rem;
      letter-spacing: .14em;
      text-transform: uppercase;
      padding: .7rem 1.8rem;
      border: 1px solid var(--cyan-border);
      cursor: pointer;
      clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
      transition: background .2s, color .2s, border-color .2s;
    }
    .btn-back:hover {
      background: var(--cyan);
      color: var(--anthracite);
      border-color: var(--cyan);
    }
    .btn-back svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2.2;
    }

    /* ── PAGE CONTENT ── */
    .page-content {
      max-width: 900px;
      margin: 0 auto;
      padding: 3.5rem 3.5rem 4rem;
      overflow-y: auto;
      max-height: calc(100vh - 88px);
    }

    /* eyebrow */
    .eyebrow {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.2rem;
    }
    .eyebrow-line {
      width: 32px; height: 1.5px;
      background: var(--cyan);
    }
    .eyebrow-text {
      font-size: .75rem;
      letter-spacing: .22em;
      text-transform: uppercase;
      color: var(--cyan);
      font-weight: 500;
    }

    /* title */
    h1 {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 900;
      font-style: italic;
      font-size: clamp(2.8rem, 5vw, 4.2rem);
      line-height: .95;
      letter-spacing: -.01em;
      text-transform: uppercase;
      color: var(--white);
      margin-bottom: 1rem;
    }
    h1 .outline {
      -webkit-text-stroke: 2px var(--cyan);
      color: transparent;
    }

    .page-desc {
      font-size: 1rem;
      line-height: 1.75;
      color: var(--grey-light);
      font-weight: 300;
      max-width: 640px;
      margin-bottom: 3rem;
      border-left: 2px solid var(--cyan);
      padding-left: 1rem;
    }

    /* ── STEPS ── */
    .steps { display: flex; flex-direction: column; gap: 2rem; }

    .step {
      display: flex;
      gap: 1.6rem;
      align-items: flex-start;
      background: var(--anthracite2);
      border: 1px solid var(--anthracite3);
      padding: 1.8rem 2rem;
      position: relative;
      transition: border-color .25s;
    }
    .step:hover { border-color: var(--cyan-border); }

    .step-num {
      flex-shrink: 0;
      width: 48px; height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--cyan-dim);
      border: 1px solid var(--cyan-border);
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 900;
      font-size: 1.3rem;
      color: var(--cyan);
      clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
    }

    .step-body h3 {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 700;
      font-size: 1.15rem;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--white);
      margin-bottom: .45rem;
    }
    .step-body p {
      font-size: .92rem;
      line-height: 1.7;
      color: var(--grey-light);
      font-weight: 300;
    }
    .step-body p strong {
      color: var(--cyan);
      font-weight: 500;
    }

    /* ── FOOTER NOTE ── */
    .footer-note {
      margin-top: 3rem;
      padding-top: 2rem;
      border-top: 1px solid var(--anthracite3);
      text-align: center;
      font-size: .82rem;
      color: var(--grey);
      letter-spacing: .06em;
    }
    .footer-note a {
      color: var(--cyan);
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 860px) {
      .top-bar { padding: 1.5rem 1.5rem; }
      .page-content { padding: 2rem 1.5rem; max-height: none; }
      .step { flex-direction: column; gap: 1rem; }
      h1 { font-size: 2.4rem; }
    }
  </style>
</head>
<body>

  <!-- ═══ TOP BAR ═══ -->
  <header class="top-bar">
    <div class="logo">Livreur<span class="logo-dot">.</span>Pro</div>
    <a href="../index.html" class="btn-back">
      <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Accueil
    </a>
  </header>

  <!-- ═══ MAIN CONTENT ═══ -->
  <main class="page-content">

    <div class="eyebrow">
      <span class="eyebrow-line"></span>
      <span class="eyebrow-text">Centre d'aide</span>
    </div>

    <h1>Comment utiliser <span class="outline">LivreurPro</span></h1>

    <p class="page-desc">
      Bienvenue sur LivreurPro&nbsp;! Suivez ces étapes simples pour profiter pleinement de notre service de livraison rapide, fiable et sécurisé.
    </p>

    <!-- ── STEPS ── -->
    <section class="steps">

      <div class="step">
        <div class="step-num">01</div>
        <div class="step-body">
          <h3>Créez votre compte</h3>
          <p>Rendez-vous sur la page d'<a href="form_inscription.php" style="color:var(--cyan);text-decoration:underline;text-underline-offset:3px;">inscription</a> et remplissez le formulaire avec vos informations personnelles. En quelques clics, votre compte est prêt à être utilisé.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-num">02</div>
        <div class="step-body">
          <h3>Consultez le catalogue</h3>
          <p>Parcourez notre <a href="catalogue.php" style="color:var(--cyan);text-decoration:underline;text-underline-offset:3px;">catalogue</a> pour découvrir les services disponibles, comparer les options et choisir celle qui correspond le mieux à vos besoins.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-num">03</div>
        <div class="step-body">
          <h3>Gérez votre profil</h3>
          <p>Une fois inscrit, accédez à votre <a href="us_profil.php" style="color:var(--cyan);text-decoration:underline;text-underline-offset:3px;">espace profil</a> pour suivre vos livraisons, mettre à jour vos coordonnées et gérer vos préférences.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-num">04</div>
        <div class="step-body">
          <h3>Suivez vos livraisons</h3>
          <p>Depuis votre tableau de bord, suivez en temps réel l'état de chaque livraison. Vous recevez des notifications à chaque étape pour rester informé.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-num">05</div>
        <div class="step-body">
          <h3>Besoin d'aide&nbsp;?</h3>
          <p>Si vous rencontrez un problème ou avez une question, n'hésitez pas à nous <a href="#" style="color:var(--cyan);text-decoration:underline;text-underline-offset:3px;">contacter</a>. Notre équipe vous répondra dans les plus brefs délais.</p>
        </div>
      </div>

    </section>

    <div class="footer-note">
      LivreurPro &mdash; La fiabilité à chaque km. <a href="../index.html">Retour à l'accueil</a>
    </div>

  </main>

</body>
</html>
