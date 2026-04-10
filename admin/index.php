<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$pdo = getPDO();
$nbUsers = $pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
$nbRes   = $pdo->query('SELECT COUNT(*) FROM ressources')->fetchColumn();
$derniers = $pdo->query(
    'SELECT u.prenom, u.nom, u.email, u.role, u.date_inscription
     FROM utilisateurs u ORDER BY u.date_inscription DESC LIMIT 5'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration — EduLib</title>
    <link rel="stylesheet" href="/mini-projet/assets/style.css">
</head>
<body>

<nav>
    <div class="container nav-inner">
        <a href="/mini-projet/" class="nav-brand">EduLib</a>
        <div class="nav-links">
            <a href="/mini-projet/">Accueil</a>
            <a href="/mini-projet/resources.php">Ressources</a>
            <a href="/mini-projet/admin/" class="active">Administration</a>
            <a href="/mini-projet/logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Tableau de bord administrateur</h1>
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:700;color:var(--accent)"><?= (int)$nbUsers ?></div>
                <div class="text-muted text-sm">utilisateurs</div>
            </div>
            <div class="card" style="flex:1;min-width:140px;text-align:center">
                <div style="font-size:2rem;font-weight:700;color:var(--accent)"><?= (int)$nbRes ?></div>
                <div class="text-muted text-sm">ressources</div>
            </div>
        </div>

        <div class="flex-between mb-2">
            <h2 style="font-size:1.1rem">Dernières inscriptions</h2>
            <a href="/mini-projet/admin/users.php" class="btn btn-sm btn-outline">Gérer les utilisateurs</a>
        </div>

        <div style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($derniers as $u): ?>
                        <tr>
                            <td><?= h($u['prenom']) ?> <?= h($u['nom']) ?></td>
                            <td><?= h($u['email']) ?></td>
                            <td><span class="badge"><?= h($u['role']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                <a href="/mini-projet/admin/">Administration</a>
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
