<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /mini-projet/admin/users.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0 || $id === $_SESSION['user_id']) {
    // Ne pas supprimer soi-même
    header('Location: /mini-projet/admin/users.php');
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: /mini-projet/admin/users.php');
    exit;
}

// Les ressources sont supprimées en cascade (ON DELETE CASCADE)
$pdo->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$id]);
header('Location: /mini-projet/admin/users.php?deleted=1');
exit;
