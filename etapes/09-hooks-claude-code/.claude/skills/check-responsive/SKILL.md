---
description: Teste le responsive design d'une ou plusieurs pages HTML via Playwright. Vérifie les débordements horizontaux, la lisibilité, la navigation et les éléments interactifs à 320px, 375px, 768px et 1440px.
argument-hint: [fichier-html] [--fix]
disable-model-invocation: false
user-invocable: true
---

# Check Responsive

Tu testes le responsive design d'une ou plusieurs pages HTML avec le MCP Playwright.

## Entrée

- Le fichier HTML à tester est `$0` (optionnel).
- Si aucun fichier n'est fourni, trouve toutes les pages HTML du projet avec Glob (`**/*.html`) depuis le répertoire de travail courant, et teste-les toutes.
- Si `--fix` est présent dans `$ARGUMENTS`, applique les corrections simples et sûres après le test.
- Sinon, ne modifie aucun fichier.

## Largeurs testées

| Alias | Largeur | Représente |
|---|---|---|
| `xs`  | 320px  | Petit smartphone (iPhone SE) |
| `sm`  | 375px  | Smartphone standard (iPhone 14) |
| `md`  | 768px  | Tablette portrait |
| `xl`  | 1440px | Desktop large |

## Méthode

Pour chaque fichier HTML à tester :

1. Construis l'URL absolue `file:///` à partir du chemin absolu du fichier (transforme les `\` en `/` sous Windows).

2. Pour chaque largeur dans [320, 375, 768, 1440] :

   a. Navigue vers la page avec `browser_navigate`.

   b. Redimensionne la fenêtre avec `browser_resize` (largeur cible, hauteur : 900px).

   c. **Débordement horizontal global** — détecte si la page scroll horizontalement :
      ```js
      (() => {
        const html = document.documentElement;
        return {
          overflow: html.scrollWidth > html.clientWidth,
          scrollWidth: html.scrollWidth,
          clientWidth: html.clientWidth
        };
      })()
      ```

   d. **Éléments qui dépassent** — liste les sélecteurs qui sortent du viewport :
      ```js
      (() => {
        const cw = document.documentElement.clientWidth;
        const offenders = [];
        document.querySelectorAll('*').forEach(el => {
          const r = el.getBoundingClientRect();
          if (r.right > cw + 2) {
            const sel = el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/).join('.') : '');
            offenders.push(sel);
          }
        });
        return [...new Set(offenders)].slice(0, 8);
      })()
      ```

   e. **Lisibilité** — texte sous 12px :
      ```js
      (() => {
        const issues = [];
        document.querySelectorAll('p, li, span, a, h1, h2, h3, h4, label').forEach(el => {
          const size = parseFloat(window.getComputedStyle(el).fontSize);
          const text = el.innerText ? el.innerText.trim() : '';
          if (size < 12 && text.length > 2) {
            issues.push({ tag: el.tagName, size: size + 'px', text: text.slice(0, 40) });
          }
        });
        return issues.slice(0, 5);
      })()
      ```

   f. **Boutons et liens** — zone de touche < 44×44 px :
      ```js
      (() => {
        const small = [];
        document.querySelectorAll('a, button').forEach(el => {
          const r = el.getBoundingClientRect();
          const text = el.innerText ? el.innerText.trim() : '';
          if ((r.width < 44 || r.height < 44) && text.length > 0) {
            small.push({ tag: el.tagName, text: text.slice(0, 30), w: Math.round(r.width), h: Math.round(r.height) });
          }
        });
        return small.slice(0, 5);
      })()
      ```

   g. **Navigation** — visible, non débordante :
      ```js
      (() => {
        const nav = document.querySelector('nav');
        if (!nav) return { found: false };
        const style = window.getComputedStyle(nav);
        const r = nav.getBoundingClientRect();
        return {
          found: true,
          visible: style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0',
          overflows: r.right > document.documentElement.clientWidth + 2
        };
      })()
      ```

   h. **Formulaires** (si présents) — champs qui débordent :
      ```js
      (() => {
        const forms = document.querySelectorAll('form');
        if (!forms.length) return { found: false };
        const cw = document.documentElement.clientWidth;
        const issues = [];
        document.querySelectorAll('input, textarea, select').forEach(f => {
          const r = f.getBoundingClientRect();
          if (r.right > cw + 2) issues.push(f.id || f.name || f.tagName);
        });
        return { found: true, issues };
      })()
      ```

   i. Si au moins un problème est détecté à cette largeur, prends un screenshot avec `browser_take_screenshot` pour illustrer.

3. Consulte `${CLAUDE_SKILL_DIR}/reference.md` comme checklist complémentaire.

## Sortie sans `--fix`

Affiche un tableau récapitulatif, **une ligne par problème trouvé** :

| Page | Largeur | Catégorie | Problème | Correction recommandée |
|---|---|---|---|---|

Si aucun problème n'est détecté : "Aucun problème responsive aux 4 largeurs testées."

## Sortie avec `--fix`

Applique uniquement les corrections simples et sûres, sans toucher à la mise en page ni aux media queries existantes :

- **Débordement global** → ajoute `overflow-x: hidden` sur `body` dans le bloc `<style>` existant.
- **Images sans max-width** → ajoute `max-width: 100%; height: auto;` dans la règle `img` existante ou en crée une.
- **Bouton/lien trop petit** → ajoute `min-height: 44px` sur le sélecteur concerné si identifiable sans ambiguïté.
- **Ne jamais** modifier la grille, les positions absolues, les media queries existantes, ni les valeurs de couleur.

Résume les fichiers modifiés et les corrections appliquées.
