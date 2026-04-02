<?php
require_once __DIR__ . '/config/db.php';
$_SESSION = [];
session_destroy();
header('Location: /mini-projet/login.php');
exit;
