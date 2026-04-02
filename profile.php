<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo  = getPDO();
$user = getCurrentUser();

$errors  = [];
$success = '';

// ── Suppression du compte ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$_SESSION['user_id']]);
    $_SESSION = [];
    session_destroy();
    header('Location: /mini-projet/?account_deleted=1');
    exit;
}

// ── Mise à jour du profil ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $mdp    = $_POST['nouveau_mdp']    ?? '';
    $mdp2   = $_POST['nouveau_mdp2']   ?? '';
    $mdpActuel = $_POST['mdp_actuel']  ?? '';

    if ($nom === '')    $errors[] = 'Le nom est requis.';
    if ($prenom === '') $errors[] = 'Le prénom est requis.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';

    // Vérification unicité email (sauf soi-même)
    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? AND id != ?');
        $chk->execute([$email, $_SESSION['user_id']]);
        if ($chk->fetch()) $errors[] = 'Cet email est déjà utilisé par un autre compte.';
    }

    // Changement de mot de passe (optionnel)
    $newHash = null;
    if ($mdp !== '') {
        if (!password_verify($mdpActuel, $user['mot_de_passe'])) {
            $errors[] = 'Mot de passe actuel incorrect.';
        } elseif (strlen($mdp) < 8) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($mdp !== $mdp2) {
            $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
        } else {
            $newHash = password_hash($mdp, PASSWORD_BCRYPT);
        }
    }

    if (empty($errors)) {
        if ($newHash) {
            $pdo->prepare('UPDATE utilisateurs SET nom=?, prenom=?, email=?, mot_de_passe=? WHERE id=?')
                ->execute([$nom, $prenom, $email, $newHash, $_SESSION['user_id']]);
        } else {
            $pdo->prepare('UPDATE utilisateurs SET nom=?, prenom=?, email=? WHERE id=?')
                ->execute([$nom, $prenom, $email, $_SESSION['user_id']]);
        }
        $_SESSION['prenom'] = $prenom;
        $user = getCurrentUser();
        $success = 'Profil mis à jour avec succès.';
    }
}

// Mes ressources
$mesRes = $pdo->prepare('SELECT id, titre, categorie, date_depot FROM ressources WHERE auteur_id = ? ORDER BY date_depot DESC');
$mesRes->execute([$_SESSION['user_id']]);
$ressources = $mesRes->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil — EduLib</title>
    <link rel="stylesheet" href="/mini-projet/assets/style.css">
</head>
<body>

<nav>
    <div class="container nav-inner">
        <a href="/mini-projet/" class="nav-brand">EduLib</a>
        <div class="nav-links">
            <a href="/mini-projet/">Accueil</a>
            <a href="/mini-projet/resources.php">Ressources</a>
            <a href="/mini-projet/profile.php" class="active">Mon profil</a>
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
            <h1>Mon profil</h1>
            <p><?= h($user['prenom']) ?> <?= h($user['nom']) ?> &mdash; inscrit le <?= date('d/m/Y', strtotime($user['date_inscription'])) ?></p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="update_profile" value="1">
            <div style="display:flex;gap:.75rem">
                <div class="form-group" style="flex:1">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= h($user['prenom']) ?>" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= h($user['nom']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= h($user['email']) ?>" required>
            </div>

            <hr style="border:none;border-top:1px solid var(--border);margin:1.25rem 0">
            <p class="text-sm text-muted mb-1">Laisser vide pour ne pas changer le mot de passe.</p>

            <div class="form-group">
                <label for="mdp_actuel">Mot de passe actuel</label>
                <input type="password" id="mdp_actuel" name="mdp_actuel" autocomplete="current-password">
            </div>
            <div style="display:flex;gap:.75rem">
                <div class="form-group" style="flex:1">
                    <label for="nouveau_mdp">Nouveau mot de passe</label>
                    <input type="password" id="nouveau_mdp" name="nouveau_mdp" autocomplete="new-password" minlength="8">
                </div>
                <div class="form-group" style="flex:1">
                    <label for="nouveau_mdp2">Confirmer</label>
                    <input type="password" id="nouveau_mdp2" name="nouveau_mdp2" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn">Enregistrer les modifications</button>
        </form>

        <hr style="border:none;border-top:1px solid var(--border);margin:2rem 0">

        <h2 style="font-size:1.1rem;margin-bottom:.75rem">Mes ressources (<?= count($ressources) ?>)</h2>

        <?php if (empty($ressources)): ?>
            <p class="text-muted text-sm">Vous n'avez pas encore déposé de ressource. <a href="/mini-projet/add-resource.php">En déposer une</a>.</p>
        <?php else: ?>
            <?php foreach ($ressources as $res): ?>
                <div class="resource-item">
                    <div>
                        <h3><a href="/mini-projet/resource-detail.php?id=<?= $res['id'] ?>"><?= h($res['titre']) ?></a></h3>
                        <div class="resource-meta">
                            <span class="badge"><?= h($res['categorie']) ?></span>
                            &nbsp;<?= date('d/m/Y', strtotime($res['date_depot'])) ?>
                        </div>
                    </div>
                    <div class="resource-actions">
                        <a href="/mini-projet/edit-resource.php?id=<?= $res['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
                        <form method="post" action="/mini-projet/delete-resource.php" style="display:inline">
                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Supprimer cette ressource ?')">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <hr style="border:none;border-top:1px solid var(--border);margin:2rem 0">

        <div class="card" style="border-color:#fca5a5">
            <h3 style="font-size:1rem;color:var(--danger);margin-bottom:.5rem">Zone dangereuse</h3>
            <p class="text-sm text-muted mb-2">La suppression de votre compte est définitive. Toutes vos ressources seront également supprimées.</p>
            <form method="post" onsubmit="return confirm('Supprimer définitivement votre compte et toutes vos ressources ?')">
                <input type="hidden" name="delete_account" value="1">
                <button type="submit" class="btn btn-danger btn-sm">Supprimer mon compte</button>
            </form>
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
