<?php
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$stmt = getPDO()->prepare(
    'SELECT r.*, u.nom, u.prenom
     FROM ressources r
     JOIN utilisateurs u ON u.id = r.auteur_id
     WHERE r.id = ?'
);
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$canEdit = isLoggedIn() && ($_SESSION['user_id'] === $r['auteur_id'] || isAdmin());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($r['titre']) ?> — EduLib</title>
    <link rel="stylesheet" href="/mini-projet/assets/style.css">
</head>
<body>

<nav>
    <div class="container nav-inner">
        <a href="/mini-projet/" class="nav-brand">EduLib</a>
        <div class="nav-links">
            <a href="/mini-projet/">Accueil</a>
            <a href="/mini-projet/resources.php" class="active">Ressources</a>
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

<main>
    <div class="container">
        <div class="page-header">
            <div class="flex-between">
                <div>
                    <h1><?= h($r['titre']) ?></h1>
                    <p>
                        <span class="badge"><?= h($r['categorie']) ?></span>
                        &nbsp;Par <?= h($r['prenom']) ?> <?= h($r['nom']) ?>
                        &nbsp;&mdash;&nbsp;<?= date('d/m/Y à H:i', strtotime($r['date_depot'])) ?>
                    </p>
                </div>
                <div style="display:flex;gap:.5rem">
                    <?php if ($canEdit): ?>
                        <a href="/mini-projet/edit-resource.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
                        <form method="post" action="/mini-projet/delete-resource.php" style="display:inline">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Supprimer cette ressource ?')">
                                Supprimer
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="/mini-projet/resources.php" class="btn btn-sm btn-muted">← Retour</a>
                </div>
            </div>
        </div>

        <?php if ($r['description']): ?>
            <div class="alert alert-info mb-2">
                <strong>Description :</strong> <?= h($r['description']) ?>
            </div>
        <?php endif; ?>

        <?php if ($r['image']): ?>
            <div class="mb-2">
                <img src="<?= UPLOAD_URL . h($r['image']) ?>"
                     alt="Illustration de la ressource"
                     style="max-width:100%;max-height:400px;border-radius:6px;border:1px solid var(--border);display:block">
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="pre-wrap"><?= h($r['contenu']) ?></div>
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
