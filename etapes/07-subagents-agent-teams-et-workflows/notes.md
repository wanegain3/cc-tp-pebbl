# VII - Subagents, agent teams, et workflows

Création de :
- ./.claude/agents/performance-auditor.md
- fichiers et dossiers du back-end : /admin, /api, /data, /includes, .gitignore, .htaccess
- fichiers de docs : /docs/ci-minimale.md, /docs/deploiement-php-sqlite.md, /docs/donnees-waitlist.md

Modification de :
- ./.claude/settings.json avec le paramétrage d'agent teams
- index.html et waitinglist.html pour les améliorer et connecter le formulaire au backend


## 7.1 - Generation du subagent performance-auditor

```text
Crée un agent projet nommé `performance-auditor` dans `.claude/agents/`.

Il audite les performances front-end uniquement à partir des fichiers du projet, sans modifier quoi que ce soit.

Il utilise seulement Read, Glob et Grep.

Il doit notamment :
- repérer les images inutilement lourdes ou potentiellement surdimensionnées
- vérifier le lazy-loading lorsque pertinent
- identifier les ressources CSS/JS potentiellement coûteuses ou bloquantes
- repérer les ressources inutiles ou redondantes

Pour les images, ne recommande jamais de modifier les proportions ni d’ajouter arbitrairement des attributs `width`/`height`.

Retourne un tableau court et priorisé :
priorité | problème | impact probable | recommandation

Utilise Sonnet, sans mémoire persistante.

Garde la `description` courte : 1 ou 2 phrases maximum, sans exemples.
```

## 7.2 - Test du subagent front-reviewer

```text
Utilise le subagent front-reviewer pour auditer @waitinglist.html
```

## 7.3 - Comparaison d'options backend avec plusieurs subagents

```text
Lance plusieurs sous-agents indépendants pour comparer les solutions backend permettant de rendre la waitlist Pebbl fonctionnelle.

Ne modifie aucun fichier et n'installe rien.

Contexte :
- Le projet est une landing page HTML/CSS/JS vanilla.
- Le formulaire principal est dans @waitinglist.html.
- Priorité : une solution simple, rapide à mettre en place, réellement exécutable et testable dans notre environnement.
- Privilégier les technologies déjà installées et autorisées, sans dépendances supplémentaires.
- Stockage persistant simple, idéalement dans un fichier local (SQLite de préférence, JSON si nécessaire). Pas de serveur de base de données séparé ni de service tiers.

Étape 1 — Investiguer en parallèle

Lance des sous-agents indépendants pour étudier chacun une solution : PHP, Python, Node.js et Java. Ajoute un sous-agent transversal sécurité/RGPD.

Chaque sous-agent technique doit :
- vérifier si sa technologie est disponible et utilisable sur ce poste ;
- identifier les dépendances nécessaires et les possibilités de stockage local ;
- si nécessaire, examiner la faisabilité technique d'une installation minimale (droits et restrictions détectables), sans rien installer ;
- proposer une solution simple permettant validation côté serveur, stockage persistant et administration ;
- évaluer la facilité de lancement et de vérification locale, les limites et les risques.

Le sous-agent sécurité/RGPD examine les précautions communes aux solutions proposées.

Étape 2 — Synthétiser et recommander

Compare brièvement les conclusions des sous-agents et recommande une seule stack adaptée à notre TP, en privilégiant une solution immédiatement exécutable.

Justifie ton choix et attends ma validation avant toute implémentation.

Si aucune solution n'est utilisable, signale clairement les blocages, les éventuelles autorisations à obtenir et les prérequis nécessaires. N'invente pas de solution de contournement.
```

## 7.4 - Agent team pour implementer la waitlist Pebbl

```text
Crée une agent team pour planifier puis implémenter une waitlist Pebbl avec la stack retenue et validée lors de notre comparaison précédente.

Important : je veux une équipe coordonnée, pas de simples sous-agents indépendants ni de workflow dynamique. 
Les coéquipiers doivent échanger sur les dépendances backend/front/admin, signaler les conflits et produire un plan commun.

Environnement :
- Utilise la stack précédemment validée et les outils disponibles sur ce poste.
- Privilégie une solution légère, sans framework lourd ni installation supplémentaire non autorisée.
- Le backend doit pouvoir être lancé et testé localement.
- Privilégie un stockage persistant dans un fichier (SQLite ou JSON selon la stack).
- Si aucun backend n'est exécutable sur ce poste, utilise PHP 8 + SQLite comme cible de génération, sans prétendre pouvoir tester son exécution.

Décisions déjà prises :
- Stockage local non versionné, protégé contre les accès directs depuis le web.
- Ne pas stocker `ip_hash` ni `user_agent` ; afficher une durée de conservation de 12 mois.
- Admin : authentification simple par formulaire et session ; identifiant `admin`, mot de passe de démonstration `Pebbl-Demo-2026!`.
- Stocker uniquement le hash du mot de passe, jamais le mot de passe en clair dans le code.
- Prévoir le remplacement des identifiants de démonstration avant toute utilisation publique.

Objectif :
- Analyser @waitinglist.html pour identifier les champs à stocker.
- Créer le backend et son stockage persistant.
- Connecter le formulaire au backend.
- Créer une administration protégée.
- Afficher les statistiques, lister et supprimer les inscrits, permettre l'export CSV.

Contraintes :
- Pas d'envoi d'email ni de système utilisateur complet.
- Préserver le design existant.
- Validation côté serveur obligatoire.
- Ne pas versionner les secrets, données locales ou logs.
- Vérifier ou créer un `.gitignore` adapté.
- Protéger les données contre tout accès non autorisé.
- Demander validation du plan avant d'écrire les fichiers.

Coéquipiers souhaités :
- Backend : stockage, endpoint d'inscription, validation serveur.
- Front : adaptation de @waitinglist.html avec `fetch` et gestion des erreurs.
- Admin : authentification, statistiques, tableau, suppression, export CSV.
- Vérification : sécurité/RGPD, `.gitignore`, protection des données et tests du flux complet lorsque l'environnement le permet.

Commence par organiser l'équipe, faire échanger les coéquipiers, puis produis un plan commun validable avant toute modification.
```

## 7.5 - test de /verify

```text
/verify contrôle, selon les outils disponibles, le flux inscription → stockage → administration → suppression → export CSV.
```

## 7.6 - Vue agents : note deploiement

```text
Prépare une note courte dans @docs/execution-locale.md expliquant comment lancer et tester Pebbl avec la stack retenue. Précise les prérequis, les commandes de démarrage et d'arrêt, le stockage des données, l'accès à l'administration et les vérifications essentielles. Appuie-toi sur les fichiers réellement présents dans le projet.
```

## 7.7 - Vue agents : checklist CI minimale

```text
Analyse le projet actuel et prépare une checklist dans @docs/ci-minimale.md pour le vérifier avec GitHub Actions. Adapte les contrôles à la stack retenue : syntaxe du backend, absence de secrets ou données locales versionnées, vérification HTML simple et tests minimaux des fonctionnalités critiques. Reste simple : pas de pipeline complexe.
```

## 7.8 - Vue agents : note donnees/RGPD

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

## 7.9 - Workflow dynamique mini-application

```text
Crée et exécute un workflow dynamique de chasse aux bugs, réutilisable sur d'autres applications web. Applique-le au projet Pebbl.

Phase 1 — Recherche parallèle
- Identifie les composants et points d'entrée du projet.
- Répartis leur analyse entre plusieurs sous-agents indépendants.
- Recherche les bugs fonctionnels, défauts de validation, failles de sécurité et problèmes de protection des données.

Phase 2 — Vérification contradictoire
- Fais examiner les anomalies potentielles par d'autres sous-agents.
- Ils doivent essayer de réfuter chaque anomalie en examinant le code et son contexte.
- Écarte les faux positifs et regroupe les doublons.

Phase 3 — Nouvelle passe
- Si des zones restent insuffisamment examinées, lance une seconde investigation ciblée.
- Arrête après deux passes maximum.

Résultat
Produis un rapport priorisé : problème | fichier concerné | preuve | gravité | correction proposée.

Contraintes :
- Ne modifie pas le code de l'application. La création du workflow et du rapport est autorisée.
- Limite à 5 sous-agents maximum et à 2 passes d'analyse.
- N'installe aucune dépendance.
- Distingue les anomalies confirmées par le code des problèmes qui nécessiteraient des tests d'exécution.
```
