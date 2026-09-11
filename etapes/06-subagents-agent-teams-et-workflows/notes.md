# VI - Subagents, agent teams, et workflows

Création de :
- ./.claude/agents/ avec l'agent front-reviewer
- fichiers et dossiers du back-end : /admin, /api, /data, /includes, .gitignore, .htaccess
- fichiers de docs : /docs/ci-minimale.md, /docs/deploiement-php-sqlite.md, /docs/donnees-waitlist.md

Modification de :
- ./.claude/settings.json avec le paramétrage d'agent teams
- index.html et waitinglist.html pour les améliorer et connecter le formulaire au backend


## 6.1 - Generation du subagent front-reviewer

```text
Crée un agent projet nommé front-reviewer dans .claude/agents/
Il doit auditer une page HTML/CSS après modification, sans jamais modifier les fichiers.
Il vérifie l’accessibilité, le responsive, la lisibilité et les problèmes visuels importants.
Il peut utiliser Read, Grep, Glob, et les outils Playwright si disponibles.
Il doit retourner une synthèse courte et priorisée sous forme de tableau.
Utilise Sonnet, n’ajoute pas de mémoire persistante, et garde l’agent simple et court.
```

## 6.2 - Test du subagent front-reviewer

```text
Utilise le subagent front-reviewer pour auditer @waitinglist.html
```

## 6.3 - Comparaison d'options backend avec plusieurs subagents

```text
Lance plusieurs sous-agents pour comparer les options backend possibles afin de rendre la waitlist Pebbl fonctionnelle.
Ne modifie aucun fichier.

Contexte a prendre en compte :
- le projet est une landing page HTML/CSS/JS vanilla ;
- le formulaire principal est dans @waitinglist.html
- Le site doit rester simple a deployer.

Je veux 4 angles :
- un sous-agent defend une solution PHP + SQLite sur apache
- un sous-agent defend une solution Node/Express + SQLite
- un sous-agent defend une solution sans backend maison, par exemple service externe ou serverless
- un sous-agent securite/RGPD identifie les risques principaux de chaque option.

Chaque sous-agent doit donner : avantages, limites, complexite de deploiement, risques, et recommandation.
Synthetise ensuite 2 recommandations finales : cas d'un projet reel, et cas d'un TP formation qui doit rester simple.
```

## 6.4 - Agent team pour enquete scientifique

```text
Les utilisateurs signalent que l'application se ferme apres un seul message au lieu de rester connectee.
Lance 5 coequipiers agents pour enqueter sur differentes hypotheses.
Fais-les echanger entre eux afin qu'ils tentent de refuter mutuellement leurs theories, comme dans un debat scientifique.
Mets a jour le document de conclusions avec le consensus qui emerge.
```

## 6.5 - Agent team pour revue de code

```text
Cree 3 membres d'equipe pour examiner ma codebase : securite, performances et documentation.
Fais-les partager leurs constats, contester les priorites des autres, puis produire un plan d'action commun.
```

## 6.6 - Agent team pour implementer la waitlist Pebbl

```text
Cree une agent team pour planifier puis implementer une waitlist Pebbl en PHP + SQLite.

Important : n'utilise pas de workflow dynamique ; ne lance pas de simples sous-agents independants ; je veux une equipe coordonnee : les coequipiers doivent se partager les taches, echanger sur les dependances backend/front/admin, signaler les conflits ou risques, puis produire un plan commun.

Environnement cible : hebergement mutualise type o2switch/cPanel, Apache, PHP 8+, avec PDO SQLite disponible. La solution doit rester simple, lisible, sans compte tiers et adaptee a une formation. Eviter un maximum de configuration du serveur.

Decisions deja prises :
- SQLite : base stockee dans `data/`, dossier protege par `.htaccess`, base non versionnee.
- Donnees : ne pas stocker `ip_hash` ni `user_agent` ; afficher une duree de conservation de 12 mois.
- Admin : authentification PHP simple par formulaire + session ; identifiant `admin` ; mot de passe de demo `Pebbl-Demo-2026!`.
- Securite admin : stocker le hash du mot de passe dans le code, pas en clair ; ne pas utiliser Basic Auth Apache ni `.htpasswd`.
- Apache : utiliser la protection Apache uniquement pour empecher l'acces direct au dossier `data/`.

Objectif :
- analyser @waitinglist.html pour identifier les champs a stocker ;
- creer un backend PHP + SQLite ;
- connecter le formulaire au backend ;
- creer une page admin protegee ;
- afficher les stats et permettre de lister les inscrits, permettre leur suppression et l'export CSV.

Contraintes :
- pas de framework ;
- pas d'envoi email ;
- pas de systeme utilisateur complet ;
- ne pas casser le design existant ;
- validation serveur obligatoire ;
- ne pas versionner de secret, de base SQLite locale, de dependances generees ni de logs ;
- verifier ou creer un `.gitignore` adapte ;
- proteger le dossier contenant la base SQLite contre l'acces direct via le web, en tenant compte d'Apache ;
- demander validation du plan avant d'ecrire les fichiers.

Coequipiers souhaites :
- Backend : base SQLite, endpoint d'inscription, validation serveur.
- Front : adaptation de @waitinglist.html avec fetch et gestion des erreurs.
- Admin : page admin, authentification PHP simple, stats, tableau, export CSV.
- Verification : relit le plan, verifie securite/RGPD, `.gitignore`, protection SQLite, puis teste le flux final.

Commence par organiser l'equipe, faire echanger les coequipiers, puis produis un plan commun validable avant toute modification.
```

## 6.7 - Vue agents : note deploiement

```text
Prepare une note courte dans @docs/deploiement-php-sqlite.md :
comment deployer le mini-projet Pebbl sur un hebergement mutualise type o2switch/cPanel, avec Apache, PHP 8+ et PDO SQLite.
Explique dans quel repertoire uploader les fichiers, quels fichiers / dossiers doivent etre uploades et lesquels doivent rester locaux, et pourquoi.
Resumes aussi comment acceder a l'administration.
```

## 6.8 - Vue agents : checklist CI minimale

```text
Analyse le projet actuel et prepare une checklist dans @docs/ci-minimale.md pour verifier ce projet avec GitHub Actions.

Contexte : projet PHP sans framework, front HTML/CSS/JS vanilla, SQLite locale non versionnee.

Couvre :
- syntaxe PHP
- absence de secrets ou fichiers SQLite versionnes
- verification HTML simple
- test minimal des fichiers critiques

Reste simple : pas de pipeline complexe.
```

## 6.9 - Vue agents : note donnees/RGPD

```text
Analyse le projet actuel et prepare une note dans @docs/donnees-waitlist.md sur les donnees collectees par la waitlist Pebbl.

Couvre :
- champs stockes
- finalite
- consentement
- conservation
- acces admin
- export CSV
- suppression
- risques a eviter
```

## 6.10 - Workflow dynamique mini-application

```text
Cree un workflow dynamique reutilisable pour transformer une landing page statique en mini-application web.

Applique-le au projet Pebbl.

Le workflow doit :
1. analyser les pages existantes et les formulaires ;
2. identifier la fonctionnalite interactive principale a rendre reelle ;
3. proposer une architecture backend minimale adaptee au contexte ;
4. implementer le stockage des donnees ;
5. connecter le formulaire ou l'interface existante au backend ;
6. ajouter une interface admin minimale si utile ;
7. verifier le flux complet avec Playwright ;
8. produire un rapport final : fichiers modifies, fonctionnement, limites, prochaines etapes.

Contraintes :
- garder une solution simple et pedagogique ;
- limiter le workflow a 5 sous-agents maximum ;
- demander validation avant toute modification massive ;
- eviter les frameworks lourds sauf justification.
```
