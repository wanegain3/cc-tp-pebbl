# Déploiement Pebbl — hébergement mutualisé (o2switch / cPanel)

> Environnement cible : Apache, PHP 8.1+, extension `pdo_sqlite` activée.
> Aucune base de données MySQL n'est nécessaire : tout repose sur le fichier SQLite `data/waitlist.sqlite`.

---

## 1. Répertoire cible sur le serveur

Sur un hébergement cPanel (o2switch inclus), le dossier public accessible par HTTP est :

```
/home/<votre-compte>/public_html/
```

Tout ce que vous déposez dans ce dossier est servi par Apache. Vous pouvez créer un sous-dossier si le projet n'est pas à la racine du domaine :

```
/home/<votre-compte>/public_html/pebbl/
```

---

## 2. Ce qu'il faut uploader

Uploadez **l'intégralité** des fichiers et dossiers suivants (via le Gestionnaire de fichiers cPanel ou un client FTP/SFTP) :

| Chemin local | Destination sur le serveur | Rôle |
|---|---|---|
| `index.html` | `public_html/` | Landing page principale |
| `waitinglist.html` | `public_html/` | Page formulaire liste d'attente |
| `.htaccess` | `public_html/` | Sécurité Apache (désactive l'affichage des erreurs, bloque l'accès aux `.sqlite`) |
| `img/` | `public_html/img/` | Toutes les images du projet |
| `api/subscribe.php` | `public_html/api/` | Endpoint AJAX d'inscription |
| `includes/db.php` | `public_html/includes/` | Connexion PDO SQLite (crée la table au premier appel) |
| `admin/` (tout le dossier, y compris `admin/.htaccess` et `admin/includes/`) | `public_html/admin/` | Interface d'administration + protection Apache |
| `data/.htaccess` | `public_html/data/` | Bloque tout accès HTTP direct au dossier `data/` |

> **Note :** ne pas uploader `data/.gitkeep` (marqueur git sans utilité sur le serveur) ni `data/waitlist.sqlite`. Le fichier SQLite sera **créé automatiquement** par PHP au premier appel à `includes/db.php` (première inscription ou première visite de l'admin). Il suffit que le dossier `data/` existe sur le serveur.

---

## 3. Ce qui doit rester en local (ne pas uploader)

| Fichier / Dossier | Raison |
|---|---|
| `docs/` | Documentation interne, sans utilité sur le serveur |
| `.claude/` | Fichiers de configuration Claude Code, rien de fonctionnel côté serveur |
| `CLAUDE.md` | Instructions pour l'IA, inutile en production |
| `.gitignore` | Outil de versioning local |
| `.mcp.json` | Configuration MCP locale |
| `data/waitlist.sqlite` | Ne jamais uploader — PHP le crée automatiquement au premier appel. Uploader un fichier existant écraserait les données de production. |

---

## 4. Permissions des dossiers et fichiers

Après l'upload, vérifiez les permissions dans le Gestionnaire de fichiers cPanel :

| Cible | Permission recommandée | Pourquoi |
|---|---|---|
| `data/` (dossier) | `755` | PHP doit pouvoir y écrire (créer et lire le `.sqlite`) |
| `data/waitlist.sqlite` (une fois créé) | `644` | Le processus PHP possède le fichier, lecture seule pour les autres |
| Tous les autres dossiers | `755` | Standard |
| Tous les fichiers `.php`, `.html` | `644` | Lecture seule suffisante |

> Sur o2switch, PHP s'exécute sous votre propre compte Unix (mod_lsapi / CGI), donc `755` sur `data/` suffit — pas besoin de `777`.

---

## 5. Sélectionner PHP 8+ dans cPanel

Dans cPanel, ouvrir **MultiPHP Manager** :
- Cocher le domaine ou sous-domaine concerné.
- Choisir **PHP 8.1** ou supérieur dans le menu déroulant.
- Cliquer sur **Apply**.

Sur o2switch, `pdo_sqlite` est activé par défaut sur toutes les versions PHP 8.x — aucune manipulation supplémentaire n'est nécessaire dans le **PHP Selector**.

---

## 6. Vérifier que PDO SQLite est bien actif

Créez un fichier de test temporaire `phpinfo-test.php` à la racine :

```php
<?php phpinfo();
```

Ouvrez-le dans le navigateur, cherchez **pdo_sqlite** dans la page — il doit apparaître dans la section PDO. **Supprimez ce fichier immédiatement après** le test.

Sur o2switch, `pdo_sqlite` est activé par défaut sur PHP 8.x.

---

## 7. Option sécurité renforcée : base de données hors du webroot

Par défaut, `data/` est dans le webroot et protégé par `.htaccess`. Cette protection peut être contournée si Apache ignore le `.htaccess` (mauvaise configuration du serveur).

Pour éliminer ce risque, déplacer le fichier SQLite **au-dessus** de `public_html/`, inaccessible par HTTP :

1. Sur le serveur, créer `~/pebbl-data/` (hors de `public_html/`).
2. Modifier le chemin dans `includes/db.php` :

```php
// Remplacer :
$path = __DIR__ . '/../data/waitlist.sqlite';

// Par (remplacer COMPTE par votre nom d'utilisateur o2switch) :
$path = '/home/COMPTE/pebbl-data/waitlist.sqlite';
```

3. S'assurer que `~/pebbl-data/` a les permissions `755`.
4. Ne pas uploader le dossier `data/` — il devient inutile.

Avec cette configuration, même une mauvaise configuration Apache ne peut pas exposer la base de données.

---

## 8. Changer le mot de passe administrateur avant la mise en ligne

Le mot de passe par défaut codé dans `admin/login.php` est **`pebbl-demo-2026`** (hash bcrypt).  
Avant de déployer en production, générez un nouveau hash et remplacez la constante `ADMIN_HASH` :

```php
// Dans un script PHP local temporaire :
echo password_hash('votre-nouveau-mot-de-passe', PASSWORD_BCRYPT);
```

Remplacez ensuite la valeur de `ADMIN_HASH` dans `admin/login.php` et uploadez le fichier modifié.

---

## 9. Accéder à l'administration

Une fois le projet en ligne, l'interface d'administration est accessible à :

```
https://votre-domaine.fr/admin/
```

ou, si le projet est dans un sous-dossier :

```
https://votre-domaine.fr/pebbl/admin/
```

Apache redirige automatiquement `/admin/` vers `/admin/index.php`, qui lui-même redirige vers `/admin/login.php` si la session n'est pas ouverte.

**Identifiants par défaut (à changer avant la mise en ligne) :**

| Champ | Valeur |
|---|---|
| Identifiant | `admin` |
| Mot de passe | `pebbl-demo-2026` |

Depuis l'interface admin vous pouvez :
- consulter la liste des inscrits en temps réel,
- exporter les données au format CSV,
- vous déconnecter proprement.

---

## 10. Checklist de mise en ligne

- [ ] Tous les fichiers uploadés (voir section 2)
- [ ] `data/` avec permission `755`
- [ ] `.htaccess` présent à la racine **et** dans `data/`
- [ ] `pdo_sqlite` vérifié via `phpinfo` (fichier de test supprimé ensuite)
- [ ] Mot de passe admin changé dans `admin/login.php`
- [ ] Test d'inscription depuis la landing page → réponse JSON `{"ok":true}`
- [ ] Accès direct à `/data/waitlist.sqlite` retourne une **erreur 403** (`.htaccess` actif)
- [ ] Test de connexion à `/admin/` et export CSV
- [ ] Fichier `phpinfo-test.php` supprimé du serveur
