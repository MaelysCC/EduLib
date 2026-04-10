<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = getPDO();
    $nbRessources = $pdo->query('SELECT COUNT(*) FROM ressources')->fetchColumn();
    $nbUsers      = $pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
} catch (Exception $e) {
    $nbRessources = 0;
    $nbUsers = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduLib — Bibliothèque de ressources étudiantes</title>
    <link rel="stylesheet" href="/mini-projet/assets/style.css">
</head>
<body>

<nav>
    <div class="container nav-inner">
        <a href="/mini-projet/" class="nav-brand">EduLib</a>
        <div class="nav-links">
            <a href="/mini-projet/" class="active">Accueil</a>
            <a href="/mini-projet/resources.php">Ressources</a>
            <?php if (isLoggedIn()): ?>
                <a href="/mini-projet/profile.php">Mon profil</a>
                <?php if (isAdmin()): ?>
                    <a href="/mini-projet/admin/">Administration</a>
                <?php endif; ?>
                <a href="/mini-projet/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="/mini-projet/login.php">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="hero-box">
        <div class="hero-eyebrow">EduLib c'est quoi ?</div>
        <h1>Partagez et trouvez des ressources étudiantes</h1>
        <div class="hero-actions">
            <?php if (!isLoggedIn()): ?>
                <a href="/mini-projet/register.php" class="btn">S'inscrire gratuitement</a>
                <a href="/mini-projet/resources.php" class="btn btn-outline">Voir les ressources</a>
            <?php else: ?>
                <a href="/mini-projet/add-resource.php" class="btn">Déposer une ressource</a>
                <a href="/mini-projet/resources.php" class="btn btn-outline">Parcourir les fiches</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<main>
    <div class="container">

        <div class="eco-block">
            <div class="eco-label">Mini projet Green IT</div>
            <h2>Engagement écologique</h2>
            <p>Le numérique n'est pas immatériel : chaque page chargée, chaque requête, chaque média embarqué, chaque bibliothèque ajoutée consomme des ressources. Avec EduLib, vous pourrez réviser vos CE et DE tout en étant écologique, grâce à cet espace centralisé et sobre pour déposer, consulter et trouver des fiches de cours par matière. Plus de temps perdu à chercher des ressources pédagogiques dispersées sur de multiples plateformes avec notre site !</p>
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap;padding:2rem 0;border-bottom:1px solid var(--border)">
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:800;color:var(--accent)"><?= (int)$nbRessources ?></div>
                <div class="text-muted text-sm">fiches disponibles</div>
            </div>
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:800;color:var(--accent)"><?= (int)$nbUsers ?></div>
                <div class="text-muted text-sm">membres inscrits</div>
            </div>
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:800;color:var(--accent)"><?= count(CATEGORIES) ?></div>
                <div class="text-muted text-sm">catégories</div>
            </div>
        </div>

        <div style="padding:2rem 0">
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem">Catégories disponibles</h2>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                <?php foreach (CATEGORIES as $cat): ?>
                    <a href="/mini-projet/resources.php?categorie=<?= urlencode($cat) ?>" class="badge" style="text-decoration:none;color:var(--accent)">
                        <?= h($cat) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<footer>
    <div class="container footer-inner">
        <div class="footer-brand">
            <span class="footer-logo">EduLib</span>
            <div class="footer-social">
                <a href="#"><span aria-hidden="true">IG</span><span class="sr-only">Instagram</span></a>
                <a href="#"><span aria-hidden="true">in</span><span class="sr-only">LinkedIn</span></a>
                <a href="#"><span aria-hidden="true">✕</span><span class="sr-only">X / Twitter</span></a>
            </div>
        </div>
        <div class="footer-cols">
            <div class="footer-col">
                <strong>Navigation</strong>
                <a href="/mini-projet/">Accueil</a>
                <a href="/mini-projet/resources.php">Ressources</a>
                <a href="/mini-projet/add-resource.php">Déposer une fiche</a>
            </div>
            <div class="footer-col">
                <strong>Compte</strong>
                <a href="/mini-projet/login.php">Connexion</a>
                <a href="/mini-projet/register.php">S'inscrire</a>
                <a href="/mini-projet/profile.php">Mon profil</a>
            </div>
            <div class="footer-col">
                <strong>Légal</strong>
                <a href="#">Mentions légales</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
