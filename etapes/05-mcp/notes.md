# V - MCP

Création de :
- waitinglist.html
- .mcp.json pour la connexion au MCP playwright
- connexion au MCP Context7 au niveau utilisateur (hors projet)
- docs/structure-projet.excalidraw

Amélioration de index.html

## 5.1 - Amelioration du hero avec Playwright

Installation de playwright :
```text
claude mcp add playwright --scope project -- npx -y @playwright/mcp@latest
```

Prompt :
```text
Ameliore le haut de page de @index.html.

La 1ere grande image a un cote gauche vide, destine a y superposer du texte.
Scinde le bandeau hero en 2 parties. L'une est conservee, et l'autre est superposee a l'image, sur sa partie gauche.

Verifie le rendu en utilisant le MCP Playwright et corrige si ca n'est pas design, pas accessible, ou non-recommande pour les landing pages.
```

## 5.2 - Creation de la page waitinglist.html

```text
Cree une page `waitinglist.html` pour la liste d'attente de Pebbl.

Elle doit se caler visuellement sur `index.html` et respecter les regles HTML/CSS du projet.

Le formulaire doit permettre de collecter :
- le prenom
- le nom
- l'email
- l'usage principal envisage
- le consentement a etre recontacte

Ajoute une validation front simple en JavaScript :
- champs obligatoires
- email valide
- affichage des erreurs
- message de succes sans rechargement de page

Comportement attendu (à verifier en utilisant le MCP Playwright si disponible) :
- au chargement, le formulaire est visible et le message de confirmation est masque
- si la validation echoue, afficher les erreurs et garder le formulaire visible
- si la validation reussit, masquer le formulaire et afficher le message de succes

Lie les pages index.html et waitinglist.html de maniere coherente.
```

## 5.3 - Utilisation de Context7

Installation de Context7 :
```text
claude mcp add --transport http --scope project context7 https://mcp.context7.com/mcp
```

Prompt :
```text
Explore uniquement le répertoire courant pour identifier les fichiers et dossiers présents.
Ne parcours pas les dépôts parents, frères ou externes.
Utilise Context7 uniquement pour consulter uniquement la documentation actuelle nécessaire afin de créer un
fichier .excalidraw valide.
Crée docs/structure-projet.excalidraw avec un schéma simple qui regroupe les éléments du répertoire courant en
grandes catégories cohérentes.
Contraintes :
- ne lis le contenu des fichiers que si c’est nécessaire pour les classer
- n’installe rien
- n’utilise pas Node.js ni de script
- ne lance pas de validation externe
- reste sur un schéma simple et lisible
- une fois le fichier créé, arrête-toi
```