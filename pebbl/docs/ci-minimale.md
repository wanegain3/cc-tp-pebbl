# CI minimale : checklist GitHub Actions

Date : 20 octobre 2026. Statut : checklist à valider, **aucun workflow créé**.

Objectif : un seul job GitHub Actions, rapide (moins de 2 minutes), sans dépendance à installer, qui bloque une fusion si le back-end ne compile pas, si un secret ou une donnée locale est versionné, si les pages HTML sont cassées ou si le flux d'inscription ne fonctionne plus.

## 0. Constat sur le projet actuel

| Élément | État | Conséquence pour la CI |
|---|---|---|
| Stack | PHP 8.2 + SQLite (PDO), aucun Composer ni npm | `php -l`, `php -S` et `curl` suffisent |
| Serveur | `php -S localhost:8000 router.php` depuis `pebbl/`, routeur en liste blanche | Le routeur se teste en boîte noire avec `curl` |
| Base | Chemin remplaçable par `PEBBL_DB_PATH` (`includes/config.php`), schéma créé au premier appel | Base jetable dans `$RUNNER_TEMP`, aucune fixture |
| Secrets | `config/config.local.php` (hash admin) ignoré ; seul `config.local.example.php` (hash vide) est prévu pour le dépôt | Vérifier que seul le modèle est versionné et qu'il reste vide |
| Données locales | `data/pebbl.sqlite`, `.claude/settings.local.json`, `.playwright-mcp/` (captures du formulaire) présents sur le poste, tous couverts par `.gitignore` | Contrôler l'index Git, pas seulement le `.gitignore` |
| Front | `index.html`, `waitinglist.html` (JS inline, `fetch` vers `api/inscription.php`), images `img/*.png` et `*.webp` | Vérifications HTML simples, sans navigateur |
| Dépôt Git | Racine = `cc-tp-pebbl/` (parent), pas de dossier `.github/` | Workflow à la racine, `working-directory: pebbl` |

> **Point bloquant** : à ce jour, tout le back-end (`api/`, `admin/`, `includes/`, `config/`, `bin/`, `router.php`), les deux pages HTML et le `.gitignore` sont **non suivis** par Git. Une CI ne vérifierait rien tant qu'ils ne sont pas commités.

## 1. Mise en place

- [ ] Commiter le code de `pebbl/` (après le contrôle du point 3 en local : `git status --ignored pebbl/`).
- [ ] Créer `.github/workflows/pebbl-ci.yml` **à la racine du dépôt** (pas dans `pebbl/`).
- [ ] Déclencheurs : `push` et `pull_request`, filtrés sur `paths: ['pebbl/**', '.github/workflows/pebbl-ci.yml']`.
- [ ] Un seul job sur `ubuntu-latest`, `defaults.run.working-directory: pebbl`.
- [ ] PHP **8.2** (même version que le poste XAMPP) via `shivammathur/setup-php@v2`, extensions `pdo_sqlite, mbstring`. Le code utilise le type `never` : PHP 8.1 minimum.
- [ ] `permissions: contents: read` (le job n'écrit rien).
- [ ] Ne jamais déclarer de secret GitHub : la CI n'en a pas besoin.

## 2. Syntaxe du back-end

- [ ] `php -l` sur tous les fichiers PHP suivis par Git (`api/`, `admin/`, `includes/`, `config/`, `bin/`, `router.php`) ; échec au premier fichier invalide.

  ```bash
  git ls-files -z '*.php' | xargs -0 -n1 php -l
  ```

- [ ] Chaque fichier PHP commence par `declare(strict_types=1);` (convention du projet).

  ```bash
  for f in $(git ls-files '*.php'); do grep -q 'declare(strict_types=1);' "$f" || { echo "strict_types manquant : $f"; exit 1; }; done
  ```

## 3. Secrets et données locales non versionnés

- [ ] Aucun fichier sensible dans l'index Git (le test porte sur `git ls-files`, donc détecte aussi un `git add -f`) :

  ```bash
  if git ls-files | grep -E '(^|/)config/config\.local\.php$|\.(sqlite3?|db)$|-(wal|shm|journal)$|(^|/)\.env|settings\.local\.json$|^\.playwright-mcp/|\.log$|^data/[^.]'; then
    echo "Fichier local ou secret versionné"; exit 1
  fi
  ```

- [ ] Le modèle `config/config.local.example.php` garde un hash vide :

  ```bash
  grep -q "ADMIN_PASSWORD_HASH = '';" config/config.local.example.php
  ```

- [ ] Aucun hash de mot de passe ni clé privée dans les fichiers suivis :

  ```bash
  ! git grep -nE '\$2y\$[0-9]{2}\$|\$argon2id?\$|BEGIN [A-Z ]*PRIVATE KEY' -- . ':!docs/ci-minimale.md'
  ```

- [ ] Les protections Apache sont bien présentes : `data/.htaccess`, `config/.htaccess`, `bin/.htaccess`.
- [ ] Le `.gitignore` contient toujours `/config/config.local.php`, `*.sqlite` et `.playwright-mcp/` (garde-fou contre une suppression accidentelle).

## 4. Vérification HTML simple

Sans navigateur ni dépendance, sur `index.html` et `waitinglist.html` :

- [ ] Structure de base : `<!DOCTYPE html>`, `<html lang="fr">`, `<meta charset`, `<meta name="viewport"`, un `<title>` non vide, un seul `<h1>`.
- [ ] Chaque `<img` a un attribut `alt` (vide accepté pour une image décorative).
- [ ] Chaque image référencée (`src` et `srcset`) existe dans `img/` **et** respecte le motif du routeur `^[a-z0-9_-]+\.(png|webp)$` ; sinon elle sera en 404 une fois servie par `php -S`.

  ```bash
  for f in $(grep -ohE 'img/[^" ,]+' index.html waitinglist.html | sort -u); do
    [ -f "$f" ] || { echo "Image absente : $f"; exit 1; }
    echo "${f#img/}" | grep -qE '^[a-z0-9_-]+\.(png|webp)$' || { echo "Refusée par router.php : $f"; exit 1; }
  done
  ```

- [ ] Les liens internes pointent vers des pages existantes (`waitinglist.html`, ancres `#…` présentes comme `id`).
- [ ] Contraintes de discours (`docs/pebbl.md`) : aucune expression interdite. La liste vise des expressions précises, car « respiration » est employé légitimement dans la page.

  ```bash
  ! grep -inE "détecte vos émotions|analyse votre comportement|surveille|réduit le stress|réduire le stress|améliore(r)? (le|votre) sommeil|corrige(r)? (la|votre) respiration" index.html waitinglist.html
  grep -q "n’est pas un dispositif médical" index.html
  ```

- [ ] *Optionnel, une fois le bruit réglé en local :* validation stricte avec `npx --yes html-validate@<version figée> index.html waitinglist.html` et un `.htmlvalidate.json` minimal. À n'activer qu'après un premier passage local sans erreur, pour ne pas bloquer la CI sur des détails.

## 5. Tests minimaux des fonctionnalités critiques

Préparation : lancer le serveur en arrière-plan sur une base jetable, puis attendre qu'il réponde.

```bash
export PEBBL_DB_PATH="$RUNNER_TEMP/ci.sqlite"
php -S 127.0.0.1:8000 router.php > "$RUNNER_TEMP/php.log" 2>&1 &
for i in $(seq 1 20); do curl -s -o /dev/null http://127.0.0.1:8000/ && break; sleep 0.5; done
```

Helper suggéré : `code() { curl -s --path-as-is -o /dev/null -w '%{http_code}' "$@"; }`, puis `[ "$(code …)" = 200 ]`. L'option `--path-as-is` est indispensable : sans elle, curl résout lui-même `..` et le test de contournement du routeur ne teste rien.

> Tous les codes attendus ci-dessous ont été vérifiés le 20 octobre 2026 sur une copie du projet (sans `config.local.php`, base jetable) : tests 5.1 à 5.5 au vert.

### 5.1 Pages publiques

- [ ] `GET /`, `/index.html`, `/waitinglist.html` → **200**.
- [ ] `GET /img/hero.png` → **200**.

### 5.2 Routeur : rien de sensible n'est servi

- [ ] **404** pour : `/data/ci.sqlite`, `/data/pebbl.sqlite-wal`, `/config/config.local.php`, `/config/config.local.example.php`, `/includes/db.php`, `/bin/hash-password.php`, `/docs/pebbl.md`, `/.gitignore`, `/router.php`.
- [ ] **404** pour les contournements : `/img/../data/ci.sqlite`, `/img/%2e%2e/router.php`, `//index.html`, `/index.html::$DATA`.

### 5.3 Endpoint d'inscription (`api/inscription.php`)

Rester **sous 20 POST** au total : au-delà, la limite par minute renvoie 429 et fausse les tests suivants.

| Cas | Requête | Attendu |
|---|---|---|
| Méthode | `GET` | 405 + en-tête `Allow: POST` |
| Format | `POST` en `application/json` | 415 |
| Origine | `POST` valide avec `Origin: https://evil.example` | 403 |
| Champ tableau | `prenom[]=x` | 400 |
| Inscription valide | `prenom=Ana&email=Ana@Exemple.fr&usage=travail&consentement=on` | 200 `{"ok":true}` |
| Doublon | même e-mail en minuscules | 200 `{"ok":true}` (réponse identique, rien ne révèle le doublon) |
| E-mail invalide | `email=pas-un-mail` | 422, clé `errors.email` |
| Consentement absent | sans `consentement` | 422, clé `errors.consentement` |
| Usage hors liste | `usage=autre` | 422, clé `errors.usage` |
| Champ piège | requête valide + `site_web=x`, autre e-mail | 200 `{"ok":true}` |

- [ ] Après ces appels, la base contient **exactement 1** inscription, e-mail normalisé en minuscules (le doublon et le champ piège n'ont rien ajouté) :

  ```bash
  php -r '$p=new PDO("sqlite:".getenv("PEBBL_DB_PATH")); echo $p->query("SELECT COUNT(*)||\"|\"||email FROM inscriptions")->fetchColumn();'
  # attendu : 1|ana@exemple.fr
  ```

- [ ] Les réponses JSON portent `Content-Type: application/json` et `X-Content-Type-Options: nosniff`.

### 5.4 Administration (sans configuration en CI)

`config/config.local.php` n'existe pas en CI : l'admin doit rester fermée.

- [ ] `GET /admin` → **301** vers `/admin/`.
- [ ] `GET /admin/`, `GET /admin/index.php` → **302** vers `/admin/login.php`.
- [ ] `GET /admin/login.php` → **200** et contient « Administration non configurée ».
- [ ] `POST /admin/export.php` et `POST /admin/delete.php` sans session → **302** vers la connexion, jamais de CSV ni de suppression.

### 5.5 Fin de job

- [ ] Le journal du serveur ne contient aucune erreur PHP :

  ```bash
  ! grep -E 'PHP (Fatal|Parse|Warning|Notice|Deprecated)' "$RUNNER_TEMP/php.log"
  ```

- [ ] En cas d'échec seulement, afficher `php.log` dans la sortie du job (`if: failure()`). Ne pas le publier en artefact.

## 6. Organisation conseillée

Un workflow, un job, quatre étapes nommées (lisibles dans l'onglet Actions) :

1. `Syntaxe PHP` (point 2)
2. `Secrets et données locales` (point 3)
3. `Pages HTML` (point 4)
4. `Tests fonctionnels` (point 5), regroupés dans un script versionné `pebbl/tests/smoke.sh` pour pouvoir le lancer aussi en local depuis Git Bash.

Squelette indicatif :

```yaml
name: Pebbl CI
on:
  push:
    paths: ['pebbl/**', '.github/workflows/pebbl-ci.yml']
  pull_request:
    paths: ['pebbl/**', '.github/workflows/pebbl-ci.yml']
permissions:
  contents: read
jobs:
  verifier:
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
      - name: Syntaxe PHP
        run: git ls-files -z '*.php' | xargs -0 -n1 php -l
      - name: Secrets et données locales
        run: bash tests/no-secrets.sh
      - name: Pages HTML
        run: bash tests/html.sh
      - name: Tests fonctionnels
        run: bash tests/smoke.sh
```

## 7. Hors périmètre (volontairement)

- Pas de matrice de versions PHP, de cache, de déploiement ni de badge.
- Pas de navigateur (Playwright) en CI : le responsive et l'accessibilité restent vérifiés en local avec les skills `check-responsive` et `check-accessibility`.
- Pas de PHPUnit ni d'analyse statique (PHPStan) : à envisager seulement si le back-end grossit.
- Pas de test de connexion admin réussie (il faudrait générer un hash et gérer le jeton CSRF) ni de test unitaire de `csv_safe()` : candidats pour une étape suivante.
- Pas de scanner de secrets externe (gitleaks) : les contrôles du point 3 couvrent les fichiers connus du projet.

## 8. Avant d'activer la CI

- [ ] Exécuter les scripts en local depuis Git Bash (`bash tests/smoke.sh`) avec le serveur arrêté et une base jetable.
- [ ] Vérifier qu'une PR de test volontairement cassée (erreur de syntaxe PHP, `config.local.php` ajouté avec `git add -f`) fait bien échouer le job.
