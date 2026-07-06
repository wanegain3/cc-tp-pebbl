# VIII - Hooks Claude Code

Modification de ./.claude/settings.json avec une section hooks.
Ils sont désactivés avec "disabled": true.

## 8.1 - Prompt de hook pour bloquer les demandes trop larges

```text
Analyse le JSON recu dans $ARGUMENTS. Cherche le texte exact du prompt utilisateur.
Si le prompt demande de modifier du HTML, du CSS ou du JavaScript, bloque uniquement s'il ne contient aucun nom de fichier explicite.
Considere comme fichier explicite toute mention contenant .html, .css ou .js, avec ou sans @, par exemple index.html, @index.html, ./index.html, waitinglist.html.
Si un fichier est explicitement mentionne, reponds uniquement {"ok": true}.
Si aucun fichier n'est mentionne, reponds uniquement {"ok": false, "reason": "Precisez la page ou le fichier a modifier."}.
Pour tous les autres prompts, reponds uniquement {"ok": true}.
Ne retourne aucun texte hors JSON.
```

## 8.2 - Prompt de test du hook HTML

```text
Ajoute un commentaire tres leger dans index.html pour tester mon hook.
```

## 8.3 - Prompt bloque par le hook UserPromptSubmit

```text
Supprime les commentaires html.
```

## 8.4 - Prompt autorise par le hook UserPromptSubmit

```text
Supprime les commentaires de waitinglist.html.
```
