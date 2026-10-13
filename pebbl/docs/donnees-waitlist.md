# Données collectées par la liste d'attente Pebbl

Note interne rédigée à partir du code existant (état au 13/10/2026). Elle décrit ce que la waitlist collecte réellement, comment ces données sont utilisées et protégées, et les points à corriger avant une mise en production.

Fichiers analysés :

- `waitinglist.html` — formulaire public et envoi des données ;
- `backend/db.php` — schéma de la base SQLite ;
- `backend/subscribe.php` — validation serveur et enregistrement ;
- `admin/*.php` — authentification, tableau de bord, export CSV, suppression ;
- `data/.htaccess`, `.gitignore` — protection du fichier de base.

> Cette note est un état des lieux technique, pas un avis juridique. Les mentions légales et la politique de confidentialité définitives doivent être validées par la personne responsable de la conformité RGPD.

---

## 1. Champs stockés

Base : SQLite, fichier `data/waitlist.sqlite`, table unique `subscribers` (`backend/db.php:39-49`).

| Colonne | Origine | Type / contrainte | Obligatoire | Commentaire |
|---|---|---|---|---|
| `id` | généré | entier auto-incrémenté | — | Identifiant technique, utilisé pour la suppression. |
| `first_name` | champ « Prénom » | texte, 100 caractères max | oui | Espaces de début et de fin retirés. |
| `last_name` | champ « Nom » | texte, 100 caractères max | oui | Idem. |
| `email` | champ « Adresse e-mail » | texte, 254 caractères max, **unique** (insensible à la casse) | oui | Format vérifié par `FILTER_VALIDATE_EMAIL`. Une adresse ne peut être inscrite qu'une fois. |
| `usage` | liste « Usage principal envisagé » | valeur parmi `concentration`, `zen`, `energie`, `autre` | oui | Déclaratif, choisi par la personne. |
| `consent` | case à cocher | `0` ou `1` | oui | Toujours `1` en pratique : l'inscription est refusée sans case cochée. |
| `created_at` | généré | horodatage UTC ISO 8601 (`2026-10-13T09:31:00Z`) | — | Date d'inscription, qui sert aussi de date de consentement. |

**Ce qui n'est volontairement pas collecté** (`backend/db.php:38`) : adresse IP, empreinte d'IP, user agent, cookies de suivi, outil d'analyse d'audience. La page `waitinglist.html` n'utilise ni `localStorage`, ni cookie, ni ressource tierce (polices système, aucun script externe).

À noter : les **journaux du serveur web** (Apache/nginx) enregistrent en général l'IP et le user agent de chaque requête, y compris l'appel à `backend/subscribe.php`. Ce n'est pas géré par l'application, mais il faut le prendre en compte dans la durée de conservation globale (voir §4).

---

## 2. Finalité

Finalité annoncée à l'utilisateur, sous la case de consentement (`waitinglist.html:371`) :

> « Vos coordonnées servent uniquement à vous informer du lancement de Pebbl et sont conservées 12 mois. »

Usages couverts par cette finalité :

- envoyer les informations sur le lancement de Pebbl et ses prochaines étapes ;
- compter les inscrits et la répartition par usage envisagé (statistiques agrégées du tableau de bord).

Usages **non couverts** — ils demanderaient une nouvelle information et un nouveau consentement :

- newsletter générale, offres commerciales sans lien avec le lancement ;
- partage ou revente des coordonnées à des partenaires ;
- croisement avec d'autres sources (réseaux sociaux, enrichissement de données) ;
- utilisation des adresses pour de la publicité ciblée (audiences personnalisées).

Le champ `usage` est une **préférence déclarée**, pas une donnée comportementale. Il peut servir à personnaliser le message de lancement (ex. mettre en avant l'ambiance concentration), jamais à profiler la personne. Conformément aux contraintes de discours de `docs/pebbl.md` §13, les communications envoyées ne doivent pas non plus promettre de bénéfice sur le stress, le sommeil ou la concentration.

---

## 3. Consentement

**Recueil**

- Case à cocher non pré-cochée : « J'accepte d'être recontacté·e par Pebbl au sujet du lancement. » (`waitinglist.html:365-367`).
- Vérification côté client **et** côté serveur : `subscribe.php:68` n'accepte que la valeur booléenne `true` ; toute autre valeur renvoie une erreur 422 sur le champ `consent`.
- Aucune inscription n'est enregistrée sans consentement : la colonne `consent` vaut donc toujours `1`.

**Preuve du consentement**

- Ce qui est conservé : le fait que la case a été cochée (`consent = 1`) et la date (`created_at`).
- Ce qui manque : la **version du texte** de consentement et de la mention d'information affichées au moment de l'inscription. Si la formulation change, il ne sera plus possible de savoir ce que chaque personne a accepté.

**Information de la personne**

La mention actuelle donne la finalité et la durée, mais pas :

- l'identité du responsable du traitement (raison sociale de Pebbl) ;
- la manière d'exercer ses droits (accès, rectification, effacement, retrait du consentement) et l'adresse de contact correspondante ;
- le droit de saisir la CNIL.

Le lien « Confidentialité » du pied de page (`waitinglist.html:401`) renvoie vers la section réassurance de `index.html`, qui parle des signaux des appareils connectés et pas de la liste d'attente. Il n'existe pas de page de politique de confidentialité.

**Retrait du consentement**

Aucun moyen autonome n'est proposé (pas de lien de désinscription, pas de formulaire). Le retrait passe aujourd'hui forcément par une demande traitée manuellement par un administrateur (voir §7).

**Double opt-in**

Il n'y en a pas : n'importe qui peut inscrire l'adresse d'une autre personne. L'adresse n'est jamais vérifiée avant l'enregistrement.

---

## 4. Conservation

**Durée annoncée** : 12 mois à compter de l'inscription — sur le formulaire (`waitinglist.html:371`) et rappelée en tête du tableau de bord (`admin/dashboard.php:76`).

**Durée réellement appliquée** : illimitée. Aucun code ne supprime les inscriptions de plus de 12 mois (pas de tâche planifiée, pas de purge au chargement de l'admin). L'engagement affiché n'est donc pas tenu techniquement.

Purge à mettre en place, par exemple via une tâche planifiée quotidienne :

```sql
DELETE FROM subscribers
WHERE created_at < strftime('%Y-%m-%dT%H:%M:%SZ', 'now', '-12 months');
```

Le format ISO 8601 UTC de `created_at` permet cette comparaison directe.

**Emplacement et protection du fichier**

- `data/waitlist.sqlite`, créé automatiquement au premier accès (`backend/db.php:31-49`).
- Exclu du dépôt git (`.gitignore` : `data/*.sqlite`, fichiers `-journal`, `-wal`, `-shm`).
- Accès web direct bloqué par `data/.htaccess` (`Require all denied` + `Options -Indexes`). **Cette protection ne fonctionne qu'avec Apache** et seulement si `AllowOverride` est activé : sous nginx, le serveur intégré de PHP ou certains hébergeurs, le fichier pourrait être téléchargeable par simple URL.
- Les suppressions SQLite peuvent laisser des traces dans les pages libres du fichier : un `VACUUM` périodique (après la purge) les efface physiquement.

**Copies à ne pas oublier** : sauvegardes de la base, exports CSV téléchargés (§6), journaux du serveur web, éventuelle copie dans un outil d'e-mailing au moment du lancement. La durée de 12 mois doit s'appliquer à chacune.

---

## 5. Accès admin

**Authentification** (`admin/config.php`, `admin/auth.php`, `admin/login.php`)

- Un **compte unique** : identifiant `admin`, mot de passe vérifié par `password_verify()` contre un hash bcrypt stocké dans `admin/config.php`.
- Ces identifiants sont explicitement **des identifiants de démonstration**, à changer avant toute mise en production.
- Message d'erreur générique, qui ne révèle pas quel champ est faux.
- Régénération de l'identifiant de session après connexion (protection contre la fixation de session).
- Cookie de session : `HttpOnly`, `SameSite=Lax`, limité au chemin `/admin/`, expiré à la fermeture du navigateur ; `Secure` seulement si le site est servi en HTTPS.
- Déconnexion : session vidée, détruite et cookie expiré (`admin/logout.php`).

**Ce que voit l'administrateur** (`admin/dashboard.php`)

- Total des inscrits et répartition par usage.
- Tableau paginé (50 lignes par page, plus récents en premier) : prénom, nom, e-mail, usage, date d'inscription. Toutes les valeurs sont échappées (`htmlspecialchars`), ce qui protège contre l'injection de HTML/JS via les champs du formulaire.

**Limites actuelles**

- Pas de limitation du nombre de tentatives de connexion (attaque par force brute possible).
- Compte partagé : impossible de savoir qui a consulté, exporté ou supprimé des données.
- Aucun journal des accès, des exports ni des suppressions.
- Pas d'expiration de session par inactivité côté serveur.
- Pas de second facteur d'authentification.

---

## 6. Export CSV

Fichier : `admin/export.php`, accessible uniquement après authentification, via le bouton « Exporter en CSV ».

- **Contenu** : toutes les inscriptions, des plus anciennes aux plus récentes.
- **Colonnes** : `Prénom ; Nom ; Email ; Usage ; Consentement ; Date d'inscription` — l'usage est traduit en libellé (« Énergie »…), le consentement en « Oui »/« Non », la date reste au format ISO UTC.
- **Format** : séparateur `;`, encodage UTF-8 avec BOM (lecture correcte des accents dans Excel).
- **Nom du fichier** : `pebbl-inscrits-AAAA-MM-JJ.csv`.
- **En-têtes HTTP** : `no-store, no-cache` — le fichier n'est pas conservé dans les caches intermédiaires.

**Points d'attention**

- Une fois téléchargé, le fichier **échappe à tout contrôle** : il n'est concerné ni par la purge à 12 mois, ni par les suppressions faites ensuite dans l'admin. Il doit être stocké dans un espace protégé, partagé le moins possible et supprimé dès qu'il n'est plus utile.
- **Injection de formule CSV** : les prénoms et noms sont exportés tels quels. Une valeur commençant par `=`, `+`, `-` ou `@` (ex. un prénom `=HYPERLINK(...)`) sera interprétée comme une formule à l'ouverture dans Excel ou LibreOffice. Il faut neutraliser ces caractères à l'export (préfixer la cellule par une apostrophe).
- L'export ne contient que des colonnes utiles : ne pas y ajouter de données techniques (id, IP…) sans besoin précis.

---

## 7. Suppression

**Mécanisme existant** (`admin/delete.php`)

- Bouton « Supprimer » sur chaque ligne du tableau de bord, avec confirmation navigateur.
- Requête `POST` uniquement, protégée par un **jeton CSRF** comparé en temps constant (`hash_equals`).
- Identifiant validé comme entier positif, requête préparée : `DELETE FROM subscribers WHERE id = :id`.
- Suppression **définitive** (pas de corbeille, pas de suppression logique).

**Limites**

- **Pas de recherche** par e-mail ou par nom dans l'admin : pour traiter une demande d'effacement, il faut parcourir les pages de 50 inscrits à la main.
- **Pas de suppression en libre-service** pour la personne inscrite (lien de désinscription, formulaire).
- **Pas de trace** de la demande ni de la date de son traitement : impossible de prouver qu'une demande d'effacement a été honorée dans le délai d'un mois.
- La suppression n'atteint ni les exports CSV déjà téléchargés, ni les sauvegardes, ni un éventuel outil d'e-mailing.
- Aucune purge automatique (voir §4).

**Procédure recommandée en attendant** : à réception d'une demande, retrouver l'inscription, la supprimer depuis l'admin, supprimer ou mettre à jour les exports CSV en circulation, puis confirmer la suppression à la personne, en notant la date de la demande et celle du traitement.

---

## 8. Risques à éviter

Classés par priorité.

### Priorité haute — avant toute mise en production

1. **Promettre 12 mois de conservation sans les appliquer.** Mettre en place la purge automatique (§4) ; tant qu'elle n'existe pas, l'information donnée aux inscrits est inexacte.
2. **Garder les identifiants de démonstration.** Changer l'identifiant et le mot de passe admin, les sortir du code (variable d'environnement ou fichier non versionné), servir l'admin uniquement en HTTPS.
3. **Base SQLite téléchargeable.** Placer `data/` **en dehors** de la racine web plutôt que de compter sur `.htaccess` ; à défaut, vérifier sur le serveur réel qu'une requête vers `/data/waitlist.sqlite` renvoie bien 403.
4. **Information incomplète des personnes.** Créer une vraie page de politique de confidentialité (responsable du traitement, finalité, durée, droits, contact, CNIL) et y faire pointer le lien « Confidentialité » de la page waitlist.
5. **Injection de formule dans l'export CSV.** Neutraliser les cellules commençant par `=`, `+`, `-` ou `@`.

### Priorité moyenne

6. **Énumération des adresses inscrites.** Le message « Cette adresse e-mail est déjà inscrite » permet à n'importe qui de vérifier si une personne est sur la liste. Préférer une réponse identique dans tous les cas (ex. « Merci, votre inscription est bien prise en compte »).
7. **Inscriptions abusives.** Pas de limitation de fréquence ni de protection anti-robot sur `backend/subscribe.php`, et pas de double opt-in : risque de spam et d'adresses inscrites à l'insu de leur titulaire. Ajouter au minimum une limitation par IP (sans stocker l'IP en base) et idéalement un e-mail de confirmation.
8. **Force brute sur l'admin.** Limiter les tentatives de connexion et ajouter une expiration de session après inactivité.
9. **Droits des personnes difficiles à exercer.** Ajouter une recherche par e-mail dans l'admin, un lien de désinscription dans chaque e-mail envoyé, et un registre simple des demandes traitées.
10. **Pas de preuve de la version du consentement.** Enregistrer une version du texte de consentement (ex. colonne `consent_version`) à chaque inscription.

### À garder en tête dans l'usage quotidien

11. **Dériver de la finalité.** Ne pas réutiliser la liste pour une newsletter générale, de la prospection sans lien avec le lancement, de la publicité ciblée ou un partage avec des tiers sans nouveau consentement.
12. **Transformer `usage` en profil.** Le présenter et l'utiliser comme une préférence choisie par la personne, jamais comme une analyse de son comportement — cohérent avec la règle de marque « jamais de surveillance ».
13. **Faire circuler les exports.** Pas d'envoi de CSV par e-mail ou messagerie, pas de copie sur un poste personnel, suppression après usage.
14. **Collecter plus que nécessaire.** Le schéma est volontairement minimal (pas d'IP, pas de user agent) : tout nouveau champ (téléphone, ville, date de naissance…) doit être justifié par la finalité avant d'être ajouté.
15. **Oublier les copies.** Appliquer la durée de 12 mois et les suppressions aux sauvegardes, aux journaux serveur et à l'outil d'e-mailing qui sera utilisé au lancement.

---

## Synthèse

| Thème | Situation actuelle | Statut |
|---|---|---|
| Champs stockés | Prénom, nom, e-mail, usage, consentement, date — pas d'IP ni de user agent | Conforme au principe de minimisation |
| Finalité | Information sur le lancement, annoncée sur le formulaire | Claire, à documenter dans une politique de confidentialité |
| Consentement | Case non pré-cochée, vérifiée côté serveur | Correct, sans version du texte ni double opt-in |
| Conservation | 12 mois annoncés, aucune purge | **À corriger** |
| Accès admin | Mot de passe hashé, session sécurisée, CSRF | Identifiants de démo, pas de limitation des tentatives ni de journal |
| Export CSV | Réservé à l'admin, non mis en cache | Injection de formule à corriger, copies hors contrôle |
| Suppression | Définitive, protégée par CSRF | Pas de recherche, pas de libre-service, pas de trace |
