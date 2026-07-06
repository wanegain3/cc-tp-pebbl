<?php
declare(strict_types=1);

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $path = __DIR__ . '/../data/waitlist.sqlite';

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA busy_timeout=5000');
    $pdo->exec('PRAGMA foreign_keys=ON');

    $pdo->exec('CREATE TABLE IF NOT EXISTS waitlist (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        prenom          TEXT    NOT NULL,
        nom             TEXT    NOT NULL,
        email           TEXT    NOT NULL,
        email_norm      TEXT    NOT NULL,
        usage_principal TEXT    NOT NULL,
        consentement    INTEGER NOT NULL DEFAULT 0,
        created_at      TEXT    NOT NULL DEFAULT (datetime(\'now\'))
    )');

    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_waitlist_email_norm ON waitlist(email_norm)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_waitlist_created_at ON waitlist(created_at)');

    return $pdo;
}
