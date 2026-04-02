<?php
require_once __DIR__ . '/config/db.php';

$pdo = getPDO();

$categorie = trim($_GET['categorie'] ?? '');
$search    = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

if ($categorie !== '' && in_array($categorie, CATEGORIES, true)) {
    $where[]  = 'r.categorie = ?';
    $params[] = $categorie;
}
if ($search !== '') {
    $where[]  = '(r.titre LIKE ? OR r.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql = 'SELECT r.id, r.titre, r.description, r.categorie, r.date_depot,
               u.prenom, u.nom
        FROM ressources r
        JOIN utilisateurs u ON u.id = r.auteur_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.date_depot DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ressources = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ressources<?= $categorie ? ' — ' . h($categorie) : '' ?> — EduLib</title>
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
                <a href="/mini-projet/register.php" class="btn btn-sm" style="margin-left:.25rem">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>
    <div class="container">
        <div class="page-header">
            <div class="flex-between">
                <div>
                    <h1>Ressources pédagogiques</h1>
                    <p><?= count($ressources) ?> fiche<?= count($ressources) > 1 ? 's' : '' ?> trouvée<?= count($ressources) > 1 ? 's' : '' ?></p>
                </div>
                <?php if (isLoggedIn()): ?>
                    <a href="/mini-projet/add-resource.php" class="btn">+ Déposer une fiche</a>
                <?php endif; ?>
            </div>
        </div>

        <form method="get" class="filter-bar">
            <select name="categorie" onchange="this.form.submit()">
                <option value="">Toutes les catégories</option>
                <?php foreach (CATEGORIES as $cat): ?>
                    <option value="<?= h($cat) ?>" <?= $categorie === $cat ? 'selected' : '' ?>>
                        <?= h($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="q" placeholder="Rechercher…" value="<?= h($search) ?>">
            <button type="submit" class="btn btn-outline">Filtrer</button>
            <?php if ($categorie || $search): ?>
                <a href="/mini-projet/resources.php" class="btn btn-muted">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($ressources)): ?>
            <div class="alert alert-info">Aucune ressource ne correspond à votre recherche.</div>
        <?php else: ?>
            <?php foreach ($ressources as $r): ?>
                <div class="resource-item">
                    <div>
                        <h3><a href="/mini-projet/resource-detail.php?id=<?= $r['id'] ?>"><?= h($r['titre']) ?></a></h3>
                        <div class="resource-meta">
                            <span class="badge"><?= h($r['categorie']) ?></span>
                            &nbsp;<?= h($r['prenom']) ?> <?= h($r['nom']) ?>
                            &nbsp;&mdash;&nbsp;<?= date('d/m/Y', strtotime($r['date_depot'])) ?>
                        </div>
                        <?php if ($r['description']): ?>
                            <p class="text-sm text-muted mt-1" style="margin-top:.3rem"><?= h(mb_substr($r['description'], 0, 120)) ?><?= mb_strlen($r['description']) > 120 ? '…' : '' ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="resource-actions">
                        <a href="/mini-projet/resource-detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Voir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="container">
        <a href="#">Mentions légales</a> &mdash; EduLib &copy; <?= date('Y') ?>
    </div>
</footer>

</body>
</html>
