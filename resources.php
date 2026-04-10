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

// Grouper par catégorie
$grouped = [];
foreach (CATEGORIES as $cat) {
    $grouped[$cat] = [];
}
foreach ($ressources as $r) {
    if (isset($grouped[$r['categorie']])) {
        $grouped[$r['categorie']][] = $r;
    }
}
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
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="resources-hero">
    <div class="container">
        <h1>Ressources</h1>
        <p>Ajoute ou trouve la fiche de révisions ou le document dont tu as besoin</p>
        <div class="resources-hero-actions">
            <a href="#filter" class="btn">Filtre</a>
            <?php if (isLoggedIn()): ?>
                <a href="/mini-projet/add-resource.php" class="btn btn-outline">Ajout</a>
            <?php else: ?>
                <a href="/mini-projet/login.php" class="btn btn-outline">Connexion pour ajouter</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<main>
    <div class="container">

        <form id="filter" method="get" class="filter-bar" style="padding:1rem 0;border-bottom:1px solid var(--border);margin-bottom:1.25rem">
            <select name="categorie" onchange="this.form.submit()">
                <option value="">Toutes les catégories</option>
                <?php foreach (CATEGORIES as $cat): ?>
                    <option value="<?= h($cat) ?>" <?= $categorie === $cat ? 'selected' : '' ?>>
                        <?= h($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="q" placeholder="Rechercher…" value="<?= h($search) ?>">
            <button type="submit" class="btn">Filtrer</button>
            <?php if ($categorie || $search): ?>
                <a href="/mini-projet/resources.php" class="btn btn-muted">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($ressources)): ?>
            <div class="alert alert-info">Aucune ressource ne correspond à votre recherche.</div>
        <?php else: ?>
            <?php foreach ($grouped as $cat => $items):
                // Masquer les catégories vides quand on filtre par catégorie ou mot-clé
                if (empty($items) && ($categorie !== '' || $search !== '')) {
                    continue;
                }
                // Sans filtre, masquer les catégories sans ressources
                if (empty($items)) {
                    continue;
                }
                $open = ($categorie === $cat || $search !== '') ? 'open' : '';
            ?>
                <details class="accordion" <?= $open ?>>
                    <summary>
                        <?= h($cat) ?>
                        <span class="text-muted text-sm">(<?= count($items) ?>)</span>
                        <span class="chevron" aria-hidden="true">▾</span>
                    </summary>
                    <div class="accordion-body">
                        <?php if (empty($items)): ?>
                            <div class="accordion-empty">Aucune ressource dans cette catégorie.</div>
                        <?php else: ?>
                            <?php foreach ($items as $r): ?>
                                <div class="resource-item">
                                    <div>
                                        <h3><a href="/mini-projet/resource-detail.php?id=<?= $r['id'] ?>"><?= h($r['titre']) ?></a></h3>
                                        <div class="resource-meta">
                                            <?= h($r['prenom']) ?> <?= h($r['nom']) ?>
                                            &nbsp;&mdash;&nbsp;<?= date('d/m/Y', strtotime($r['date_depot'])) ?>
                                        </div>
                                        <?php if ($r['description']): ?>
                                            <p class="text-sm text-muted" style="margin-top:.3rem"><?= h(mb_substr($r['description'], 0, 120)) ?><?= mb_strlen($r['description']) > 120 ? '…' : '' ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="resource-actions">
                                        <a href="/mini-projet/resource-detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Voir</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        <?php endif; ?>

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
