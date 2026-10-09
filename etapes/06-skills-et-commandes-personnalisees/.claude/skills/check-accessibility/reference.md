# Checklist accessibilité — HTML/CSS/JS vanilla

Source : WCAG 2.2 (W3C). Couvre les critères les plus fréquemment défaillants en audit.

---

## 1. Structure de page et titres

- [ ] La page a un `<title>` descriptif et unique.
- [ ] Un seul `<h1>` par page, représentant le sujet principal.
- [ ] La hiérarchie des titres est logique (`h1` → `h2` → `h3`…) sans sauter de niveau.
- [ ] Les landmarks HTML5 sont utilisés : `<header>`, `<nav>`, `<main>`, `<footer>`, `<aside>`.
- [ ] Si plusieurs `<nav>` existent, chacun a un `aria-label` distinct.
- [ ] La langue principale est déclarée : `<html lang="fr">`.

---

## 2. Images

- [ ] Toute image porteuse d'information a un attribut `alt` descriptif.
- [ ] Les images décoratives ont `alt=""` (chaîne vide, pas absente).
- [ ] Une image-lien décrit la destination dans son `alt` (ex. : `alt="Accueil Pebbl"`).
- [ ] Les images de texte sont évitées ; si inévitables, le texte est reproduit dans `alt`.
- [ ] Les SVG inline ont `role="img"` et un `<title>` ou `aria-label`.

---

## 3. Liens et boutons

- [ ] Chaque `<a>` a un texte visible ou un `aria-label` décrivant sa destination.
- [ ] Les liens "Lire la suite" sont différenciés (`aria-label="Lire la suite sur [sujet]"`).
- [ ] Les `<button>` ont un libellé explicite ; les boutons icône ont un `aria-label`.
- [ ] Les actions déclenchées au clic utilisent `<button>`, pas `<div>` ou `<span>`.
- [ ] Le focus clavier est visible sur tous les liens et boutons (pas de `outline: none` sans alternative).
- [ ] L'ordre de tabulation suit l'ordre visuel logique.

---

## 4. Formulaires

- [ ] Chaque champ a un `<label>` lié via `for`/`id` (ou `aria-labelledby`).
- [ ] Les champs obligatoires sont signalés visuellement **et** avec `aria-required="true"` (ou `required`).
- [ ] Les messages d'erreur sont associés au champ via `aria-describedby`.
- [ ] Les groupes de champs liés (radio, cases à cocher) sont dans un `<fieldset>` avec `<legend>`.
- [ ] Le `placeholder` n'est pas le seul label (il disparaît à la saisie).
- [ ] Les erreurs de validation sont annoncées aux lecteurs d'écran (via `role="alert"` ou `aria-live`).

---

## 5. Attributs ARIA

- [ ] ARIA n'est utilisé que si le HTML natif ne suffit pas (préférer `<button>` à `role="button"`).
- [ ] Tout élément avec `role` a les attributs requis (`role="checkbox"` → `aria-checked`).
- [ ] `aria-hidden="true"` est appliqué aux éléments décoratifs pour les masquer aux AT.
- [ ] Les zones de contenu dynamique utilisent `aria-live="polite"` (ou `assertive` si urgent).
- [ ] Les modales/overlays piègent le focus et exposent `role="dialog"` + `aria-labelledby`.
- [ ] Les éléments interactifs custom ont `tabindex="0"` et gèrent les événements clavier (Enter/Espace).

---

## Outils de vérification rapide

| Outil | Usage |
|---|---|
| [axe DevTools](https://www.deque.com/axe/) | Extension navigateur, analyse automatique |
| [WAVE](https://wave.webaim.org/) | Visualisation des erreurs dans la page |
| Inspecteur d'accessibilité (DevTools) | Arbre d'accessibilité, propriétés ARIA |
| Navigation au clavier (Tab, Shift+Tab, Enter, Espace, Flèches) | Test manuel du focus et de l'ordre |
