# V - Skills et commandes personnalisees

Création de :
- ./.claude/skills/ avec 2 skills check-accessibility et check-responsive
- ./claude/commands/ avec une commande check-summary

## 5.1 - Generation de la reference du skill check-accessibility

```text
Utilise Context7 pour consulter les bonnes pratiques actuelles d'accessibilite HTML.
Cree un fichier `.claude/skills/check-accessibility/reference.md`.
Le fichier doit etre une checklist courte et pratique pour auditer une page HTML/CSS/JS vanilla.
Couvre uniquement : structure de page, titres, textes, images, liens et boutons, formulaires, attributs ARIA.
Le document doit rester synthetique.
```

## 5.2 - Generation du skill check-responsive

```text
Cree un skill Claude Code `check-responsive` dans ce projet.

Il doit tester le responsive avec le MCP Playwright et est invocable par Claude et via `/check-responsive`.

Il accepte un fichier HTML optionnel en argument, qui correspond au fichier a tester.
Si aucun fichier n'est fourni, il teste toutes les pages HTML du projet.

Il teste 4 largeurs : 320px, 375px, 768px, 1440px.
Il verifie les debordements horizontaux, la lisibilite, la navigation, les boutons/liens et les formulaires si presents.

Sans `--fix`, il ne modifie rien et affiche un tableau : page, largeur, resultat, probleme, correction recommandee.
Avec `--fix`, il applique seulement les corrections simples et sures.
```

## 5.3 - Creation de la commande project-summary

```text
Cree une commande personnalisee nommee `project-summary`.

Objectif : la commande doit demander a Claude de resumer rapidement le projet courant.

Contenu attendu :
- resumer en quelques lignes le role du projet
- lister les pages ou fichiers principaux
- indiquer les prochaines actions possibles
```
