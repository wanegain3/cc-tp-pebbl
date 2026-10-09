---
description: Audite l’accessibilité d’une page HTML selon les éléments présents. À utiliser pour vérifier une page, un formulaire, des images, des boutons, des liens ou des messages dynamiques.
argument-hint: <fichier-html> [--fix]
disable-model-invocation: false
user-invocable: true
---

# Check Accessibility

Tu audites l’accessibilité d’une page HTML/CSS/JS.


## Entrée

- Le fichier à auditer est `$0`.
- Si aucun fichier n’est fourni, demande quel fichier auditer.
- Si `--fix` est présent dans `$ARGUMENTS`, applique les corrections simples.
- Sinon, ne modifie aucun fichier.

## Référence

Utilise `${CLAUDE_SKILL_DIR}/reference.md` comme checklist principale.

Si un élément important n’est pas couvert par cette référence et que Context7 est disponible, consulte Context7 pour compléter l’analyse.

## Méthode

1. Lis le fichier HTML indiqué.
2. Identifie les éléments présents dans ce fichier :
   - titres
   - images
   - liens
   - boutons
   - formulaires
   - etc.
3. Audite l’accessibilité des éléments présents dans la page.
4. Classe les problèmes en trois niveaux : bloquant, important, amélioration.
5. Si `--fix` est demandé, corrige seulement les problèmes simples et sûrs.

## Sortie

Sans `--fix`, retourne un tableau :

| Priorité | Élément | Problème | Correction recommandée |
|---|---|---|---|

Avec `--fix`, applique les corrections ciblées, puis résume les fichiers modifiés.