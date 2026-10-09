---
description: Ajoute un bouton flottant clair/sombre au front-end courant.
allowed-tools: Read, Glob, Grep, Edit, Write
---

# Ajouter un mode sombre / clair

Analyse le front-end du projet courant et implémente un sélecteur de thème clair/sombre simple et propre.

Objectif visuel :
- afficher un petit bouton flottant sur le bord droit de l'écran ;
- le bouton doit rester visible au scroll ;
- afficher une icône ou un libellé explicite permettant de basculer entre mode clair et mode sombre ;
- respecter au maximum le design existant.

Comportement :
- adapter les couleurs de fond, texte, cartes, bordures, boutons et formulaires quand c'est pertinent ;
- ne pas casser les styles existants ;
- utiliser de préférence des variables CSS ou une classe/attribut global de thème ;
- mémoriser le choix dans `localStorage` ;
- au premier chargement, respecter `prefers-color-scheme` si aucun choix n'a encore été enregistré ;
- rendre le bouton accessible au clavier et lui donner un libellé ARIA pertinent ;
- éviter toute dépendance externe.

Contraintes :
- commence par identifier les fichiers réellement utilisés par l'interface ;
- modifie le minimum de fichiers nécessaire ;
- ne change pas la structure fonctionnelle du projet ;
- ne supprime aucun contenu existant ;
- ne lance pas de framework ou de dépendance supplémentaire.

À la fin :
1. indique les fichiers modifiés ;
2. résume en quelques lignes le fonctionnement ;
3. indique comment tester rapidement le bouton.
