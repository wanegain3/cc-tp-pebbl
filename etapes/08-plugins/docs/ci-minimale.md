# CI minimale — Pebbl (GitHub Actions)

> Stack : PHP 8+ sans framework · HTML/CSS/JS vanilla · SQLite locale non versionnée  
> Objectif : vérifications rapides à chaque `push` ou `pull_request`, sans pipeline complexe.

---

## Ce que couvre cette CI

| Vérification | Outil | Pourquoi |
|---|---|---|
| Syntaxe PHP | `php -l` | Détecte les erreurs de parse avant mise en prod |
| Fichiers SQLite non versionnés | `git ls-files` | La base contient des données réelles (RGPD) |
| Secrets potentiels en dur | `grep` patterns | Mot de passe admin actuellement hardcodé dans `admin/login.php` |
| Structure HTML basique | `htmlhint` (npm) | Balises manquantes, attributs `alt` absents, id dupliqués |
| Présence des fichiers critiques | `test -f` | Vérifie que les fichiers clés n'ont pas été supprimés par erreur |

---

## Workflow GitHub Actions

Copier ce fichier dans `.github/workflows/ci.yml` à la racine du dépôt.

```yaml
name: CI Pebbl

on:
  push:
    branches: ["**"]
  pull_request:
    branches: [main, master]

jobs:
  # ─────────────────────────────────────────────
  # 1. SYNTAXE PHP
  # ─────────────────────────────────────────────
  php-lint:
    name: Syntaxe PHP
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Installer PHP 8.2
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.2"
          tools: none

      - name: Vérifier la syntaxe de chaque fichier PHP
        run: |
          echo "→ Fichiers PHP analysés :"
          find . -name "*.php" -not -path "./.git/*" | sort
          echo ""
          ERRORS=0
          while IFS= read -r file; do
            php -l "$file" || ERRORS=$((ERRORS + 1))
          done < <(find . -name "*.php" -not -path "./.git/*")
          if [ "$ERRORS" -gt 0 ]; then
            echo "❌ $ERRORS fichier(s) PHP avec des erreurs de syntaxe."
            exit 1
          fi
          echo "✅ Tous les fichiers PHP sont syntaxiquement valides."

  # ─────────────────────────────────────────────
  # 2. FICHIERS SQLITE NON VERSIONNÉS
  # ─────────────────────────────────────────────
  no-sqlite:
    name: Pas de SQLite versionné
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Vérifier qu'aucun fichier SQLite n'est suivi par git
        run: |
          SQLITE_FILES=$(git ls-files | grep -E '\.(sqlite|sqlite3|db)$' || true)
          if [ -n "$SQLITE_FILES" ]; then
            echo "❌ Fichiers SQLite détectés dans le dépôt :"
            echo "$SQLITE_FILES"
            echo ""
            echo "Ces fichiers peuvent contenir des données personnelles (RGPD)."
            echo "Ajouter au .gitignore et retirer avec : git rm --cached <fichier>"
            exit 1
          fi
          echo "✅ Aucun fichier SQLite versionné."

  # ─────────────────────────────────────────────
  # 3. DÉTECTION DE SECRETS EN DUR
  # ─────────────────────────────────────────────
  no-secrets:
    name: Pas de secrets en dur
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Rechercher des secrets potentiels dans les fichiers PHP
        run: |
          FOUND=0

          # Mots de passe en clair (hors hash bcrypt)
          if grep -rn --include="*.php" -iE "password\s*=\s*['\"][^'\"]{4,}['\"]" .; then
            echo "⚠️  Mot de passe potentiel en clair détecté ci-dessus."
            FOUND=$((FOUND + 1))
          fi

          # Clés API ou tokens
          if grep -rn --include="*.php" -iE "(api_key|apikey|secret_key|auth_token)\s*=\s*['\"][^'\"]{8,}['\"]" .; then
            echo "⚠️  Clé API ou token potentiel détecté ci-dessus."
            FOUND=$((FOUND + 1))
          fi

          # DSN avec identifiants (ex. mysql://user:pass@host)
          if grep -rn --include="*.php" -iE "mysql://[^:]+:[^@]+@" .; then
            echo "⚠️  DSN avec identifiants détecté ci-dessus."
            FOUND=$((FOUND + 1))
          fi

          if [ "$FOUND" -gt 0 ]; then
            echo ""
            echo "❌ $FOUND pattern(s) suspect(s) trouvés. Vérifier manuellement."
            exit 1
          fi
          echo "✅ Aucun secret en clair détecté."

      # Note : le hash bcrypt dans admin/login.php est ignoré volontairement
      # (hash != mot de passe en clair). À remplacer par une variable d'env en prod.

  # ─────────────────────────────────────────────
  # 4. VALIDATION HTML
  # ─────────────────────────────────────────────
  html-lint:
    name: Validation HTML
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Installer Node.js et htmlhint
        uses: actions/setup-node@v4
        with:
          node-version: "20"

      - name: Installer htmlhint
        run: npm install -g htmlhint

      - name: Créer la configuration htmlhint
        run: |
          cat > .htmlhintrc << 'EOF'
          {
            "doctype-first": true,
            "doctype-html5": true,
            "tag-pair": true,
            "tag-self-close": false,
            "attr-lowercase": true,
            "attr-value-double-quotes": true,
            "id-unique": true,
            "src-not-empty": true,
            "alt-require": true,
            "spec-char-escape": false
          }
          EOF

      - name: Vérifier les fichiers HTML
        run: |
          echo "→ Fichiers HTML analysés :"
          find . -name "*.html" -not -path "./.git/*" | sort
          echo ""
          htmlhint index.html waitinglist.html
          echo "✅ Validation HTML terminée."

  # ─────────────────────────────────────────────
  # 5. PRÉSENCE DES FICHIERS CRITIQUES
  # ─────────────────────────────────────────────
  check-files:
    name: Fichiers critiques présents
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Vérifier la présence des fichiers essentiels
        run: |
          MISSING=0
          FILES=(
            "index.html"
            "waitinglist.html"
            "api/subscribe.php"
            "includes/db.php"
            "admin/login.php"
            "admin/index.php"
            "admin/includes/guard.php"
            "data/.gitkeep"
            ".htaccess"
          )
          for f in "${FILES[@]}"; do
            if [ ! -f "$f" ]; then
              echo "❌ Fichier manquant : $f"
              MISSING=$((MISSING + 1))
            else
              echo "✅ $f"
            fi
          done
          if [ "$MISSING" -gt 0 ]; then
            echo ""
            echo "❌ $MISSING fichier(s) critique(s) absent(s)."
            exit 1
          fi
```

---

## Checklist manuelle avant `git push`

Ces points ne sont pas automatisables simplement — les vérifier à la main.

- [ ] Le fichier `data/waitlist.sqlite` n'apparaît pas dans `git status`
- [ ] Aucune vraie adresse email ou donnée personnelle dans les fichiers versionnés
- [ ] Le hash admin dans `admin/login.php` correspond bien à un mot de passe fort connu
- [ ] Les `.htaccess` sont présents (racine et `data/`) — Apache les ignore si absents

---

## Ce que cette CI ne couvre pas intentionnellement

- **Tests fonctionnels** — nécessitent un serveur PHP + base SQLite en runtime
- **Tests de performance** — hors scope pour un projet de cette taille  
- **Déploiement automatique** — décrit dans `docs/deploiement-php-sqlite.md`, reste manuel (o2switch via FTP/SSH)
- **Vérification des images** — les PNG sont des assets, pas du code

---

## Mise en place rapide

```bash
mkdir -p .github/workflows
# Copier le bloc YAML ci-dessus dans :
# .github/workflows/ci.yml
git add .github/workflows/ci.yml
git commit -m "ci: ajouter workflow GitHub Actions minimal"
git push
```

La CI se déclenche automatiquement à chaque push.
