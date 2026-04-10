<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LivreurPro | Administration</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,900&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* =========================
   RESET
========================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Barlow',sans-serif;
    background:#1C1F24;
    color:#F2F4F7;
}

/* =========================
   VARIABLES
========================= */
:root{
    --bg-main:#1C1F24;
    --bg-secondary:#252930;
    --bg-card:#2F343C;

    --primary:#00D4E8;
    --primary-light:rgba(0,212,232,0.10);
    --primary-border:rgba(0,212,232,0.22);

    --text:#F2F4F7;
    --text-gray:#6B7280;
    --text-light:#9CA3AF;
}

/* =========================
   LAYOUT
========================= */
.container{
    max-width:1400px;
    margin:auto;
    padding:0 24px;
}

.main{
    padding:30px 0 50px;
}

/* =========================
   HEADER
========================= */
.header{
    background:var(--bg-secondary);
    border-bottom:1px solid var(--bg-card);
    position:sticky;
    top:0;
    z-index:100;
}

.nav-container{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:1rem 0;
    flex-wrap:wrap;
    gap:1rem;
}

/* =========================
   MENU BURGER
========================= */
.menu-toggle{
    display:none;
    flex-direction:column;
    justify-content:space-between;
    width:30px;
    height:22px;
    cursor:pointer;
}

.menu-toggle span{
    width:100%;
    height:3px;
    background:var(--primary);
    border-radius:2px;
    transition:0.3s;
}

.menu-toggle.active span:nth-child(1){
    transform:translateY(9px) rotate(45deg);
}
.menu-toggle.active span:nth-child(2){
    opacity:0;
}
.menu-toggle.active span:nth-child(3){
    transform:translateY(-9px) rotate(-45deg);
}

/* =========================
   NAVIGATION
========================= */
.nav-links{
    display:flex;
    gap:2rem;
    align-items:center;
}

.nav-links a{
    text-decoration:none;
    font-size:0.85rem;
    font-weight:500;
    letter-spacing:0.12em;
    text-transform:uppercase;
    color:var(--text-light);
    transition:0.2s;
}

.nav-links a:hover{
    color:var(--primary);
}

.nav-links a.active{
    color:var(--primary);
}

/* =========================
   USER INFO
========================= */
.user-info{
    display:flex;
    align-items:center;
    gap:1rem;
    background:var(--bg-card);
    padding:0.5rem 1.2rem;
    border-radius:4px;
    border-left:2px solid var(--primary);
}

.user-info i{
    color:var(--primary);
}

.user-info span{
    color:var(--text-light);
}

.logout-btn{
    background:none;
    border:none;
    color:var(--text-gray);
    cursor:pointer;
}

.logout-btn:hover{
    color:var(--primary);
}

/* =========================
   FORMULAIRE
========================= */
.form-container{
    background:var(--bg-secondary);
    border:1px solid var(--bg-card);
    border-left:3px solid var(--primary);
    padding:25px;
    border-radius:6px;
    max-width:900px;
    margin:auto;
}

.form-container h2{
    font-family:'Barlow Condensed',sans-serif;
    color:var(--primary);
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
}

/* GRID */
.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group.full{
    grid-column:1/3;
}

/* INPUTS */
.form-group label{
    font-size:0.8rem;
    margin-bottom:5px;
    color:var(--text-light);
}

.form-group input,
.form-group select,
.form-group textarea{
    background:var(--bg-card);
    border:1px solid var(--bg-card);
    color:var(--text);
    padding:10px;
    border-radius:4px;
    outline:none;
    transition:0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    border:1px solid var(--primary);
    box-shadow:0 0 0 2px var(--primary-light);
}

textarea{
    resize:none;
    height:80px;
}

/* BUTTON */
.submit-btn{
    margin-top:20px;
    background:var(--primary);
    color:#1C1F24;
    border:none;
    padding:12px 20px;
    font-weight:600;
    cursor:pointer;
    border-radius:4px;
    display:flex;
    align-items:center;
    gap:10px;
    transition:0.2s;
}

.submit-btn:hover{
    opacity:0.85;
}

/* =========================
   RESPONSIVE
========================= */
@media(max-width:768px){

    .menu-toggle{
        display:flex;
    }

    .nav-links{
        position:fixed;
        top:0;
        left:-100%;
        width:70%;
        height:100vh;
        background:var(--bg-secondary);
        flex-direction:column;
        align-items:flex-start;
        padding:80px 2rem;
        transition:0.3s;
    }

    .nav-links.active{
        left:0;
    }

    .nav-links a{
        width:100%;
        border-bottom:1px solid var(--bg-card);
        padding:10px 0;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .form-group.full{
        grid-column:1;
    }
}
</style>
</head>
<body>
<header class="header">
<div class="container">
<div class="nav-container">
    <div class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></div>
    <a href="admin_dashboard.php" style="text-decoration:none">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:48px;background:#00D4E8;clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));font-family:'Barlow Condensed',sans-serif;font-weight:900;font-style:italic;font-size:1.5rem;color:#1C1F24">L.Pro</div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="ad_dashboard.php">Dashboard</a>
        <a href="ad_utilisateur.php">Utilisateurs</a>
        <a href="ad_livreur.php" class="active">Livreurs</a>
        <a href="ad_livraison.php">Livraisons</a>
        <a href="ad_rapport.php">Rapports</a>
        <div class="user-info">
            <i class="fas fa-user-shield"></i>
            <span>Admin</span>
            <button class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </div>
</div>
</div>
</header>

<main class="main">
<div class="container">
   <div class="form-container">
    <h2><i class="fas fa-motorcycle"></i> Ajouter un livreur</h2>

    <form action="traitement_livreur.php" method="POST">
        
        <div class="form-grid">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>

            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" required>
            </div>

            <div class="form-group">
                <label>Âge</label>
                <input type="number" name="age">
            </div>

            <div class="form-group">
                <label>Sexe</label>
                <select name="sex" required>
                    <option value="">Choisir</option>
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Numéro</label>
                <input type="text" name="numero" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="mail">
            </div>

            <div class="form-group full">
                <label>Adresse</label>
                <textarea name="adresse"></textarea>
            </div>

            <div class="form-group">
                <label>Numéro CNI</label>
                <input type="text" name="numero_cni">
            </div>

            <div class="form-group">
                <label>Carte grise</label>
                <input type="text" name="carte_grise">
            </div>
        </div>

        <button type="submit" class="submit-btn">
            <i class="fas fa-plus"></i> Enregistrer
        </button>

    </form>
</div>
</div>
</main>

<script>
var mt = document.getElementById('menuToggle'),
    nl = document.getElementById('navLinks');
mt.addEventListener('click', function(){ mt.classList.toggle('active'); nl.classList.toggle('active'); });

document.getElementById('logoutBtn').addEventListener('click', function(){
    if (confirm('Voulez-vous vous déconnecter ?')) window.location.href = 'index.html';
});
</script>
</body>
</html>