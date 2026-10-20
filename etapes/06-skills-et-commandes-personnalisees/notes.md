# VI - Skills et commandes personnalisees

Création de :
- ./.claude/skills/ avec 2 skills check-accessibility et check-responsive
- ./claude/commands/ avec une commande check-summary

## 6.1 - Création du skill check-accessibility

### 1ère partie : SKILL.md 
Cf. ./.claude/skills/check-accessibility/SKILL.md

### 2e partie : reference.md

Prompt pour générer ./.claude/skills/check-accessibility/reference.md :
```text
Utilise Context7, si disponible, pour consulter la documentation actuelle utile pour l’accessibilité HTML/CSS/JS vanilla.
Sinon, appuie-toi sur tes connaissances.
Cree un fichier `.claude/skills/check-accessibility/reference.md`.
Le fichier doit etre une checklist courte et pratique pour auditer une page HTML/CSS/JS vanilla.
Couvre uniquement : structure de page, titres, textes, images, liens et boutons, formulaires, attributs ARIA.
Le document doit rester synthetique.
```

## 6.2 - Generation du skill check-responsive

```text
Cree un skill Claude Code `check-responsive` dans ce projet.

Il doit vérifier le responsive avec le MCP Playwright et est invocable par Claude et via `/check-responsive`.

Il accepte un fichier HTML optionnel en argument, qui correspond au fichier a tester.
Si aucun fichier n'est fourni, il teste toutes les pages HTML du projet.

Il teste 4 largeurs : 320px, 375px, 768px, 1440px.
Il verifie les debordements horizontaux, la lisibilite, la navigation, les boutons/liens et les formulaires si presents.

Sans `--fix`, il ne modifie rien et affiche un tableau : page, largeur, resultat, probleme, correction recommandee.
Avec `--fix`, il applique seulement les corrections simples et sures.
```

## 6.3 - Installation et utilisation du skill archify

```text
npx skills find archify
```

```text
npx skills add tt-a1i/archify@archify -a claude-code -g
```

```text
Analyse ce repository puis utilise Archify pour générer un diagramme d’architecture haut niveau. Montre les
composants principaux, le flux principal de l’application, les dépendances externes; les frontières importantes. 
Garde le diagramme lisible et évite les détails inutiles. Utilise des noms de fichiers et de dossiers courts, sans horodatage, pour éviter les problèmes de longueur de chemin sous Windows.
```

## 6.4 - Installation et utilisation du skill frontend-design

Installation :
```text
Installe dans ce projet le skill frontend-design de https://github.com/anthropics/skills/tree/main/skills/frontend-design. 
Récupère ses fichiers dans .claude/skills/frontend-design/, sans utiliser Node.js, npm ou npx. Vérifie son installation.
```

Utilisation :
```text
/frontend-design Améliore le design de waitinglist.html pour lui donner une finition plus professionnelle. 
Respecte l'identité visuelle de Pebbl et le brief@docs/pebbl.md. 
Travaille particulièrement la hiérarchie visuelle,la typographie, les espacements et le formulaire.
Conserve les champs, les validations JavaScriptet les comportements existants.
Reste en HTML/CSS/JS vanilla, sans framework ni nouvelle dépendance.
```

## 6.5 - Creation de la commande project-summary

```text
Cree une commande personnalisee nommee `project-summary`.

Objectif : la commande doit demander a Claude de resumer rapidement le projet courant.

Contenu attendu :
- resumer en quelques lignes le role du projet
- lister les pages ou fichiers principaux
- indiquer les prochaines actions possibles
```

