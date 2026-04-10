<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LivreurPro - Inscription</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,700;0,900&family=Barlow:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    *, *::before, *::after { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0; 
    }

    :root {
      --anthracite:  #1C1F24;
      --cyan:        #00D4E8;
      --grey-light:  #9CA3AF;
      --white:       #F2F4F7;
    }

    body {
      background: var(--anthracite);
      color: var(--white);
      font-family: 'Barlow', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .form-container {
      background: #252930;
      width: 100%;
      max-width: 480px;
      padding: 3.5rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
      text-align: center;
    }

    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      margin-bottom: 2.5rem;
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 900;
      font-size: 2rem;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .logo-box {
      width: 58px;
      height: 58px;
      background: var(--cyan);
      color: #1C1F24;
      font-size: 1.7rem;
      font-weight: 900;
      display: flex;
      align-items: center;
      justify-content: center;
      clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px));
    }

    h1 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2.1rem;
      font-weight: 900;
      margin-bottom: 0.8rem;
    }

    .subtitle {
      color: var(--grey-light);
      font-size: 1.05rem;
      margin-bottom: 2.5rem;
    }

    .form-group {
      margin-bottom: 2rem;
      text-align: left;
    }

    label {
      display: block;
      font-size: 0.95rem;
      color: var(--grey-light);
      margin-bottom: 0.7rem;
      font-weight: 500;
    }

    .phone-input {
      display: flex;
      align-items: center;
      background: #1C1F24;
      border: 1px solid #3A3F48;
      border-radius: 8px;
      padding: 0.8rem 1rem;
      transition: all 0.3s;
    }

    .phone-input:focus-within {
      border-color: var(--cyan);
      box-shadow: 0 0 0 4px rgba(0, 212, 232, 0.15);
    }

    .phone-input i {
      color: var(--cyan);
      margin-right: 12px;
      font-size: 1.3rem;
    }

    input[type="tel"] {
      background: transparent;
      border: none;
      outline: none;
      color: white;
      font-size: 1.15rem;
      flex: 1;
    }

    .btn-submit {
      background: var(--cyan);
      color: #1C1F24;
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 700;
      font-size: 1.15rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      padding: 1.25rem;
      width: 100%;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 1rem;
    }

    .btn-submit:hover {
      background: #00e8ff;
      transform: translateY(-3px);
    }

    .info {
      margin-top: 2rem;
      font-size: 0.95rem;
      color: var(--grey-light);
    }
  </style>
</head>
<body>

  <div class="form-container">

    <!-- Logo -->
    <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;background:#00D4E8;border:2px solid #00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.3rem;color:#1C1F24;letter-spacing:-.02em;">L.Pro</div>

    <h1>Commencer maintenant</h1>
    <p class="subtitle">Entrez votre numéro de téléphone pour continuer</p>

    <!-- Formulaire -->
    <form action="../models/inscrire_user.php" method="POST">

      <div class="form-group">
        <label for="telephone">Numéro de téléphone</label>
        <div class="phone-input">
          <i class="fas fa-phone"></i>
          <input
            type="tel"
            id="telephone"
            name="numero"
            placeholder="6XX XXX XXX"
            required
          >
        </div>
      </div>

      <button type="submit" class="btn-submit">
        DÉMARRER
      </button>

    </form>

    <div class="info">
      Vous recevrez un code de vérification par SMS
    </div>

  </div>

</body>
</html>