<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/../includes/db.php';

$usage_values  = ['concentration', 'meditation', 'energie', 'transitions', 'ambiance', 'autre'];
$filter_usage  = in_array($_GET['usage']   ?? '', $usage_values, true) ? $_GET['usage']   : '';
$filter_period = in_array($_GET['periode'] ?? '', ['7', '30'],    true) ? $_GET['periode'] : '';

$pdo = get_db();

$where  = [];
$params = [];

if ($filter_usage !== '') {
    $where[]          = 'usage_principal = :usage';
    $params[':usage'] = $filter_usage;
}
if ($filter_period !== '') {
    $where[]            = "created_at >= datetime('now', :periode)";
    $params[':periode'] = "-{$filter_period} days";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql  = "SELECT id, prenom, nom, email, usage_principal, consentement, created_at
         FROM waitlist {$where_clause} ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$filename = 'pebbl-inscrits-' . date('Ymd-Hi') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel

fputcsv($out, ['ID', 'Prénom', 'Nom', 'E-mail', 'Usage', 'Consentement', 'Date inscription'], ';');

foreach ($rows as $r) {
    $line = [
        (int) $r['id'],
        $r['prenom'],
        $r['nom'],
        $r['email'],
        $r['usage_principal'],
        (int) $r['consentement'] ? 'Oui' : 'Non',
        date('d/m/Y H:i', strtotime($r['created_at'])),
    ];

    // Anti-formula injection : préfixer les cellules commençant par = + - @ \t \r
    $safe = array_map(function ($v) {
        if (is_string($v) && isset($v[0]) && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $v;
        }
        return $v;
    }, $line);

    fputcsv($out, $safe, ';');
}

fclose($out);
