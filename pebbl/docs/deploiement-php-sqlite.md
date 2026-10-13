# Déploiement de Pebbl sur un hébergement mutualisé (o2switch / cPanel)

Note courte pour mettre en ligne le mini-projet Pebbl (site vitrine + liste d'attente + administration) sur un hébergement mutualisé avec **Apache**, **PHP 8+** et **PDO SQLite**.

## 1. Prérequis côté hébergement

- **Version PHP 8.1 minimum** (le code utilise le type de retour `never`) : à choisir dans cPanel > *Sélecteur de version PHP* (ou *MultiPHP Manager*).
- **Extensions activées** : `pdo_sqlite`, `sqlite3`, `mbstring` (validation des champs), `json`. Elles sont actives par défaut chez o2switch ; à vérifier dans le même écran.
- **`.htaccess` pris en compte** par Apache (`AllowOverride`) : c'est le cas sur cPanel.
- **HTTPS activé** (certificat AutoSSL / Let's Encrypt) : le cookie de session admin passe en `secure` automatiquement dès que le site est servi en HTTPS.

## 2. Où uploader les fichiers

Uploader le **contenu** du dossier `pebbl/` directement à la **racine web du domaine** :

- domaine principal : `~/public_html/`
- domaine additionnel ou sous-domaine : le dossier racine indiqué dans cPanel > *Domaines* (ex. `~/pebbl.example.fr/`).

Résultat attendu sur le serveur :

```
public_html/
├── index.html
├── waitinglist.html
├── img/
├── backend/
├── admin/
└── data/
    └── .htaccess
```

**Pourquoi la racine et pas un sous-dossier ?** Deux chemins sont écrits en absolu dans le code :

- `waitinglist.html` envoie le formulaire vers `/backend/subscribe.php` ;
- le cookie de session admin est limité au chemin `/admin/` (`admin/auth.php`).

Dans un sous-dossier (`/pebbl/`), le formulaire renverrait une 404 et la connexion admin ne tiendrait pas. Si un sous-dossier est imposé, il faut adapter ces deux chemins.

Upload via le *Gestionnaire de fichiers* de cPanel (archive `.zip` puis *Extraire*) ou en FTP/SFTP.

## 3. Ce qu'il faut uploader

| Élément | Rôle |
|---|---|
| `index.html` | Page d'accueil / présentation de Pebbl |
| `waitinglist.html` | Formulaire d'inscription à la liste d'attente |
| `img/` | Visuels utilisés par les deux pages |
| `backend/` (`db.php`, `subscribe.php`) | Connexion SQLite et endpoint d'inscription |
| `admin/` (PHP + `assets/admin.css`) | Espace d'administration |
| `data/.htaccess` **uniquement** | Bloque tout accès web au dossier `data/` |

Le dossier `data/` doit exister sur le serveur et être **inscriptible par PHP** (droits `755` suffisent chez o2switch, PHP tournant sous l'utilisateur du compte). SQLite y crée la base **et** des fichiers temporaires (`-journal`), d'où le besoin d'écrire dans le dossier, pas seulement dans le fichier.

La base `data/waitlist.sqlite` est **créée automatiquement** au premier appel (`backend/db.php` exécute `CREATE TABLE IF NOT EXISTS`) : rien à importer.

## 4. Ce qui doit rester en local

| Élément | Pourquoi |
|---|---|
| `data/waitlist.sqlite` (et `*-journal`, `*-wal`, `*-shm`) | Contient des **données personnelles** de test (noms, e-mails). La prod démarre avec une base vierge ; uploader la base locale écraserait aussi les vraies inscriptions lors d'un redéploiement. |
| `docs/` | Documentation interne (brand book, audit, schémas) : inutile au site et non destinée au public. |
| `CLAUDE.md`, `.claude/`, `.agents/`, `skills-lock.json` | Configuration de l'assistant de développement. |
| `.mcp.json`, `.playwright-mcp/` | Outils et traces de tests navigateur, purement locaux. |
| `.git/`, `.gitignore` | Historique et config Git : exposer `.git/` permettrait de télécharger tout le code et son historique. |
| `.env`, `*.local.php` (s'ils existent) | Secrets locaux, déjà exclus par `.gitignore`. |

Règle simple : **on n'envoie que ce que le navigateur ou PHP utilise réellement.** Tout le reste augmente la surface d'exposition sans rien apporter.

## 5. Sécurité à vérifier avant la mise en ligne

1. **Changer les identifiants admin** dans `admin/config.php` : ceux du dépôt sont des identifiants de démonstration. Générer un nouveau hash, par exemple en local :
   ```bash
   php -r "echo password_hash('NouveauMotDePasseSolide', PASSWORD_DEFAULT), PHP_EOL;"
   ```
   puis remplacer `ADMIN_PASSWORD_HASH` (et éventuellement `ADMIN_USERNAME`).
2. **Tester la protection de la base** : `https://votre-domaine/data/waitlist.sqlite` doit renvoyer **403 Forbidden**. Si le fichier se télécharge, le `.htaccess` n'est pas appliqué : corriger avant d'ouvrir le site.
3. **Tester l'inscription** depuis `waitinglist.html`, puis vérifier qu'elle apparaît dans l'admin.

> Option plus robuste : placer `data/` **hors** de `public_html` (ex. `~/pebbl-data/`) et adapter `$dbPath` dans `backend/db.php`. La base n'est alors plus du tout atteignable par URL, même si le `.htaccess` venait à sauter.

## 6. Accéder à l'administration

- URL : `https://votre-domaine/admin/login.php`
- Connexion avec l'identifiant et le mot de passe définis dans `admin/config.php` (mot de passe vérifié via `password_verify`, jamais stocké en clair).
- Une fois connecté, `admin/dashboard.php` affiche :
  - le nombre total d'inscrits et la répartition par usage (Concentration, Zen, Énergie, Autre) ;
  - la liste paginée des inscrits (50 par page) ;
  - un bouton **Exporter en CSV** (`export.php`, séparateur `;`, compatible Excel) ;
  - un bouton **Supprimer** par inscrit (protégé par jeton CSRF, avec confirmation).
- **Se déconnecter** (`logout.php`) détruit la session. Toutes les pages admin redirigent vers `login.php` sans session valide.
