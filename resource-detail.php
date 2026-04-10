<?php
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare(
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

// Soumission d'un commentaire
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $contenu = trim($_POST['contenu'] ?? '');
    if ($contenu === '') {
        $commentError = 'Le commentaire ne peut pas être vide.';
    } else {
        $ins = $pdo->prepare('INSERT INTO commentaires (ressource_id, auteur_id, contenu) VALUES (?, ?, ?)');
        $ins->execute([$id, $_SESSION['user_id'], $contenu]);
        header('Location: /mini-projet/resource-detail.php?id=' . $id . '#commentaires');
        exit;
    }
}

// Chargement des commentaires
$cstmt = $pdo->prepare(
    'SELECT c.id, c.contenu, c.date_depot, c.auteur_id,
            u.nom, u.prenom, u.role
     FROM commentaires c
     JOIN utilisateurs u ON u.id = c.auteur_id
     WHERE c.ressource_id = ?
     ORDER BY c.date_depot ASC'
);
$cstmt->execute([$id]);
$commentaires = $cstmt->fetchAll();
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

        <!-- Section commentaires -->
        <div id="commentaires" class="mt-3">
            <h2 style="font-size:1.15rem;margin-bottom:1rem">
                Discussion
                <span class="badge" style="font-size:.8rem;vertical-align:middle"><?= count($commentaires) ?></span>
            </h2>

            <?php if ($commentaires): ?>
                <div class="comment-list">
                    <?php foreach ($commentaires as $c): ?>
                        <?php $isAdmin = $c['role'] === 'admin'; ?>
                        <div class="comment-item <?= $isAdmin ? 'comment-admin' : '' ?>">
                            <div class="comment-header">
                                <span class="comment-author">
                                    <?= h($c['prenom']) ?> <?= h($c['nom']) ?>
                                    <?php if ($isAdmin): ?>
                                        <span class="comment-badge-admin">Admin</span>
                                    <?php endif; ?>
                                </span>
                                <span class="comment-date text-muted text-sm">
                                    <?= date('d/m/Y à H:i', strtotime($c['date_depot'])) ?>
                                </span>
                            </div>
                            <div class="comment-body pre-wrap"><?= h($c['contenu']) ?></div>
                            <?php if (isLoggedIn() && ($c['auteur_id'] === $_SESSION['user_id'] || isAdmin())): ?>
                                <div class="comment-actions">
                                    <form method="post" action="/mini-projet/delete-comment.php">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Supprimer ce commentaire ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-sm">Aucun commentaire pour l'instant. Soyez le premier à réagir !</p>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <div class="mt-2">
                    <h3 style="font-size:1rem;margin-bottom:.6rem">Laisser un commentaire</h3>
                    <?php if ($commentError): ?>
                        <div class="alert alert-error mb-1"><?= h($commentError) ?></div>
                    <?php endif; ?>
                    <form method="post" action="/mini-projet/resource-detail.php?id=<?= $id ?>#commentaires">
                        <div class="form-group">
                            <textarea name="contenu" required
                                      style="min-height:90px"
                                      placeholder="Votre commentaire…"></textarea>
                        </div>
                        <button type="submit" class="btn">Publier</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="text-sm text-muted mt-2">
                    <a href="/mini-projet/login.php">Connectez-vous</a> pour laisser un commentaire.
                </p>
            <?php endif; ?>
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
