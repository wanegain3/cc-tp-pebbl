# XI - Reprendre et ameliorer une codebase existante

Ce chapitre contient beaucoup de fichiers et dossiers générés.
Aussi ils ne sont pas listés ici.
Mais il y a essentiellement :
- l'ajout d'une documentation non technique
- l'ajout d'une documentation technique
- l'ajout de PHPDoc, PHPUnit, et composer
- une refactorisation d'une partie du code.

## 11.1 - Documentation d'architecture

```text
Cree docs/architecture.md pour un developpeur qui reprend Pebbl.

Inclue :
- la stack technique ;
- les fichiers principaux ;
- le flux formulaire -> API -> SQLite -> admin -> export CSV ;
- un diagramme Mermaid ;
- les risques a connaitre avant modification.

Ne modifie pas le code.
```

## 11.2 - Audit global de qualite avec agents

```text
En utilisant des agents, fais une analyse globale de la qualite de l'application avec 3 angles :
- securite PHP / donnees personnelles ;
- maintenabilite / duplication ;
- tests / risques de regression.

Fais une synthese priorisee en fin de reponse.
Ne modifie rien.
Mets a jour @docs/architecture.md avec ces insights.
```

## 11.3 - Documentation technique generable

```text
Mets en place une documentation technique generable pour le code PHP du projet.

Objectif :
- installer phpDocumentor avec Composer en dependance de developpement ;
- identifier les fonctions, modules ou points d'entree PHP importants ;
- ajouter des commentaires PHPDoc utiles uniquement la ou ils apportent une vraie comprehension ;
- generer une documentation technique dans un dossier dedie ;
- ajouter une commande simple composer docs et modifier CLAUDE.md pour qu'il la retienne
- creer ou mettre a jour une page docs/documentation.md expliquant :
  - a quoi sert la documentation generee ;
  - comment la regenerer ;
  - quels fichiers sont documentes ;
  - ce qui doit etre committe ou ignore ;
- ajouter une verification documentaire :
  - le hook pre-commit doit avertir si du PHP est commite sans modification de la documentation ;
  - la CI doit lancer composer docs pour verifier que la documentation generee fonctionne ;
  - le hook ne doit pas regenerer automatiquement la documentation.

Contraintes :
- Dans phpdoc.xml, prefixe tous les chemins relatifs avec ./ (./api, ./src, ./docs/api, etc.)
- ne documente pas chaque ligne ;
- ne commente pas les evidences ;
- privilegie les responsabilites, parametres, retours, erreurs possibles et effets de bord ;
- ne change pas le comportement de l'application ;
- ajoute au .gitignore les dossiers generes ou caches si necessaire ;
- conserve composer.json et composer.lock dans le depot ;
- verifie le workflow de deploiement CD : la documentation generee, vendor/ et les fichiers d'outillage ne doivent pas etre envoyes sur l'hebergement sauf raison explicite ;
- si phpDocumentor est trop lourd ou incompatible avec la version PHP locale, arrete-toi et propose une alternative avant de modifier.

Apres modification, lance composer docs, verifie la syntaxe PHP, puis montre-moi le diff.
```

## 11.4 - Tests automatises de la waitlist

```text
Mets en place des tests automatises pour le formulaire d'inscription / liste d'attente du projet.

Objectif :
- identifier le code PHP qui traite la soumission du formulaire ;
- extraire la logique de validation dans une fonction ou un module testable ;
- installer PHPUnit avec Composer en dependance de developpement ;
- creer des tests automatises pour verifier au minimum :
  - soumission valide ;
  - email invalide ;
  - usage / choix invalide ;
  - consentement manquant ;
  - champ obligatoire manquant ;
  - doublon eventuel si le projet le gere deja ;
- ajouter une commande simple composer test et modifier claude.md pour la retenir
- mettre a jour la CI pour lancer les tests apres composer install ;
- mettre a jour le hook pre-commit pour lancer les tests quand du PHP est modifie.

Contraintes :
- ne change pas le comportement visible du formulaire ;
- ne change pas le design ;
- ne change pas le schema de donnees sans me demander ;
- Composer sert ici aux outils de developpement, pas a la production ;
- ajoute au .gitignore ce qui ne doit pas etre versionne, notamment vendor/ et les sorties temporaires eventuelles ;
- conserve composer.json et composer.lock dans le depot ;
- verifie le workflow de deploiement CD : il ne doit pas envoyer vendor/, les tests, les caches ou les fichiers d'outillage de developpement sur l'hebergement ;

Lance les tests, verifie la syntaxe PHP, puis montre-moi le diff et resume les fichiers crees/modifies.
```

## 11.5 - Refactorisation des filtres waitlist

```text
Refactorise la logique de filtre de la waitlist lorsqu'elle est dupliquee dans plusieurs fichiers.

Objectif :
- centraliser cette logique dans un fichier partage sous admin/includes/ ;
- garder exactement le meme comportement visible ;
- ne pas changer le design ;
- ne pas ajouter de nouvelle fonctionnalite.

Apres modification :
- met a jour puis relance les tests ;
- verifie la syntaxe PHP ;
- montre-moi le diff ;
- explique brievement ce qui a ete deplace et pourquoi.
```
