<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT * FROM ressources WHERE id = ?');
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r || ($r['auteur_id'] !== $_SESSION['user_id'] && !isAdmin())) {
    header('Location: /mini-projet/resources.php');
    exit;
}

$errors = [];
$data   = [
    'titre'       => $r['titre'],
    'description' => $r['description'] ?? '',
    'contenu'     => $r['contenu'],
    'categorie'   => $r['categorie'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['titre']       = trim($_POST['titre']       ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['contenu']     = trim($_POST['contenu']     ?? '');
    $data['categorie']   = trim($_POST['categorie']   ?? '');

    if ($data['titre'] === '')                           { $errors[] = 'Le titre est requis.'; }
    if ($data['contenu'] === '')                         { $errors[] = 'Le contenu est requis.'; }
    if (!in_array($data['categorie'], CATEGORIES, true)) { $errors[] = 'Catégorie invalide.'; }

    $newImage = null;
    $removeImage = isset($_POST['remove_image']);

    if (empty($errors) && !$removeImage) {
        try {
            $newImage = handleImageUpload('image');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        // Déterminer le nom de fichier final à stocker
        if ($removeImage) {
            deleteImageFile($r['image']);
            $finalImage = null;
        } elseif ($newImage !== null) {
            deleteImageFile($r['image']); // remplace l'ancienne
            $finalImage = $newImage;
        } else {
            $finalImage = $r['image']; // inchangée
        }

        $upd = $pdo->prepare(
            'UPDATE ressources SET titre = ?, description = ?, contenu = ?, categorie = ?, image = ?
             WHERE id = ?'
        );
        $upd->execute([
            $data['titre'],
            $data['description'],
            $data['contenu'],
            $data['categorie'],
            $finalImage,
            $id,
        ]);
        header('Location: /mini-projet/resource-detail.php?id=' . $id . '&updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier la ressource — EduLib</title>
    <link rel="stylesheet" href="/mini-projet/assets/style.css">
</head>
<body>

<nav>
    <div class="container nav-inner">
        <a href="/mini-projet/" class="nav-brand">EduLib</a>
        <div class="nav-links">
            <a href="/mini-projet/">Accueil</a>
            <a href="/mini-projet/resources.php">Ressources</a>
            <a href="/mini-projet/profile.php">Mon profil</a>
            <?php if (isAdmin()): ?>
                <a href="/mini-projet/admin/">Administration</a>
            <?php endif; ?>
            <a href="/mini-projet/logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main>
    <div class="container" style="max-width:700px">
        <div class="page-header">
            <h1>Modifier la ressource</h1>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= h($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre <span style="color:var(--danger)">*</span></label>
                <input type="text" id="titre" name="titre"
                       value="<?= h($data['titre']) ?>" required maxlength="200">
            </div>
            <div class="form-group">
                <label for="categorie">Catégorie <span style="color:var(--danger)">*</span></label>
                <select id="categorie" name="categorie" required>
                    <option value="">— Choisir une catégorie —</option>
                    <?php foreach (CATEGORIES as $cat): ?>
                        <option value="<?= h($cat) ?>" <?= $data['categorie'] === $cat ? 'selected' : '' ?>>
                            <?= h($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description courte <span class="text-muted text-sm">(optionnelle)</span></label>
                <input type="text" id="description" name="description"
                       value="<?= h($data['description']) ?>" maxlength="300">
            </div>
            <div class="form-group">
                <label for="contenu">Contenu <span style="color:var(--danger)">*</span></label>
                <textarea id="contenu" name="contenu" required
                          style="min-height:220px"><?= h($data['contenu']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image illustrative</label>
                <?php if ($r['image']): ?>
                    <div style="margin-bottom:.5rem">
                        <img src="<?= UPLOAD_URL . h($r['image']) ?>" alt="Illustration actuelle"
                             style="max-width:220px;max-height:160px;border-radius:4px;border:1px solid var(--border)">
                        <div class="mt-1">
                            <label style="font-weight:normal;display:inline-flex;align-items:center;gap:.4rem;cursor:pointer">
                                <input type="checkbox" name="remove_image" value="1">
                                Supprimer cette image
                            </label>
                        </div>
                    </div>
                    <div class="text-sm text-muted mb-1">Ou remplacer par une nouvelle image :</div>
                <?php else: ?>
                    <div class="text-sm text-muted mb-1">Aucune image — JPG, PNG, WebP, GIF, max 2 Mo</div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn">Enregistrer</button>
                <a href="/mini-projet/resource-detail.php?id=<?= $id ?>" class="btn btn-muted">Annuler</a>
            </div>
        </form>
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
