<?php
declare(strict_types=1);

// Hash de 'pebbl-demo-2026' — ne jamais stocker le mot de passe en clair
const ADMIN_USER = 'admin';
const ADMIN_HASH = '$2y$10$w6wQ0rY7cpLrVd9BvOAAMOh4pBE7FVwAdVQ1rrdkMqW6Uj6wPHQu.';

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

if (!empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['identifiant'] ?? '');
    $pass = $_POST['mot_de_passe']    ?? '';

    if ($user === ADMIN_USER && password_verify($pass, ADMIN_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }

    sleep(1); // ralentit les tentatives de brute-force
    $error = 'Identifiant ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pebbl — Connexion</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --ink: #181714; --muted: #6b6762; --accent: #c8a96e;
      --off: #f5f4f1; --stone: #e4e1da; --white: #fff; --error: #b84f3c;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
      background: var(--off); color: var(--ink);
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh; padding: 24px;
    }
    .card {
      background: var(--white); border: 1px solid var(--stone);
      border-radius: 20px; padding: 40px; width: 100%; max-width: 380px;
    }
    .brand { font-size: 1.4rem; font-weight: 300; letter-spacing: -0.02em; margin-bottom: 32px; }
    .brand span {
      display: block; font-size: 0.75rem; font-weight: 600;
      letter-spacing: 0.18em; text-transform: uppercase; color: var(--muted); margin-top: 4px;
    }
    .field { margin-bottom: 20px; }
    label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; }
    input[type="text"], input[type="password"] {
      width: 100%; padding: 12px 16px; font-size: 0.92rem; font-family: inherit;
      background: var(--off); border: 1.5px solid var(--stone); border-radius: 10px;
      outline: none; transition: border-color 0.2s;
    }
    input:focus { border-color: var(--accent); }
    .error-msg {
      background: rgba(184,79,60,.07); border: 1px solid rgba(184,79,60,.2);
      border-radius: 8px; padding: 10px 14px; font-size: 0.85rem;
      color: var(--error); margin-bottom: 20px;
    }
    button {
      width: 100%; padding: 14px; background: var(--ink); color: #fff;
      border: none; border-radius: 40px; font-size: 0.92rem; font-weight: 600;
      cursor: pointer; margin-top: 4px; transition: opacity 0.2s;
    }
    button:hover { opacity: 0.85; }
  </style>
</head>
<body>
  <div class="card">
    <div class="brand">Pebbl <span>Administration</span></div>

    <?php if ($error): ?>
    <div class="error-msg" role="alert">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="field">
        <label for="identifiant">Identifiant</label>
        <input type="text" id="identifiant" name="identifiant"
               autocomplete="username" required autofocus>
      </div>
      <div class="field">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe"
               autocomplete="current-password" required>
      </div>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</body>
</html>
