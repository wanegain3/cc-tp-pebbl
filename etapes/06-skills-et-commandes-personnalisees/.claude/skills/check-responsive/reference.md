# Checklist responsive — HTML/CSS vanilla

Source : WCAG 2.1 (1.4.4 Resize text, 1.4.10 Reflow), bonnes pratiques mobile-first.

---

## 1. Débordements horizontaux

- [ ] Aucun scroll horizontal à aucune largeur.
- [ ] `body` et `html` ne dépassent pas `100vw`.
- [ ] Les images ont `max-width: 100%` (ou héritent d'un conteneur contraint).
- [ ] Les tableaux larges ont un conteneur avec `overflow-x: auto`.
- [ ] Les éléments en `position: absolute` ou `fixed` ne créent pas de débordement.
- [ ] Les chaînes longues sans espace (URL, code, hash) ont `word-break: break-word` ou `overflow-wrap: anywhere`.

---

## 2. Lisibilité

- [ ] La taille de police minimale est de 14px pour le corps de texte.
- [ ] Les textes ne sont pas tronqués (`overflow: hidden` sans `text-overflow: ellipsis` visible).
- [ ] Le `line-height` reste lisible sur mobile (≥ 1.4).
- [ ] Les titres ne débordent pas de leur conteneur sur petit écran.
- [ ] Le contraste texte/fond respecte un ratio ≥ 4,5:1 (texte normal) ou 3:1 (grand texte).

---

## 3. Navigation

- [ ] La navigation principale est accessible sur mobile (visible ou masquée avec alternative fonctionnelle).
- [ ] Si la nav est masquée sous `display: none` sur mobile, un menu burger ou équivalent est présent.
- [ ] Les liens de la nav ne se chevauchent pas ni ne débordent.
- [ ] Le logo est visible et cliquable à toutes les largeurs.
- [ ] La barre de nav sticky ne masque pas le contenu lorsqu'on navigue par ancre.

---

## 4. Boutons et liens

- [ ] La zone de touche minimale est de 44×44 px (WCAG 2.5.5).
- [ ] Les boutons ne se chevauchent pas sur petits écrans.
- [ ] Les groupes de boutons côte à côte ont `flex-wrap: wrap` pour passer à la ligne.
- [ ] Les liens inline restent distinguables du texte courant (couleur ou soulignement).
- [ ] Les boutons CTA restent entièrement visibles et cliquables à 320px.

---

## 5. Formulaires

- [ ] Les champs `<input>`, `<textarea>` et `<select>` n'excèdent pas `100%` de leur conteneur.
- [ ] Les `<label>` restent visibles et lisibles sur mobile.
- [ ] Les boutons de soumission sont suffisamment larges sur mobile (≥ 44px de hauteur).
- [ ] Les messages d'erreur et d'aide ne débordent pas du viewport.
- [ ] Les champs de type `date`, `email`, `tel` déclenchent le bon clavier virtuel (attribut `type` correct).

---

## 6. Grille et mise en page

- [ ] Les grilles multi-colonnes (`grid`, `flex`) passent à 1 colonne sous 768px.
- [ ] Les marges intérieures (`padding-inline`) sont adaptées aux petits écrans (≥ 16px).
- [ ] Les images hero/banner ont une hauteur raisonnable sur mobile (pas > 100vh sans `overflow: hidden`).
- [ ] Les colonnes en `grid-template-columns: repeat(N, 1fr)` ont une media query ou un `min()` pour éviter les colonnes trop étroites.
- [ ] Les sections `position: sticky` ne bloquent pas le défilement ni ne cachent du contenu.
