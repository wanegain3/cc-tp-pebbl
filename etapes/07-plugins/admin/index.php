<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/../includes/db.php';

$usage_values  = ['concentration', 'meditation', 'energie', 'transitions', 'ambiance', 'autre'];
$filter_usage  = in_array($_GET['usage']   ?? '', $usage_values, true) ? $_GET['usage']   : '';
$filter_period = in_array($_GET['periode'] ?? '', ['7', '30'],    true) ? $_GET['periode'] : '';

$pdo = get_db();

$total     = (int) $pdo->query('SELECT COUNT(*) FROM waitlist')->fetchColumn();
$last7     = (int) $pdo->query("SELECT COUNT(*) FROM waitlist WHERE created_at >= datetime('now','-7 days')")->fetchColumn();
$last30    = (int) $pdo->query("SELECT COUNT(*) FROM waitlist WHERE created_at >= datetime('now','-30 days')")->fetchColumn();
$last_date = $pdo->query('SELECT MAX(created_at) FROM waitlist')->fetchColumn();

$usage_stats = $pdo->query(
    'SELECT usage_principal, COUNT(*) c FROM waitlist GROUP BY usage_principal ORDER BY c DESC'
)->fetchAll();

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

function h(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pebbl — Admin liste d'attente</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --ink: #181714; --muted: #6b6762; --accent: #c8a96e;
      --off: #f5f4f1; --stone: #e4e1da; --white: #fff;
      --error: #b84f3c; --success: #4a7c59;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
      background: var(--off); color: var(--ink); line-height: 1.5;
      padding: clamp(16px, 4vw, 40px);
    }
    .page-header {
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
    }
    h1 { font-size: clamp(1.1rem, 3vw, 1.5rem); font-weight: 300; letter-spacing: -0.02em; }
    .rgpd {
      background: rgba(184,79,60,.07); border: 1px solid rgba(184,79,60,.2);
      border-radius: 8px; padding: 10px 14px; font-size: 0.8rem;
      color: var(--error); margin-bottom: 28px;
    }
    .stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
             gap: 12px; margin-bottom: 32px; }
    .stat {
      background: var(--white); border: 1px solid var(--stone);
      border-radius: 12px; padding: 16px;
    }
    .stat strong { display: block; font-size: 1.5rem; font-weight: 600; letter-spacing: -0.02em; }
    .stat span   { font-size: 0.78rem; color: var(--muted); }
    form.filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; }
    form.filters label { font-size: 0.82rem; font-weight: 600; display: flex; flex-direction: column; gap: 4px; }
    select {
      padding: 8px 12px; border-radius: 8px; border: 1.5px solid var(--stone);
      font-size: 0.88rem; font-family: inherit; background: var(--white);
    }
    .btn {
      display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
      border-radius: 8px; font-size: 0.88rem; font-weight: 600; font-family: inherit;
      cursor: pointer; text-decoration: none; border: none; transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.82; }
    .btn-dark    { background: var(--ink); color: var(--white); }
    .btn-outline { background: var(--white); color: var(--ink); border: 1.5px solid var(--stone); }
    .btn-logout  { background: transparent; color: var(--muted); border: 1.5px solid var(--stone); font-size: 0.82rem; }
    .toolbar {
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 12px; margin-bottom: 14px;
    }
    .count { font-size: 0.85rem; color: var(--muted); }
    table {
      width: 100%; border-collapse: collapse; background: var(--white);
      border: 1px solid var(--stone); border-radius: 12px; overflow: hidden; font-size: 0.86rem;
    }
    th {
      background: var(--off); padding: 10px 14px; text-align: left;
      font-size: 0.74rem; font-weight: 600; letter-spacing: 0.06em;
      text-transform: uppercase; color: var(--muted);
    }
    td { padding: 10px 14px; border-top: 1px solid var(--stone); }
    tr:hover td { background: var(--off); }
    .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.74rem; font-weight: 600; }
    .badge-y { background: rgba(74,124,89,.12); color: var(--success); }
    .badge-n { background: rgba(184,79,60,.10); color: var(--error); }
    .empty { text-align: center; color: var(--muted); padding: 32px; }
  </style>
</head>
<body>

  <div class="page-header">
    <h1>Liste d'attente — Pebbl</h1>
    <a href="logout.php" class="btn btn-logout">Déconnexion</a>
  </div>

  <p class="rgpd">
    ⚠ Données personnelles — accès restreint. Conservation : 12 mois maximum.
    Suppression sur demande : contact@tech-me-up.com.
  </p>

  <!-- Statistiques -->
  <div class="stats">
    <div class="stat"><strong><?= $total ?></strong><span>Inscrits au total</span></div>
    <div class="stat"><strong><?= $last7 ?></strong><span>7 derniers jours</span></div>
    <div class="stat"><strong><?= $last30 ?></strong><span>30 derniers jours</span></div>
    <div class="stat">
      <strong><?= $last_date ? h(date('d/m/Y', strtotime($last_date))) : '—' ?></strong>
      <span>Dernière inscription</span>
    </div>
    <?php foreach ($usage_stats as $s): ?>
    <div class="stat">
      <strong><?= (int)$s['c'] ?></strong>
      <span><?= h($s['usage_principal']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Filtres -->
  <form method="get" class="filters">
    <label>Usage
      <select name="usage">
        <option value="">Tous</option>
        <?php foreach ($usage_values as $u): ?>
        <option value="<?= h($u) ?>" <?= $filter_usage === $u ? 'selected' : '' ?>>
          <?= h(ucfirst($u)) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Période
      <select name="periode">
        <option value="">Toute période</option>
        <option value="7"  <?= $filter_period === '7'  ? 'selected' : '' ?>>7 jours</option>
        <option value="30" <?= $filter_period === '30' ? 'selected' : '' ?>>30 jours</option>
      </select>
    </label>
    <button type="submit" class="btn btn-dark">Filtrer</button>
    <a href="index.php" class="btn btn-outline">Tout afficher</a>
  </form>

  <!-- Tableau -->
  <div class="toolbar">
    <span class="count">
      <?= count($rows) ?> inscrit<?= count($rows) !== 1 ? 's' : '' ?>
    </span>
    <a href="export.php?usage=<?= urlencode($filter_usage) ?>&periode=<?= urlencode($filter_period) ?>"
       class="btn btn-dark">Exporter CSV</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Prénom</th>
        <th>Nom</th>
        <th>E-mail</th>
        <th>Usage</th>
        <th>Consent.</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
      <tr><td colspan="7" class="empty">Aucun inscrit pour ce filtre.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= h($r['prenom']) ?></td>
        <td><?= h($r['nom']) ?></td>
        <td><?= h($r['email']) ?></td>
        <td><?= h($r['usage_principal']) ?></td>
        <td>
          <?php if ((int)$r['consentement']): ?>
            <span class="badge badge-y">Oui</span>
          <?php else: ?>
            <span class="badge badge-n">Non</span>
          <?php endif; ?>
        </td>
        <td><?= h(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</body>
</html>
