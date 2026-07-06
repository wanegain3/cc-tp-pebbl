# Plan d'amélioration — Landing page Pebbl

> Basé sur `docs/audit-opus.md` (score actuel : 8/24) et `docs/landing-best-practices-max.md`.
> Aucun fichier modifié à ce stade.

---

## Arbitrages nécessaires avant exécution

Ces trois points nécessitent une décision avant de toucher au code :

| # | Question | Options |
|---|---|---|
| A | **Statut commercial** : "Pré-commander" (nav/hero/CTA) contredit "Disponible dès maintenant" (eyebrow CTA final). | (1) Tout passer à "Pré-commander" · (2) Tout passer à "Commander" si le produit est dispo |
| B | **Preuve sociale** : le produit est fictif, quelle forme de proof adopter ? | (1) Compteur d'early adopters ("1 200 early adopters") · (2) Témoignages fictifs avec persona · (3) Mentions presse design fictives · (4) Mix des trois |
| C | **Prix** : afficher un prix ou pas ? | (1) Prix unique fictif (ex. 149 €) · (2) Fourchette ("à partir de 129 €") · (3) Pas de prix, micro-copy "sans engagement" suffit |

---

## Quick wins (corrections rapides, sans risque)

Ces changements sont ponctuels et n'affectent pas la structure de la page.

### QW-1 — Correction de faute (ligne 907)
`"L'espace signal que c'est l'heure"` → `"L'espace signale que c'est l'heure"`

### QW-2 — Cohérence nav / section ambiances
Le lien de navigation dit `"Ambiances"` mais l'eyebrow de la section dit `"Fonctionnalités"`. Aligner les deux sur `"Ambiances"`.

### QW-3 — Fallback scroll reveal (no-JS)
Ajouter en CSS :
```css
.no-js .reveal { opacity: 1; transform: none; }
```
Et en `<head>` :
```html
<script>document.documentElement.classList.remove('no-js')</script>
```
Avec la classe `no-js` sur `<html>` par défaut. Ainsi, si le JS échoue, le contenu reste visible.

### QW-4 — Micro-copy de réassurance sous les CTAs
Ajouter sous chaque bloc CTA une ligne discrète :
> `Livraison incluse · Retour 30 jours · Paiement sécurisé`

---

## Améliorations structurantes (à fort impact conversion)

### S-1 — Hero : ajouter un sous-titre ancré dans la réalité (impact : élevé)
**Problème :** le H1 "Votre énergie change. L'espace suit." est poétique mais ne passe pas le test des 5 secondes — un visiteur froid ne sait pas ce que c'est, pour qui, ni pourquoi maintenant.

**Ce qui change dans `index.html` :**
- Conserver le H1 tel quel (identité de marque préservée)
- Ajouter sous la `.lead` existante un court sous-titre explicite en format eyebrow ou petit texte :
  > *"Pour les télétravailleurs, créatifs et indépendants qui cherchent à mieux marquer leurs transitions — sans ajouter du bruit à leur espace."*
- Alternative plus courte : modifier le `.lead` pour y intégrer ce ciblage.

---

### S-2 — Bande de réassurance sous le hero (impact : élevé)
**Problème :** aucun signal de confiance au-dessus du fold.

**Ce qui change dans `index.html` :**
Ajouter une nouvelle `<section>` légère entre `#hero` et `#probleme`, contenant une barre horizontale avec 3 éléments :
- Un nombre (ex. "1 200 early adopters" ou "Noté 4,8/5")
- Une mention de presse (ex. "Vu dans Wallpaper · Dezeen · Monocle")
- Un badge de garantie (ex. "Retour 30 jours")

Style : fond blanc, texte en `--muted`, séparateurs `·`, sobre — ne casse pas l'univers Pebbl.

> **Dépend de l'arbitrage B** (quelle forme de proof social).

---

### S-3 — Témoignages utilisateurs (impact : élevé)
**Problème :** la section `#usage` est étiquetée "PREUVE" dans le code mais ne contient aucune preuve — c'est un scénario d'usage.

**Ce qui change dans `index.html` :**
Ajouter 2–3 cartes de témoignages **juste après la grille des 4 moments** (9h/12h30/18h/22h), avant le bloc "Application". Format :
- Citation (verbatim sensoriel, court, dans le ton Pebbl — ex. *"La transition bureau → canapé, je la ressens vraiment maintenant."*)
- Prénom + rôle (ex. *Margaux, designer indépendante · Paris*)
- Étoiles optionnelles

Style : fond `--off`, bordure légère, même gabarit que les `usage-card`.

> **Dépend de l'arbitrage B.**

---

### S-4 — Section Modes : Manuel & Adaptif (impact : moyen)
**Problème :** la fonctionnalité principale du produit (deux modes) n'est pas présentée visuellement. Les assets `mode_manuel.png` et `mode_adaptif.png` ne sont pas utilisés.

**Ce qui change dans `index.html` :**
Ajouter une nouvelle `<section id="modes">` entre `#fonctions` et `#usage`, avec une grille 2 colonnes :
- Colonne gauche : `mode_manuel.png` + titre "Mode manuel" + description (contrôle total, on choisit l'ambiance)
- Colonne droite : `mode_adaptif.png` + titre "Mode adaptif" + description (formulation anti-surveillance : "tient compte de l'heure, de la lumière ambiante et des signaux de vos appareils")

Ajouter le lien dans la nav ou en tant qu'ancre depuis la section bénéfices.

---

### S-5 — Section FAQ / traitement des objections (impact : élevé)
**Problème :** un prospect convaincu à 80 % peut partir faute de réponse sur la compatibilité, les dimensions, les données.

**Ce qui change dans `index.html` :**
Ajouter une `<section id="faq">` avant `#cta`, avec 5–6 accordéons (ou Q/R statiques si accordéon trop complexe) :

1. Compatible avec quels appareils ? (iOS, Android, Apple Watch, Garmin…)
2. Comment Pebbl est-il alimenté ? (câble USB-C, autonomie X heures en mode autonome)
3. Quelles sont ses dimensions et matériaux ? (dimensions, matériau pierre/béton…)
4. Comment fonctionne le mode adaptif — mes données sont-elles collectées ? (non — Pebbl traite les signaux localement, aucune donnée envoyée)
5. Quelle garantie et politique de retour ? (30 jours, garantie 2 ans)
6. À partir de quand est-il livré ? (dépend de l'arbitrage A)

Style : fond `--white`, questions en `h3`, réponses en `p.body`, séparateur `--stone`.

---

### S-6 — Prix ou réassurance tarifaire (impact : élevé)
**Problème :** on demande de "pré-commander" sans jamais mentionner de prix.

**Ce qui change dans `index.html` :**
- Dans le `#cta` final, ajouter juste au-dessus des boutons : un prix affiché (ex. `149 €`) ou une ligne `"À partir de 129 €"`.
- Ajouter la micro-copy de réassurance (QW-4) sous les boutons.

> **Dépend de l'arbitrage C.**

---

## Ordre d'exécution recommandé

```
1. Arbitrages A, B, C  ← à valider d'abord
2. QW-1, QW-2, QW-3, QW-4  ← corrections rapides, toujours valables
3. S-1  ← hero clarté
4. S-2  ← bande de réassurance
5. S-3  ← témoignages
6. S-4  ← section modes (utilise les assets disponibles)
7. S-5  ← FAQ
8. S-6  ← prix / réassurance tarifaire
```

---

## Ce qui ne change PAS

- Le design system (variables CSS, palette, typographie) : intact
- Le ton de marque : calme, premium, sensoriel — aucune promesse médicale
- La structure narrative existante (héro → problème → solution → bénéfices → ambiances → usage → CTA)
- Les 7 sections existantes : on ajoute des contenus, on n'en supprime pas
- Le responsive `@media 860px` : les nouvelles sections l'hériteront
