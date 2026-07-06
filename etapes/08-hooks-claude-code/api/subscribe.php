<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function json_out(int $code, array $payload): never
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['ok' => false, 'error' => 'server']);
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    json_out(400, ['ok' => false, 'error' => 'server']);
}

// Honeypot anti-bot : le champ "website" doit rester vide
if (!empty($data['website'])) {
    json_out(201, ['ok' => true]);
}

$usage_allowed = ['concentration', 'meditation', 'energie', 'transitions', 'ambiance', 'autre'];

$prenom       = trim((string)($data['prenom']       ?? ''));
$nom          = trim((string)($data['nom']          ?? ''));
$email        = trim((string)($data['email']        ?? ''));
$usage        = trim((string)($data['usage']        ?? ''));
$consentement = !empty($data['consentement']);

$errors = [];

if ($prenom === '' || mb_strlen($prenom) > 100) {
    $errors['prenom'] = 'Veuillez saisir votre prénom.';
}
if ($nom === '' || mb_strlen($nom) > 100) {
    $errors['nom'] = 'Veuillez saisir votre nom.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
    $errors['email'] = 'Veuillez saisir une adresse e-mail valide.';
}
if (!in_array($usage, $usage_allowed, true)) {
    $errors['usage'] = 'Veuillez sélectionner votre usage principal.';
}
if (!$consentement) {
    $errors['consentement'] = 'Votre consentement est requis pour continuer.';
}

if ($errors) {
    json_out(422, ['ok' => false, 'errors' => $errors]);
}

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo       = get_db();
    $emailNorm = mb_strtolower($email);

    $stmt = $pdo->prepare(
        'INSERT INTO waitlist (prenom, nom, email, email_norm, usage_principal, consentement)
         VALUES (:prenom, :nom, :email, :email_norm, :usage_principal, :consentement)'
    );
    $stmt->execute([
        ':prenom'          => $prenom,
        ':nom'             => $nom,
        ':email'           => $email,
        ':email_norm'      => $emailNorm,
        ':usage_principal' => $usage,
        ':consentement'    => 1,
    ]);

    json_out(201, ['ok' => true]);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        json_out(409, ['ok' => false, 'error' => 'duplicate']);
    }

    error_log('[pebbl] subscribe: ' . $e->getMessage());
    json_out(500, ['ok' => false, 'error' => 'server']);
}
