# CI minimale — GitHub Actions

Checklist pour un workflow simple sur ce projet (PHP sans framework, front vanilla, SQLite locale non versionnée). Objectif : détecter les erreurs évidentes avant merge, sans pipeline complexe.

## 1. Déclenchement

- [ ] Workflow dans `.github/workflows/ci.yml`
- [ ] Déclenché sur `push` et `pull_request` (branche `main`)
- [ ] Une seule job, runner `ubuntu-latest`

## 2. Setup PHP

- [ ] Utiliser `shivammathur/setup-php` avec la version installée en local (PHP 8.2)
- [ ] Pas d'extensions particulières à installer (le projet n'utilise que `pdo_sqlite`, embarquée par défaut)

## 3. Syntaxe PHP (lint)

- [ ] Lancer `php -l` sur tous les fichiers `.php` du dépôt (`admin/`, `api/`, `inc/`, racine)
- [ ] Faire échouer le job si un seul fichier a une erreur de syntaxe

Exemple de commande :
```bash
find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l
```

## 4. Absence de secrets et de fichiers SQLite versionnés

- [ ] Vérifier qu'aucun fichier `*.sqlite`, `*.sqlite-journal`, `*.sqlite-wal`, `*.sqlite-shm`, `*.db` n'est suivi par git (le `.gitignore` les exclut déjà, mais vérifier qu'ils n'ont pas été forcés avec `git add -f`)
- [ ] Vérifier qu'aucun fichier `.env`, `.env.*`, `*.local.php` n'est versionné
- [ ] Vérifier que `data/.htaccess` (protection du dossier `data/`) est bien présent et versionné
- [ ] Optionnel : scan de secrets (ex. `gitleaks` en action GitHub) pour détecter des clés/mots de passe en clair dans le code

Exemple de commande (fichiers interdits suivis par git) :
```bash
git ls-files | grep -E '\.(sqlite|sqlite-journal|sqlite-wal|sqlite-shm|db)$|^\.env|\.local\.php$' && exit 1 || exit 0
```

## 5. Vérification HTML simple

- [ ] Valider `index.html` et `waitinglist.html` avec un linter HTML léger (ex. `html-validate` ou `tidy`)
- [ ] Vérifier a minima : balises fermées, `<!DOCTYPE html>` présent, pas d'attributs dupliqués

Exemple avec `tidy` (déjà disponible sur `ubuntu-latest`) :
```bash
tidy -q -e index.html waitinglist.html
```

## 6. Test minimal des fichiers critiques

- [ ] `inc/db.php` : vérifier que le fichier s'inclut sans erreur fatale (test d'inclusion isolé, sans connexion réelle si possible)
- [ ] `inc/auth.php`, `inc/inscriptions.php` : idem, vérifier l'absence d'erreur au chargement
- [ ] Test fonctionnel minimal : démarrer le serveur PHP intégré (`php -S localhost:8000`) et vérifier que `index.html`, `waitinglist.html` et `api/inscription.php` répondent (code HTTP 200 ou 4xx/5xx attendu selon le cas, pas de crash serveur)

Exemple :
```bash
php -S localhost:8000 &
sleep 1
curl -sf http://localhost:8000/index.html > /dev/null
curl -sf http://localhost:8000/waitinglist.html > /dev/null
```

## 7. Ce qu'on ne fait pas (hors scope volontaire)

- Pas de tests unitaires PHPUnit complets
- Pas de couverture de code
- Pas de déploiement automatique
- Pas de build front (pas de bundler, le JS/CSS est vanilla)
