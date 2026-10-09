<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
