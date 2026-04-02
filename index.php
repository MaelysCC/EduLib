<?php
require_once __DIR__ . '/config/db.php';

// Quelques stats pour la page d'accueil
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
                <a href="/mini-projet/register.php" class="btn btn-sm" style="margin-left:.25rem">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <h1>Partagez et trouvez des ressources étudiantes</h1>
        <p>EduLib centralise les fiches de cours, résumés et supports pédagogiques créés par les étudiants, pour les étudiants.</p>
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

        <div class="eco-block mt-3">
            <strong>Engagement écologique</strong>
            Site sobre : &lt;&nbsp;100&nbsp;Ko par page &mdash; 0 tracker &mdash; police système uniquement &mdash; HTML/CSS pur &mdash; hébergement vert
        </div>

        <div class="flex-between mt-3 mb-2">
            <h2 style="font-size:1.1rem">Chiffres clés</h2>
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap">
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:700;color:var(--accent)"><?= (int)$nbRessources ?></div>
                <div class="text-muted text-sm">fiches disponibles</div>
            </div>
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:700;color:var(--accent)"><?= (int)$nbUsers ?></div>
                <div class="text-muted text-sm">membres inscrits</div>
            </div>
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:700;color:var(--accent)"><?= count(CATEGORIES) ?></div>
                <div class="text-muted text-sm">catégories</div>
            </div>
        </div>

        <div class="mt-3 mb-1">
            <h2 style="font-size:1.1rem;margin-bottom:.75rem">Catégories disponibles</h2>
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
    <div class="container">
        <a href="#">Mentions légales</a> &mdash; <a href="#">Contact</a> &mdash; EduLib &copy; <?= date('Y') ?>
    </div>
</footer>

</body>
</html>
