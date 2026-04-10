<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'edulib');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

define('CATEGORIES', [
    'Informatique',
    'Mathématiques',
    'Physique',
    'Chimie',
    'Droit',
    'Économie',
    'Langues',
    'Autre',
]);

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /mini-projet/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /mini-projet/');
        exit;
    }
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $stmt = getPDO()->prepare('SELECT * FROM utilisateurs WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/mini-projet/uploads/');
define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024); // 2 Mo
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

/**
 * Traite un fichier uploadé depuis $_FILES[$field].
 * Retourne le nom du fichier enregistré, null si aucun fichier, ou lance une exception.
 */
function handleImageUpload(string $field) {
    if (empty($_FILES[$field]['name'])) return null;

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erreur lors du téléversement du fichier.');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('L\'image ne doit pas dépasser 2 Mo.');
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, UPLOAD_ALLOWED_TYPES, true)) {
        throw new RuntimeException('Format non accepté. Utilisez JPG, PNG, WebP ou GIF.');
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Impossible d\'enregistrer le fichier.');
    }

    return $filename;
}

function deleteImageFile($filename) {
    if (!is_string($filename) || $filename === '') {
        return;
    }

    $path = UPLOAD_DIR . $filename;
    if (file_exists($path)) {
        unlink($path);
    }
}
