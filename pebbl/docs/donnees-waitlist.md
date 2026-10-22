# Données collectées par la waitlist Pebbl

Note interne, état du code au 22/10/2026. Sources : `includes/db.php`, `includes/config.php`, `api/subscribe.php`, `admin/*`, `waitinglist.html`, `confidentialite.html`, `README.md`.
Ce n'est pas un avis juridique : les points marqués **À décider** demandent un choix de l'éditeur.

## 1. Champs stockés

Table SQLite `waitlist`, fichier `data/waitlist.sqlite` (créée au premier envoi).

| Colonne | Origine | Remarque |
|---|---|---|
| `id` | auto-incrément | identifiant technique, affiché dans l'admin et l'export |
| `prenom` | formulaire | obligatoire, 100 caractères max, sans caractère de contrôle |
| `nom` | formulaire | idem |
| `email` | formulaire | `FILTER_VALIDATE_EMAIL`, 254 caractères max, casse d'origine conservée |
| `email_norm` | calculé | `email` en minuscules, contrainte `UNIQUE` (anti-doublon) |
| `usage_principal` | formulaire | valeur parmi la liste blanche `USAGES` (6 choix, dont « Autre ») |
| `consent_at` | serveur | date/heure UTC de l'inscription, posée au moment de l'insertion |
| `created_at` | serveur | date/heure UTC, valeur par défaut `datetime('now')` |

**Non stockés** : adresse IP, user-agent, référent, cookies. Le champ piège `website` (honeypot) n'est jamais enregistré. Aucune donnée sensible (santé, etc.) n'est demandée ; le texte libre est évité volontairement, ce qui limite les risques.

## 2. Finalité

- Nom, prénom, e-mail : prévenir la personne quand Pebbl sera disponible.
- Usage principal : mieux comprendre les attentes des futurs utilisateurs (statistiques par usage dans l'admin).
- Aucune autre utilisation prévue (pas de revente, pas de tiers, pas de publicité, pas de mesure d'audience). Toute nouvelle finalité (newsletter générale, prospection, partage avec un partenaire) exige un nouveau consentement et une mise à jour de `confidentialite.html`.

Le projet n'envoie aucun e-mail : le recontact se fait hors application, à partir de l'export CSV ou de l'admin.

## 3. Consentement

- Base légale annoncée : le consentement, case à cocher non pré-cochée, obligatoire dans le formulaire.
- Texte affiché : « J'accepte d'être recontacté(e) par e-mail au sujet de Pebbl. Je peux me désinscrire à tout moment. Mes données sont conservées 12 mois », avec lien vers la politique de confidentialité.
- Contrôle côté serveur : `consent` doit valoir exactement `true`, sinon erreur 422 et rien n'est enregistré.
- Preuve conservée : seulement `consent_at`. **On ne garde ni la version du texte de la case, ni la version de la politique.** Si le texte change, on ne peut plus prouver à quoi la personne a consenti. **À décider** : ajouter une colonne `consent_version` (ou archiver les textes avec leur date).
- Pas de double opt-in : n'importe qui peut saisir l'adresse de quelqu'un d'autre. Voir les risques.
- Retrait du consentement : promis « à tout moment » mais sans lien de désinscription ; la seule voie est d'écrire à l'adresse de contact, puis suppression manuelle (§ 6).

## 4. Conservation

- Durée annoncée : **12 mois maximum** à partir de l'inscription, ou suppression plus tôt sur demande (formulaire, politique, bandeau admin).
- **Aucune purge automatique** (le README le dit). Sans action humaine, la durée annoncée n'est pas respectée.
- Un doublon n'est jamais mis à jour (`INSERT OR IGNORE`) : la durée court depuis la première inscription.
- Au-delà de la table : le mode WAL crée `waitlist.sqlite-wal` / `-shm`, et toute sauvegarde ou copie du fichier garde les données. Une suppression en base ne les efface pas de ces copies.

Requête de purge à lancer au moins une fois par mois (à planifier, ou à transformer en script cron) :

```sql
DELETE FROM waitlist WHERE created_at < datetime('now', '-12 months');
```

## 5. Accès administrateur

- Pages `admin/login.php`, `admin/index.php`, `admin/export.php`, protégées par `admin/includes/guard.php` (session PHP).
- Un seul compte partagé (`ADMIN_USER` / `ADMIN_HASH` dans `includes/config.php`), mot de passe haché avec `password_hash`. Les identifiants actuels sont des **identifiants de démonstration**, et le mot de passe figure en clair dans le README.
- Protections en place : cookie de session `httponly` et `SameSite=Strict` (`secure` si HTTPS), `session_regenerate_id` à la connexion, `sleep(1)` après un échec, en-têtes `no-store`, `X-Frame-Options: DENY`, `noindex`, jeton CSRF sur la suppression, échappement HTML de toutes les valeurs affichées.
- Ce que l'admin voit : tous les champs sauf `email_norm`, plus des compteurs (total, 7 j, 30 j, répartition par usage).
- Manques : pas de journal des connexions, des consultations, des exports ni des suppressions ; pas d'expiration de session ; pas de limite de tentatives au-delà du `sleep(1)` ; pas de comptes nominatifs.

## 6. Export CSV

- `admin/export.php`, réservé à l'admin connecté, mêmes filtres que la liste (usage, période 7 / 30 jours).
- Colonnes : ID, Prénom, Nom, E-mail, Usage, Consentement (UTC), Inscription (UTC). Séparateur `;`, UTF-8 avec BOM (compatible Excel), nom `pebbl-inscrits-AAAAMMJJ-HHMM.csv`.
- Protection contre l'injection de formules : les cellules commençant par `= + - @` ou tabulation / retour chariot sont préfixées par `'`.
- L'export sort du périmètre protégé : le fichier arrive sur le poste de la personne, dans ses téléchargements, et n'est couvert ni par la suppression, ni par la durée de 12 mois. Aucune trace de qui exporte quoi.

## 7. Suppression

- Par ligne, dans l'admin : bouton « Supprimer », confirmation JavaScript, requête `POST` avec jeton CSRF, `DELETE FROM waitlist WHERE id = :id`. Suppression définitive.
- Pas de recherche par e-mail : pour traiter une demande, il faut retrouver la ligne à la main dans la liste.
- Pas de suppression en lot, pas de purge automatique (§ 4).
- Pas de message de confirmation à la personne.
- La suppression efface aussi la preuve de consentement. C'est le comportement voulu pour un retrait, mais à connaître : seul un journal séparé (sans données personnelles) permettrait de prouver qu'une demande a été traitée.
- SQLite ne réécrit pas les pages libérées : le contenu supprimé peut subsister dans le fichier. **À décider** : `PRAGMA secure_delete=ON` à l'ouverture de la base, ou `VACUUM` après les purges.
- Une personne supprimée peut se réinscrire (l'unicité ne porte que sur les lignes existantes).

## 8. Risques à éviter

**À traiter avant toute mise en ligne**
1. Garder les identifiants de démonstration (`admin` + mot de passe public du README). Générer un nouveau hash et retirer le mot de passe du README.
2. Servir l'admin en HTTP : sans HTTPS, mot de passe et cookie de session circulent en clair, et le drapeau `secure` du cookie n'est pas posé.
3. Laisser `[À compléter]` dans `confidentialite.html` (responsable, contact, hébergeur) : la page ne remplit alors pas son rôle d'information.
4. Ne pas purger à 12 mois : promesse non tenue envers les inscrits.
5. Exposer `data/`, `includes/` ou `docs/` : sous Apache, seuls `data/`, `includes/` et `admin/includes/` ont un `.htaccess`. `router.php` bloque aussi `docs/` et les `.md`, **mais pas Apache** : sans règle équivalente, `docs/*.md` (dont cette note) et `README.md` seraient lisibles publiquement. Ajouter un `.htaccess` dans `docs/` ou héberger hors de la racine web.

**À limiter**
6. Inscription par un tiers : sans double opt-in, une adresse peut être saisie sans l'accord de son titulaire, et le consentement affiché n'est pas vérifié. Mieux : e-mail de confirmation avant de considérer l'inscription valide.
7. Multiplier les copies : fichiers CSV laissés en téléchargements, envois par mail, tableur partagé, sauvegarde de la base. Chaque copie échappe à la purge. Supprimer l'export après usage ; ne jamais le déposer dans le dépôt (le `.gitignore` couvre déjà `*.sqlite`, pas les `.csv`).
8. Versionner la base : `data/*` et `*.sqlite*` sont ignorés ; garder cette règle, et ne jamais commiter `waitlist.sqlite-wal`.
9. Compte admin partagé et sans journal : impossible de savoir qui a consulté ou exporté. Au minimum, noter dates et motifs des exports.
10. Élargir la collecte « au cas où » (téléphone, IP, âge, texte libre) sans réviser finalité, formulaire et politique ensemble. Le principe actuel (aucune IP, aucun user-agent, champs strictement utiles) est un atout à conserver.
11. Réutiliser la liste pour autre chose que « prévenir de la disponibilité de Pebbl » sans nouveau consentement.
12. Écrire des données personnelles dans les logs : `error_log` ne reçoit aujourd'hui que le message d'exception PDO ; garder cette discipline.

## 9. Actions suggérées (par ordre de priorité)

1. Changer les identifiants admin, retirer le mot de passe du README, forcer HTTPS.
2. Compléter `confidentialite.html`.
3. Planifier la purge à 12 mois et activer `secure_delete` (ou `VACUUM` après purge).
4. Protéger `docs/` côté Apache.
5. Décider du double opt-in, de `consent_version` et d'une recherche par e-mail dans l'admin.
