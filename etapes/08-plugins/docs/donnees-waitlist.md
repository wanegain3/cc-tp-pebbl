# Données collectées — Waitlist Pebbl

> Document interne · Référence technique et RGPD · Mise à jour : juillet 2026

---

## 1. Champs stockés

La base de données SQLite (`data/waitlist.sqlite`) contient une seule table `waitlist`.

| Colonne | Type SQL | Source | Contrainte |
|---|---|---|---|
| `id` | INTEGER | Auto-incrémenté | Clé primaire |
| `prenom` | TEXT | Formulaire | NOT NULL, max 100 caractères |
| `nom` | TEXT | Formulaire | NOT NULL, max 100 caractères |
| `email` | TEXT | Formulaire | NOT NULL, max 254 caractères |
| `email_norm` | TEXT | Généré côté serveur | NOT NULL, unique, minuscules (déduplication) |
| `usage_principal` | TEXT | Formulaire (select) | NOT NULL, valeurs fixes |
| `consentement` | INTEGER | Formulaire (checkbox) | NOT NULL, 1 = oui (toujours 1 en base) |
| `created_at` | TEXT | Généré côté serveur | NOT NULL, `datetime('now')` UTC |

**Valeurs autorisées pour `usage_principal` :** `concentration`, `meditation`, `energie`, `transitions`, `ambiance`, `autre`.

**Données non collectées :** adresse postale, téléphone, date de naissance, données de paiement, données de navigation, adresse IP.

---

## 2. Finalité

Les données sont collectées pour deux finalités déclarées à l'utilisateur :

1. **Informer du lancement** — envoyer un e-mail aux inscrits à l'ouverture des commandes Pebbl, avec le tarif Early Bird réservé.
2. **Segmentation d'usage** — comprendre les usages envisagés par la communauté (`usage_principal`) pour affiner le positionnement produit et la communication.

Aucun profilage comportemental, aucun enrichissement tiers, aucune revente de données n'est prévu ni autorisé.

---

## 3. Consentement

Le consentement est **explicite et actif** : l'utilisateur coche une case non pré-cochée.

Texte affiché à l'utilisateur (source : `waitinglist.html`) :

> « J'accepte d'être recontacté par Pebbl pour recevoir des informations sur le lancement du produit et les offres Early Bird. Aucun spam, aucune revente de données. »

Comportement technique :

- La checkbox `consentement` est validée côté client (JavaScript) puis côté serveur (`api/subscribe.php`).
- Si `consentement` est absent ou `false`, l'API retourne HTTP 422 et refuse l'enregistrement.
- En base, le champ vaut toujours `1` — aucune ligne sans consentement ne peut exister.
- L'enregistrement constitue la preuve de consentement ; l'horodatage `created_at` en est le justificatif.

---

## 4. Conservation

**Durée déclarée : 12 mois maximum.**

Cette durée est affichée à deux endroits :
- Dans le formulaire public (`waitinglist.html`) : « conservées 12 mois maximum ».
- Dans l'interface admin (`admin/index.php`) : bandeau d'avertissement RGPD.

**Limitation actuelle :** aucun mécanisme de suppression automatique n'est implémenté dans le code. La purge doit être effectuée manuellement. Voir la section [Risques à éviter](#8-risques-à-éviter).

---

## 5. Accès admin

L'accès aux données est protégé par une interface d'administration PHP (`admin/`).

### Authentification

- Fichier : `admin/login.php` + `admin/includes/guard.php`
- Identifiant unique : `admin`
- Mot de passe stocké sous forme de hash **bcrypt** (jamais en clair)
- Délai de 1 seconde après chaque échec (protection brute-force)
- Session régénérée à la connexion (`session_regenerate_id(true)`)

### Cookie de session

| Attribut | Valeur |
|---|---|
| `httponly` | `true` (inaccessible au JavaScript) |
| `samesite` | `Strict` (protection CSRF) |
| `secure` | `true` uniquement si HTTPS détecté |

### Ce que l'admin peut faire

- Voir la liste complète des inscrits (prénom, nom, e-mail, usage, consentement, date)
- Filtrer par usage ou par période (7 / 30 derniers jours)
- Consulter des statistiques agrégées (total, inscrits récents, répartition par usage)
- Exporter les données en CSV (voir section 6)
- Se déconnecter (`admin/logout.php`)

---

## 6. Export CSV

L'export est généré dynamiquement par `admin/export.php`, accessible uniquement après authentification.

### Colonnes exportées

`ID ; Prénom ; Nom ; E-mail ; Usage ; Consentement ; Date inscription`

### Format

- Séparateur `;` (compatible Excel français)
- Encodage UTF-8 avec BOM (`\xEF\xBB\xBF`) pour l'affichage correct dans Excel
- Nom de fichier horodaté : `pebbl-inscrits-YYYYMMDD-HHmm.csv`

### Sécurité du fichier

- **Anti-formula injection** : toute cellule commençant par `=`, `+`, `-`, `@`, tabulation ou retour chariot est préfixée d'une apostrophe `'` pour neutraliser l'exécution dans Excel/LibreOffice.
- L'export respecte les mêmes filtres usage/période que la vue admin.

---

## 7. Suppression des données

### Sur demande utilisateur

Contact déclaré : **contact@tech-me-up.com**

Ce contact est affiché dans le formulaire public et dans l'interface admin. En cas de demande de suppression (droit à l'effacement, RGPD art. 17), la suppression doit être effectuée manuellement en base via :

```sql
DELETE FROM waitlist WHERE email_norm = lower('adresse@exemple.fr');
```

### Suppression périodique

Aucun script automatique n'existe actuellement. La purge des entrées de plus de 12 mois doit être planifiée manuellement :

```sql
DELETE FROM waitlist WHERE created_at < datetime('now', '-12 months');
```

---

## 8. Risques à éviter

### Risque 1 — Absence de purge automatique
**Problème :** les données peuvent rester indéfiniment en base sans action manuelle, en contradiction avec la durée de conservation déclarée (12 mois).  
**Action recommandée :** mettre en place une tâche planifiée (cron) qui exécute la purge mensuelle.

### Risque 2 — Mot de passe admin dans le code source
**Problème :** le hash bcrypt est codé en dur dans `admin/login.php`. Toute personne ayant accès au dépôt git voit le hash.  
**Action recommandée :** externaliser le hash dans une variable d'environnement ou un fichier de configuration hors dépôt.

### Risque 3 — Fichier SQLite exposé si .htaccess non pris en compte
**Problème :** le dossier `data/` est protégé par `.htaccess` (`Require all denied`), mais si Apache n'a pas `AllowOverride All`, le fichier `waitlist.sqlite` est accessible directement.  
**Action recommandée :** vérifier la configuration Apache et déplacer le fichier SQLite hors de la racine web si possible.

### Risque 4 — Absence de rate limiting sur l'API
**Problème :** `api/subscribe.php` n'implémente pas de limite de requêtes. Un robot peut inscrire massivement des adresses malgré le honeypot.  
**Action recommandée :** ajouter un rate limiting par IP (ex. : via `.htaccess` ou un middleware), ou un token CSRF.

### Risque 5 — HTTPS non forcé
**Problème :** le cookie de session est `secure` uniquement si HTTPS est détecté, mais aucune redirection HTTP → HTTPS n'est configurée dans le code.  
**Action recommandée :** forcer HTTPS au niveau serveur (Apache VirtualHost ou `.htaccess`).

### Risque 6 — Aucun log des accès admin
**Problème :** il n'existe pas de trace des connexions à l'interface d'administration (qui s'est connecté, quand, quelle action).  
**Action recommandée :** journaliser les connexions et les exports CSV (date, IP, user agent) dans un fichier de log sécurisé.

---

## Résumé rapide

| Point | État actuel |
|---|---|
| Données minimales | Oui — seuls les champs strictement nécessaires sont collectés |
| Consentement explicite | Oui — checkbox active, validée client + serveur |
| Durée de conservation déclarée | 12 mois |
| Purge automatique | Non — à implémenter |
| Accès protégé | Oui — session PHP, bcrypt, cookie sécurisé |
| Export CSV sécurisé | Oui — anti-formula injection, admin uniquement |
| Droit à l'effacement | Manuel — contact@tech-me-up.com |
| HTTPS forcé | Non — à configurer au niveau serveur |
