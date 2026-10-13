# CI minimale — checklist GitHub Actions

Objectif : un seul workflow, un seul job, quelques étapes en `bash` — de quoi attraper les erreurs grossières à chaque push, sans pipeline complexe ni outillage lourd.

## 0. État des lieux du projet

| Élément | Constat | Conséquence pour la CI |
| --- | --- | --- |
| Racine Git | Le dépôt Git est `cc-tp-pebbl/`, le projet est dans le sous-dossier `pebbl/` | Le workflow va dans `cc-tp-pebbl/.github/workflows/`, avec `working-directory: pebbl` |
| PHP | 9 fichiers : `admin/*.php` (7), `backend/db.php`, `backend/subscribe.php` | `subscribe.php` utilise le type de retour `never` → **PHP ≥ 8.1** requis (local : 8.2) |
| Extensions | `pdo_sqlite` (connexion), `mbstring` (`mb_strlen`) | À activer explicitement dans la CI |
| Front | `index.html`, `waitinglist.html` (CSS/JS inline), images dans `img/` | Vérification par `grep`, pas de build |
| SQLite | `data/waitlist.sqlite`, créée à la volée par `getPDO()` | Ne doit jamais être versionnée ; `data/.htaccess` doit l'être |
| `.gitignore` | Ignore `data/*.sqlite*`, `data/*.db*`, `.env`, `*.local.php` | ⚠️ **Pas encore commité** : tant qu'il ne l'est pas, rien ne protège le dépôt |
| Secrets | `admin/config.php` contient un hash bcrypt (pas de mot de passe en clair) | Accepté pour la démo, mais le motif de détection doit l'ignorer |

## 1. Prérequis avant d'activer la CI

- [ ] Commiter `pebbl/.gitignore` (sinon la règle « SQLite non versionnée » n'existe pas côté dépôt).
- [ ] Commiter `pebbl/data/.htaccess`, mais **pas** `pebbl/data/waitlist.sqlite`.
- [ ] Vérifier qu'aucune base n'a déjà été commitée par le passé : `git log --all --oneline -- '*.sqlite' '*.db'` doit être vide.
- [ ] Ne pas versionner les dossiers d'outillage local (`.playwright-mcp/`, éventuellement `.agents/`) — les ajouter au `.gitignore` si besoin.

## 2. Checklist des vérifications

### 2.1 Syntaxe PHP

- [ ] Installer PHP **8.2** (aligné sur la version locale) avec `pdo_sqlite` et `mbstring`.
- [ ] Lancer `php -l` sur **chaque** fichier `.php` ; le job échoue au premier fichier invalide.

### 2.2 Absence de secrets et de fichiers sensibles versionnés

- [ ] Aucun fichier suivi par Git ne correspond à : `*.sqlite`, `*.sqlite3`, `*.db` (et leurs `-journal`, `-wal`, `-shm`), `.env`, `*.local.php`.
- [ ] `git check-ignore` confirme que `data/waitlist.sqlite` est bien ignoré.
- [ ] `data/.htaccess` est bien suivi (protection d'accès web au dossier `data/`).
- [ ] Aucun motif de secret dans `admin/`, `backend/` et les pages HTML : clé privée, clé AWS (`AKIA…`), token GitHub (`ghp_…`), clé `sk-…`, ou affectation littérale de type `password = '…'`, `'api_key' => '…'`, `"token": "…"`.
- [ ] Le hash bcrypt de `admin/config.php` (commence par `$2y$`) n'est **pas** signalé — le motif exclut volontairement les valeurs commençant par `$`.

### 2.3 Vérification HTML simple

Pour `index.html` et `waitinglist.html` :

- [ ] Première ligne = `<!DOCTYPE html>`.
- [ ] `<html lang="fr">` présent.
- [ ] Une balise `<title>` présente.
- [ ] Exactement un `<h1>`.
- [ ] Chaque `<img>` a un attribut `alt`.
- [ ] Chaque `src`/`href` local (hors `#ancre`, `http:`, `mailto:`) pointe vers un fichier existant — couvre les images `img/*.png` et les liens `index.html` ↔ `waitinglist.html`.

### 2.4 Test minimal des fichiers critiques

- [ ] Les fichiers critiques existent : `index.html`, `waitinglist.html`, `backend/db.php`, `backend/subscribe.php`, `admin/auth.php`, `admin/config.php`, `admin/login.php`, `admin/dashboard.php`, `data/.htaccess`.
- [ ] Serveur intégré `php -S` démarré, puis :

| Requête | Attendu | Ce que ça prouve |
| --- | --- | --- |
| `GET /index.html` | `200` | Landing servie |
| `GET /waitinglist.html` | `200` | Page d'inscription servie |
| `GET /admin/login.php` | `200` | Admin s'exécute (config + session) |
| `GET /admin/dashboard.php` | `302` | Garde d'authentification active |
| `GET /backend/subscribe.php` | `405` | POST uniquement |
| `POST` corps non JSON | `400` | Rejet des corps invalides |
| `POST {}` | `422` | Validation serveur des champs |
| `POST` inscription valide | `200` | Écriture SQLite fonctionnelle |
| Même `POST` une 2ᵉ fois | `422` | Doublon d'e-mail refusé |

- [ ] Après les tests, `git status --porcelain` est vide : la base créée par le test est bien ignorée par `.gitignore`.

> Les 5 premiers cas de l'endpoint ne touchent pas la base et peuvent être rejoués en local. Les 2 derniers écrivent dans `data/waitlist.sqlite` : à ne lancer **qu'en CI** (base jetable), jamais sur la base locale réelle.

## 3. Workflow proposé

Fichier à créer : `cc-tp-pebbl/.github/workflows/ci.yml` (racine du dépôt Git, pas dans `pebbl/`).

```yaml
name: CI minimale

on:
  push:
  pull_request:

jobs:
  checks:
    runs-on: ubuntu-latest
    defaults:
      run:
        working-directory: pebbl
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo_sqlite, mbstring
          tools: none

      - name: Syntaxe PHP
        run: find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l

      - name: Fichiers sensibles non versionnés
        run: |
          if git ls-files | grep -E '\.(sqlite3?|db)(-journal|-wal|-shm)?$|(^|/)\.env$|\.local\.php$'; then
            echo "::error::Fichier sensible versionné"; exit 1
          fi
          git check-ignore -q data/waitlist.sqlite || { echo "::error::data/waitlist.sqlite n'est pas ignoré"; exit 1; }
          git ls-files --error-unmatch data/.htaccess > /dev/null

      - name: Recherche de secrets
        run: |
          P='-----BEGIN [A-Z ]*PRIVATE KEY-----|AKIA[0-9A-Z]{16}|gh[pousr]_[A-Za-z0-9]{36}|sk-[A-Za-z0-9]{20,}|(password|passwd|secret|api_key|token)[A-Za-z_]*["'"'"']?[[:space:]]*(=|=>|:)[[:space:]]*["'"'"'][^"'"'"'$]{4,}'
          if grep -rnE --include='*.php' --include='*.js' --include='*.html' --include='*.json' -e "$P" admin backend index.html waitinglist.html; then
            echo "::error::Secret potentiel détecté"; exit 1
          fi

      - name: Vérification HTML
        run: |
          fail=0
          for f in index.html waitinglist.html; do
            head -1 "$f" | grep -qi '<!doctype html>' || { echo "::error file=$f::doctype manquant"; fail=1; }
            grep -q '<html lang="fr"' "$f"            || { echo "::error file=$f::lang=fr manquant"; fail=1; }
            grep -q '<title>' "$f"                    || { echo "::error file=$f::<title> manquant"; fail=1; }
            [ "$(grep -o '<h1' "$f" | wc -l)" -eq 1 ] || { echo "::error file=$f::il faut exactement un <h1>"; fail=1; }
            if grep -oE '<img [^>]*>' "$f" | grep -v 'alt='; then echo "::error file=$f::<img> sans alt"; fail=1; fi
            for p in $(grep -oE '(src|href)="[^"#:]+"' "$f" | sed -E 's/^(src|href)="//; s/"$//' | sort -u); do
              case "$p" in /*) target=".$p";; *) target="$p";; esac
              [ -e "$target" ] || { echo "::error file=$f::lien cassé $p"; fail=1; }
            done
          done
          exit $fail

      - name: Fichiers critiques présents
        run: |
          for f in index.html waitinglist.html backend/db.php backend/subscribe.php \
                   admin/auth.php admin/config.php admin/login.php admin/dashboard.php data/.htaccess; do
            [ -f "$f" ] || { echo "::error::$f manquant"; exit 1; }
          done

      - name: Smoke test HTTP
        run: |
          php -S 127.0.0.1:8000 > /tmp/php-server.log 2>&1 &
          sleep 1
          U=http://127.0.0.1:8000
          check() { # check <code attendu> <libellé> <args curl...>
            want=$1; label=$2; shift 2
            got=$(curl -s -o /dev/null -w '%{http_code}' "$@")
            [ "$got" = "$want" ] && echo "OK   $label ($got)" || { echo "::error::$label : attendu $want, reçu $got"; cat /tmp/php-server.log; exit 1; }
          }
          JSON='Content-Type: application/json'
          OK_BODY='{"first-name":"Ci","last-name":"Test","email":"ci@example.com","usage":"zen","consent":true}'
          check 200 "landing"            $U/index.html
          check 200 "waitlist"           $U/waitinglist.html
          check 200 "admin login"        $U/admin/login.php
          check 302 "dashboard protégé"  $U/admin/dashboard.php
          check 405 "GET refusé"         $U/backend/subscribe.php
          check 400 "JSON invalide"      -X POST -d 'x' $U/backend/subscribe.php
          check 422 "champs vides"       -X POST -H "$JSON" -d '{}' $U/backend/subscribe.php
          check 200 "inscription valide" -X POST -H "$JSON" -d "$OK_BODY" $U/backend/subscribe.php
          check 422 "doublon refusé"     -X POST -H "$JSON" -d "$OK_BODY" $U/backend/subscribe.php

      - name: Base de test bien ignorée
        run: test -z "$(git status --porcelain)" || { git status --porcelain; echo "::error::Fichiers non ignorés après les tests"; exit 1; }
```

## 4. Ce qui a été vérifié localement (PHP 8.2.12)

- `php -l` : aucune erreur sur les 9 fichiers PHP.
- Smoke test : `200` / `200` / `200` / `302 → login.php` / `405` / `400` / `422` obtenus sur les 7 premiers cas (les 2 cas qui écrivent en base n'ont pas été lancés sur la base locale).
- Vérification HTML : 0 erreur (doctype, `lang`, `title`, `h1` unique, `alt`, tous les liens et images locaux résolus).
- Recherche de secrets : aucun motif dans le projet ; le motif détecte bien des faux secrets de test et ignore le hash bcrypt et `random_bytes()`.
- `git check-ignore` : `data/waitlist.sqlite` est ignoré par `pebbl/.gitignore` (règle `data/*.sqlite`).
- Workflow complet : YAML validé, puis les 7 étapes `run` rejouées dans un dépôt Git jetable (copie du projet sans base) → toutes vertes, y compris inscription `200`, doublon `422` et base de test ignorée. Cas négatif contrôlé : une `waitlist.sqlite` ajoutée à l'index fait bien échouer l'étape « Fichiers sensibles non versionnés ».

## 5. Hors périmètre (volontairement)

Pas de PHPUnit, PHPStan, linter CSS/JS, validation W3C complète, test navigateur (Playwright), matrice de versions PHP, cache ni déploiement. À envisager seulement si le projet grossit — par exemple `html-validate` ou `gitleaks` en remplacement des vérifications `grep`.
