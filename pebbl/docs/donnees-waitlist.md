# Données collectées par la liste d’attente Pebbl

Date : 20 octobre 2026. Note établie à partir du code actuel (`waitinglist.html`, `api/inscription.php`, `includes/`, `admin/`, `router.php`). Elle décrit ce que fait réellement le code, puis ce qui reste à traiter. Elle ne remplace pas un avis juridique.

## 1. Champs stockés

Base : fichier SQLite `data/pebbl.sqlite` (chemin modifiable par la variable d’environnement `PEBBL_DB_PATH`), schéma créé dans `includes/db.php`.

### Table `inscriptions` (seule table contenant des données personnelles)

| Colonne | Origine | Obligatoire | Règles appliquées côté serveur |
|---|---|---|---|
| `id` | Généré | Oui | Entier, clé primaire |
| `prenom` | Formulaire | Oui | 1 à 80 caractères, UTF-8 valide, sans caractère de contrôle ni de format (ex. U+202E) |
| `nom` | Formulaire | **Non** | 80 caractères maximum, vide enregistré comme `NULL` |
| `email` | Formulaire | Oui | Minuscules, `FILTER_VALIDATE_EMAIL`, 254 caractères maximum, **unique** |
| `usage` | Formulaire (bouton radio) | Oui | Liste blanche : `travail`, `pause`, `mouvement`, `ambiance` (`includes/usages.php`) |
| `consentement_at` | Serveur | Oui | Horodatage UTC ISO 8601 de l’enregistrement, posé seulement si la case vaut `on` |
| `created_at` | Serveur | Oui | Horodatage UTC ISO 8601, sert de point de départ à la conservation |

### Données volontairement non collectées

- Ni adresse IP, ni `ip_hash`, ni `user_agent`, ni cookie côté visiteur (décision de `docs/decisions-waitlist.md`).
- Le champ piège `site_web` n’est jamais enregistré : s’il est rempli, la requête reçoit une réponse de succès et rien n’est écrit.
- Aucune ressource tierce (police, script, mesure d’audience) n’est chargée par `waitinglist.html` ni par `index.html` : aucun transfert vers un tiers lors de la visite.

### Tables techniques (sans donnée personnelle)

- `rate_limit` : un compteur de requêtes par minute, tous visiteurs confondus (20 au maximum), conservé 5 minutes.
- `login_attempts` : uniquement la date des échecs de connexion admin, purgée au bout de 15 minutes.
- Le journal d’erreurs (`error_log`) ne contient que la classe et le message de l’exception, jamais les champs du formulaire.

## 2. Finalité

- **Finalité unique** : prévenir la personne par e-mail de la disponibilité de Pebbl (mention affichée sous le bouton d’envoi et dans « En savoir plus sur vos données »).
- **Champ `usage`** : sert uniquement à des statistiques agrégées (répartition par usage dans l’admin) pour savoir quelles ambiances intéressent le plus. La page l’annonce : « il ne sert pas à vous profiler ».
- **Prénom et nom** : personnaliser le futur message. Le nom est facultatif (minimisation, question ouverte de `decisions-waitlist.md` tranchée dans le code).
- Toute autre utilisation (newsletter, prospection pour d’autres produits, enrichissement, cession) sort de la finalité annoncée et demanderait un nouveau consentement.

## 3. Consentement

- **Base légale affichée** : le consentement, retirable à tout moment.
- **Recueil** : case à cocher non précochée, obligatoire, libellé « J’accepte que Pebbl me recontacte par e-mail au sujet de sa disponibilité. ». Vérifiée côté client et **côté serveur** (`consentement` doit valoir `on`, sinon erreur 422).
- **Preuve** : `consentement_at` enregistre la date. Limites :
  - la date est identique à `created_at` à la seconde près : la colonne n’apporte pas d’information supplémentaire ;
  - le **texte** ou la version du libellé accepté n’est pas conservé : si le libellé change, on ne saura plus ce qui a été accepté par chaque inscrit.
- **Doublon** : une adresse déjà inscrite ne modifie rien (`ON CONFLICT(email) DO NOTHING`) et reçoit la même réponse qu’un succès, pour ne pas révéler qu’elle existe. Le consentement et la date d’origine restent ceux de la première inscription.
- **Retrait** : aucun mécanisme en libre-service. Il passe par l’e-mail de contact, encore à compléter dans la page, puis par une suppression manuelle dans l’admin.
- **Adresse non vérifiée** : pas de double opt-in (l’envoi d’e-mails est hors périmètre). N’importe qui peut inscrire l’adresse d’un tiers.

## 4. Conservation

- **Durée** : 12 mois après l’inscription (`RETENTION_MOIS = 12` dans `includes/config.php`), cohérente avec la mention affichée.
- **Purge** : `purge_expired()` supprime les lignes dont `created_at` dépasse 12 mois. Elle est déclenchée :
  - après chaque nouvelle inscription réussie ;
  - à chaque affichage du tableau de bord admin (un message indique le nombre de lignes supprimées).
- **Limite** : la purge est « paresseuse ». Sans nouvelle inscription ni visite de l’admin, des données de plus de 12 mois restent en base. L’admin signale ces lignes par un badge « plus de 12 mois », mais elles ne sont supprimées qu’à cette visite.
- **Copies hors base** : les fichiers `-wal` / `-shm` de SQLite, les sauvegardes éventuelles du poste et surtout les **exports CSV** ne sont pas couverts par la purge.
- **Versionnement** : `data/*` et `*.sqlite` (avec `-wal`, `-shm`, `-journal`) sont exclus par `.gitignore` ; vérifié avec `git check-ignore` pour `data/pebbl.sqlite`.

## 5. Accès admin

- **Compte unique** : identifiant et hash du mot de passe dans `config/config.local.php` (non versionné), généré par `bin/hash-password.php` (saisie sur l’entrée standard, jamais en argument). Sans hash configuré, l’admin refuse toute connexion.
- **Session** : cookie `PEBBLADMIN`, `HttpOnly`, `SameSite=Strict`, limité à `/admin/`, `Secure` uniquement en HTTPS. Identifiant régénéré à la connexion. Expiration après 30 minutes d’inactivité ou 8 heures au total.
- **Protection des actions** : export, suppression et déconnexion en POST avec jeton CSRF. Toutes les sorties HTML échappées. En-têtes `no-store`, `noindex`, CSP stricte, `X-Frame-Options: DENY`.
- **Tentatives de connexion** : blocage après 5 échecs en 15 minutes, compteur global (sans IP) et par session, délai d’une seconde après chaque échec.
- **Exposition web** : `router.php` ne sert qu’une liste blanche d’URL ; `data/`, `config/`, `includes/`, `bin/` et `docs/` répondent 404 (et `.htaccess` « Require all denied » si le site passe sous Apache). `get_db()` refuse de fonctionner si `php -S` est lancé sans le routeur.
- **Ce que voit l’admin** : statistiques (total, 7 et 30 derniers jours, répartition par usage) et la liste complète des inscrits avec prénom, nom, e-mail, usage, dates.
- **Limites** :
  - aucune trace des accès ni des actions admin (connexion, export, suppression) ;
  - un tiers peut bloquer la connexion légitime pendant 15 minutes en multipliant les échecs (verrou global) ;
  - les identifiants de démonstration doivent être remplacés et le site servi en HTTPS avant toute utilisation publique.

## 6. Export CSV

- **Déclenchement** : bouton « Exporter en CSV » de l’admin, en POST avec jeton CSRF (`admin/export.php`).
- **Contenu** : toutes les inscriptions, colonnes `Prénom ; Nom ; E-mail ; Usage ; Consentement ; Date d’inscription`, séparateur `;`, BOM UTF-8 pour Excel, dates en heure de Paris, usage en libellé lisible. Pas d’`id`.
- **Fichier** : `pebbl-inscrits-AAAAMMJJ-HHMM.csv`, téléchargé sur le poste de l’administrateur.
- **Sécurité** : les cellules commençant par `= + - @`, une tabulation ou un retour chariot sont préfixées d’une apostrophe pour neutraliser l’injection de formules. Conséquence : la valeur exportée peut différer de la valeur stockée (apostrophe en tête).
- **Point d’attention** : une fois téléchargé, le fichier échappe à la purge à 12 mois, aux suppressions faites ensuite dans l’admin et à la protection de la base. C’est la copie la plus exposée des données.

## 7. Suppression

- **Par l’admin** : bouton « Supprimer » sur chaque ligne, puis écran de confirmation (« Cette action est définitive. »), puis suppression effective (`admin/delete.php`, deux envois POST avec CSRF).
- **Nature** : suppression physique de la ligne (`DELETE`), sans archive ni corbeille. Aucune trace de la demande ni de son traitement n’est conservée.
- **Automatique** : purge à 12 mois décrite plus haut.
- **Par la personne** : pas de lien de désinscription ni de formulaire. Les droits (accès, rectification, effacement, retrait du consentement) s’exercent par e-mail, adresse encore à compléter.
- **Rectification** : l’admin ne permet pas de modifier une inscription ; il faut supprimer puis faire réinscrire la personne.
- **Résidus** :
  - le message de confirmation contient l’e-mail supprimé et transite par la session PHP (fichier de session sur le serveur) jusqu’au prochain affichage ;
  - SQLite ne réécrit pas immédiatement les pages libérées : la donnée peut subsister dans le fichier ou le `-wal` jusqu’à réutilisation de l’espace (`PRAGMA secure_delete` ou `VACUUM` non utilisés) ;
  - les exports CSV déjà téléchargés ne sont pas concernés.

## 8. Risques à éviter

### Discours et finalité

- Détourner la liste vers d’autres usages (newsletter générale, partenaires, revente) alors que la page promet « Données jamais revendues » et une finalité unique.
- Présenter le champ `usage` comme un profil ou en tirer des conclusions sur la personne ; ne pas croiser ces données avec d’autres sources. Respecter aussi les contraintes de `pebbl.md` : ne pas promettre un bénéfice sur le stress, le sommeil ou la concentration dans les futurs e-mails.
- Ajouter des champs « pour plus tard » (téléphone, ville, âge, IP) sans finalité annoncée.

### Mentions et droits

- Publier la page avec les mentions `[À compléter : …]` (responsable du traitement, e-mail de contact) : les droits annoncés seraient impossibles à exercer.
- Modifier le libellé du consentement sans conserver la version acceptée par les inscrits existants.
- Envoyer des e-mails à des adresses non vérifiées : prévoir un double opt-in le jour où l’envoi sera ajouté.

### Conservation et copies

- Compter sur la seule purge paresseuse : prévoir un déclenchement régulier (tâche planifiée) avant une mise en production.
- Laisser traîner des exports CSV (dossier Téléchargements, e-mail, outil de partage, tableur en ligne) : les supprimer après usage et ne jamais les transmettre par un canal non maîtrisé.
- Versionner, copier ou partager `data/pebbl.sqlite` (ou ses fichiers `-wal` / `-shm`), y compris pour un test ou une démonstration : utiliser une base vide ou fictive.

### Accès et sécurité

- Mettre en ligne avec les identifiants de démonstration, sans HTTPS ou avec `php -S` (serveur de développement uniquement).
- Lancer le serveur sans `router.php`, ou servir le dossier avec un autre serveur sans bloquer `data/` et `config/`.
- Partager le compte admin entre plusieurs personnes sans traçabilité ; envisager un journal minimal des exports et suppressions (date, action, sans donnée personnelle).
- Ajouter des journaux applicatifs qui reprendraient les champs du formulaire.

## 9. Actions recommandées

| Priorité | Action |
|---|---|
| P0 | Compléter les mentions de `waitinglist.html` (responsable, contact) sans inventer d’informations |
| P0 | Remplacer les identifiants de démonstration, servir en HTTPS avec un vrai serveur web avant toute ouverture publique |
| P1 | Planifier la purge à 12 mois indépendamment du trafic |
| P1 | Définir une procédure écrite pour les demandes de droits (délai de réponse, suppression, confirmation à la personne) et pour la destruction des exports CSV |
| P2 | Conserver la version du libellé de consentement avec chaque inscription |
| P2 | Journaliser les actions admin (export, suppression) sans donnée personnelle ; ne plus mettre l’e-mail dans le message de confirmation de suppression |
| P2 | Activer `PRAGMA secure_delete` ou lancer un `VACUUM` après les suppressions |
| Plus tard | Double opt-in et lien de désinscription dans chaque e-mail, le jour où l’envoi sera développé |
