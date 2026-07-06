# IV - MCP

Création de :
- waitinglist.html
- .mcp.json pour la connexion au MCP playwright
- connexion au MCP Context7 au niveau utilisateur (hors projet)

Amélioration de index.html

## 4.1 - Amelioration du hero avec Playwright

```text
Ameliore le haut de page de @index.html.

La 1ere grande image a un cote gauche vide, destine a y superposer du texte.
Scindes le bandeau hero en 2 parties. L'une est conservee, et l'autre est superposee a l'image, sur sa partie gauche.

Verifie le rendu en utilisant le MCP Playwright et corriges si ca n'est pas design, pas accessible, ou non-recommande pour les landing pages.
```

## 4.2 - Creation de la page waitinglist.html

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

Comportement attendu, a verifier en utilisant le MCP Playwright :
- au chargement, le formulaire est visible et le message de confirmation est masque
- si la validation echoue, afficher les erreurs et garder le formulaire visible
- si la validation reussit, masquer le formulaire et afficher le message de succes

Lie les pages index.html et waitinglist.html de maniere coherente.
```

## 4.3 - Amelioration accessibilite avec Context7

```text
Utilise le MCP Context7 pour consulter la documentation actuelle sur les bonnes pratiques d'accessibilite des formulaires HTML.
Ensuite, ameliore le formulaire de @waitinglist.html pour les suivre.
```
