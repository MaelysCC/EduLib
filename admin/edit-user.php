<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /mini-projet/admin/users.php');
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = ?');
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
    header('Location: /mini-projet/admin/users.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $role   = $_POST['role'] ?? 'user';

    if ($nom === '')    { $errors[] = 'Le nom est requis.'; }
    if ($prenom === '') { $errors[] = 'Le prénom est requis.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email invalide.'; }
    if (!in_array($role, ['user', 'admin'], true))  { $errors[] = 'Rôle invalide.'; }

    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? AND id != ?');
        $chk->execute([$email, $id]);
        if ($chk->fetch()) {
            $errors[] = 'Cet email est déjà utilisé par un autre compte.';
        }
    }

    if (empty($errors)) {
        $pdo->prepare('UPDATE utilisateurs SET nom=?, prenom=?, email=?, role=? WHERE id=?')
            ->execute([$nom, $prenom, $email, $role, $id]);
        header('Location: /mini-projet/admin/users.php?updated=1');
        exit;
    }

    // Repopuler pour réafficher le formulaire
    $u['nom']    = $nom;
    $u['prenom'] = $prenom;
    $u['email']  = $email;
    $u['role']   = $role;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier l'utilisateur — EduLib</title>
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
    <div class="container" style="max-width:500px">
        <div class="page-header">
            <h1>Modifier l'utilisateur</h1>
            <p><?= h($u['prenom']) ?> <?= h($u['nom']) ?></p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div style="display:flex;gap:.75rem">
                <div class="form-group" style="flex:1">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= h($u['prenom']) ?>" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= h($u['nom']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= h($u['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : '' ?>>Utilisateur</option>
                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                </select>
            </div>
            <div style="display:flex;gap:.75rem">
                <button type="submit" class="btn">Enregistrer</button>
                <a href="/mini-projet/admin/users.php" class="btn btn-muted">Annuler</a>
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
