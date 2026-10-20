# Exécution locale de Pebbl

Stack retenue (cf. `docs/decisions-waitlist.md`) : **PHP 8 + SQLite (PDO)**, servi par le serveur intégré de PHP via `router.php`. Aucune dépendance, aucun Composer, aucun build.

## 1. Prérequis

- **PHP 8.1 minimum** (le code utilise le type `never`) ; poste de formation : PHP 8.2.12 de XAMPP (`C:\xampp\php\php.exe`, dans le PATH).
- Extension **`pdo_sqlite`** active.
- Vérification :

  ```bash
  php -v
  php -m | grep -i sqlite      # PowerShell : php -m | Select-String sqlite
  ```

- Pour l'administration : un fichier `config/config.local.php` (voir section 4).

## 2. Démarrage et arrêt

Depuis le dossier `pebbl/` :

```bash
php -S localhost:8000 router.php
```

- Site : <http://localhost:8000/> (landing) et <http://localhost:8000/waitinglist.html> (liste d'attente).
- Administration : <http://localhost:8000/admin/>.
- Les erreurs PHP s'affichent dans la console du serveur, jamais dans le navigateur.
- **Arrêt** : `Ctrl+C` dans le terminal du serveur.

**Toujours passer `router.php`.** Il sert uniquement une liste blanche d'URL (pages HTML, `img/*.png|webp`, `admin/admin.css`, `api/inscription.php`, scripts `admin/`) et renvoie 404 pour tout le reste (`data/`, `config/`, `includes/`, `bin/`, `docs/`, fichiers cachés). Sans routeur, `php -S` exposerait la base : l'API refuse alors de fonctionner (erreur 500 « Serveur lancé sans router.php »).

Ouvrir `index.html` directement dans le navigateur (`file://`) suffit pour la landing, mais le formulaire de liste d'attente a besoin du serveur.

## 3. Stockage des données

- Base : **`data/pebbl.sqlite`**, créée automatiquement au premier appel (dossier et schéma inclus, `includes/db.php`). Mode WAL : des fichiers `pebbl.sqlite-wal` / `-shm` apparaissent à côté.
- Tables : `inscriptions` (prénom, nom facultatif, e-mail unique en minuscules, usage, date de consentement, date d'inscription), `login_attempts` (échecs de connexion admin), `rate_limit` (compteur global par minute).
- Aucune IP ni user-agent stocké. Les inscriptions de plus de **12 mois** sont purgées à chaque nouvelle inscription.
- La base et `config/config.local.php` sont **ignorés par Git** (`.gitignore`). `data/`, `config/` et `bin/` contiennent un `.htaccess` « Require all denied » pour le cas où le site serait servi par Apache.
- Base alternative (tests, démo) : variable d'environnement `PEBBL_DB_PATH`.

  ```bash
  PEBBL_DB_PATH=/chemin/test.sqlite php -S localhost:8000 router.php                # Git Bash
  $env:PEBBL_DB_PATH = "C:\chemin\test.sqlite"; php -S localhost:8000 router.php    # PowerShell
  ```

- Repartir de zéro : arrêter le serveur, puis supprimer `data/pebbl.sqlite` et ses fichiers `-wal` / `-shm`.

## 4. Accès à l'administration

1. Copier `config/config.local.example.php` vers `config/config.local.php` (déjà présent sur ce poste).
2. Générer le hash du mot de passe, saisi sur l'entrée standard (jamais en argument) :

   ```bash
   php bin/hash-password.php                    # Git Bash : saisie masquée
   ```

   ```powershell
   $s = Read-Host -AsSecureString "Mot de passe"
   [System.Net.NetworkCredential]::new('', $s).Password | php bin/hash-password.php
   ```

3. Coller le hash dans `ADMIN_PASSWORD_HASH` ; identifiant dans `ADMIN_USER` (`admin` par défaut).
4. Se connecter sur <http://localhost:8000/admin/>. Le mot de passe de démonstration est communiqué hors dépôt et doit être remplacé avant toute utilisation publique.

Fonctions : statistiques (total, répartition par usage, inscriptions récentes), liste des inscrits, suppression avec confirmation, export CSV (UTF-8, séparateur `;`, cellules neutralisées pour les tableurs).

Sécurité : session de 30 min d'inactivité / 8 h maximum, jeton CSRF sur toutes les actions, blocage après 5 échecs de connexion en 15 min. Hash vide ou fichier absent : la page de connexion affiche « Administration non configurée » (503).

## 5. Vérifications essentielles

Serveur lancé, depuis un second terminal (Git Bash) :

```bash
u=http://localhost:8000
for p in / /waitinglist.html /img/hero-1200.webp /data/pebbl.sqlite /config/config.local.php /includes/db.php /admin/ /api/inscription.php; do
  echo "$(curl -s -o /dev/null -w '%{http_code}' $u$p) $p"
done
```

| URL | Attendu |
|---|---|
| `/`, `/waitinglist.html`, `/img/hero-1200.webp` | 200 |
| `/data/pebbl.sqlite`, `/config/config.local.php`, `/includes/db.php` | **404** |
| `/admin/` sans session | 302 vers `/admin/login.php` |
| `/api/inscription.php` en GET | 405 |

API d'inscription :

```bash
# Inscription valide -> 200 {"ok":true}
curl -s -X POST -d 'prenom=Test&email=test@exemple.fr&usage=travail&consentement=on' $u/api/inscription.php
# Champs invalides -> 422 avec un message par champ
curl -s -w ' %{http_code}\n' -X POST -d 'prenom=&email=x' $u/api/inscription.php
```

Comportements à connaître : un doublon d'e-mail et le champ piège `site_web` rempli renvoient aussi `{"ok":true}` sans rien modifier ; au-delà de 20 envois par minute (tous visiteurs confondus), réponse 429. Pour ne pas polluer la vraie base, lancer ces tests avec `PEBBL_DB_PATH`.

Parcours navigateur :

1. Remplir le formulaire de `waitinglist.html` : message de succès, erreurs affichées sous les champs si invalide.
2. Se connecter à l'admin : l'inscription apparaît dans la liste et les statistiques.
3. Exporter le CSV, puis supprimer l'inscription de test (écran de confirmation).
4. Se déconnecter : `/admin/` redirige de nouveau vers la connexion.

Pour l'affichage, les skills du projet `check-responsive` et `check-accessibility` (MCP Playwright) s'utilisent sur le site servi en local.
