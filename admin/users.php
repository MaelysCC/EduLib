<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$pdo   = getPDO();
$users = $pdo->query(
    'SELECT u.id, u.nom, u.prenom, u.email, u.role, u.date_inscription,
            COUNT(r.id) AS nb_ressources
     FROM utilisateurs u
     LEFT JOIN ressources r ON r.auteur_id = u.id
     GROUP BY u.id
     ORDER BY u.date_inscription DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des utilisateurs — EduLib</title>
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
            <div class="flex-between">
                <div>
                    <h1>Gestion des utilisateurs</h1>
                    <p><?= count($users) ?> compte<?= count($users) > 1 ? 's' : '' ?> enregistré<?= count($users) > 1 ? 's' : '' ?></p>
                </div>
                <a href="/mini-projet/admin/" class="btn btn-sm btn-muted">← Tableau de bord</a>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Utilisateur supprimé.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Utilisateur mis à jour.</div>
        <?php endif; ?>

        <div style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Fiches</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= h($u['prenom']) ?> <?= h($u['nom']) ?></td>
                            <td><?= h($u['email']) ?></td>
                            <td><span class="badge"><?= h($u['role']) ?></span></td>
                            <td><?= (int)$u['nb_ressources'] ?></td>
                            <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                            <td>
                                <a href="/mini-projet/admin/edit-user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <form method="post" action="/mini-projet/admin/delete-user.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Supprimer cet utilisateur et toutes ses ressources ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted text-sm">(vous)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <a href="#">Mentions légales</a> &mdash; EduLib &copy; <?= date('Y') ?>
    </div>
</footer>

</body>
</html>
