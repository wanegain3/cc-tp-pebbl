---
description: Ajoute un panneau de préférences cookies en bas à gauche du front-end courant.
allowed-tools: Read, Glob, Grep, Edit, Write
---

# Ajouter un panneau de préférences cookies

Analyse le front-end du projet courant et ajoute une interface de choix des cookies visible en bas à gauche.

Objectif visuel :
- afficher une petite boîte fixe en bas à gauche ;
- rester cohérent avec le design existant ;
- proposer une interface compacte, lisible et responsive.

Contenu attendu :
- titre court, par exemple « Préférences cookies » ;
- une courte explication ;
- catégorie « Nécessaires » toujours active et non désactivable ;
- catégorie « Préférences » activable/désactivable ;
- catégorie « Mesure d'audience » activable/désactivable ;
- boutons « Tout accepter », « Refuser les optionnels » et « Enregistrer mes choix ».

Comportement :
- enregistrer le choix dans `localStorage` ;
- ne plus afficher automatiquement le panneau après un choix enregistré ;
- prévoir un petit bouton ou lien discret « Cookies » permettant de rouvrir le panneau ;
- rendre tous les contrôles accessibles au clavier ;
- ne charger aucune librairie externe ;
- ne connecter aucun tracker réel : cette commande crée l'interface et la gestion locale des préférences uniquement.

Contraintes :
- commence par identifier les fichiers réellement utilisés par l'interface ;
- modifie le minimum de fichiers nécessaire ;
- ne supprime aucun contenu existant ;
- respecte les styles déjà présents autant que possible.

Important :
Cette interface de démonstration ne constitue pas à elle seule une mise en conformité juridique. Ne prétends pas qu'elle rend le site conforme au RGPD/CNIL.

À la fin :
1. indique les fichiers modifiés ;
2. résume le fonctionnement ;
3. explique comment réinitialiser le choix pour retester le panneau.
