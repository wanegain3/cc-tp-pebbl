# Audit de la landing page Pebbl

> Diagnostic réalisé en tant que lead designer SaaS B2B.
> Périmètre : `index.html`, croisé avec le brief produit (`docs/pebbl.md`),
> les contraintes de marque (`CLAUDE.md`), les bonnes pratiques
> (`docs/landing-best-practices-max.md`) et les assets `/img/`.
> **Aucun fichier n'a été modifié.** Ce document est uniquement un diagnostic.

---

## 1. Synthèse

La landing est **visuellement aboutie et parfaitement alignée sur le ton de marque** : calme, premium, sensoriel. La direction artistique (palette pierre/encre + or, typographie légère, espacements généreux) traduit fidèlement l'univers Pebbl, et la narration *problème → solution → bénéfices → usage* est claire et agréable à parcourir. Sur le plan « identité de marque », la page réussit son pari.

En revanche, **en tant que page de conversion, elle est nettement incomplète**. Elle échoue sur plusieurs piliers fondamentaux des bonnes pratiques : aucune preuve sociale, aucun signal de confiance, aucun prix, aucune réassurance, aucune réponse aux objections, et un titre poétique qui ne passe pas le test des 5 secondes. La section censée apporter la « preuve » (commentaire `6. PREUVE` dans le code) ne contient en réalité aucune preuve — seulement un scénario d'usage.

**Verdict :** belle vitrine de marque, mais pas encore une machine à convertir. Elle donne envie de *regarder* Pebbl, pas encore de *l'acheter*. Le potentiel est élevé : l'ossature est saine, il manque les briques de conviction et de réassurance.

**Score indicatif vs checklist d'audit :** environ **8 / 24** critères satisfaits (détail en section 5).

---

## 2. Points forts

- **Cohérence de marque exemplaire.** Le ton respecte le brief à la lettre : « présence discrète », « objet de transition », « ce n'est pas une lampe, c'est un signal ». Le vocabulaire de marque (lumière, présence, intention, rythme, focus, rituel) est bien employé.
- **Respect strict des contraintes de discours.** Aucune promesse médicale (pas de « réduit le stress », « améliore le sommeil »). Le mode adaptif est décrit avec la formulation recommandée (« tient compte de l'heure, de la lumière ambiante et des signaux de vos appareils ») et non avec un langage de surveillance. C'est un point critique bien maîtrisé.
- **Structure narrative solide.** L'enchaînement hero → problème (fond sombre, rupture visuelle) → solution → bénéfices → ambiances → usage suit le schéma *problème → solution → action* préconisé.
- **La section « Une journée avec Pebbl » (9h/12h30/18h/22h)** est un excellent ressort de storytelling : elle rend l'objet concret et désirable mieux qu'une liste de specs.
- **Qualité visuelle et design system propre.** Variables CSS cohérentes, typographie hiérarchisée, micro-interactions soignées (hover, scroll reveal), responsive géré (`@media 860px`).
- **Les trois ambiances** (Concentration/bleu, Zen/doré, Énergie/rouge) sont bien matérialisées par la couleur, fidèles au brief, avec des visuels dédiés.
- **CTA primaire cohérent** : « Pré-commander » est repris du hero au CTA final, avec une hiérarchie claire (bouton sombre plein vs. outline).
- **Page légère** (un seul fichier HTML/CSS, pas de framework lourd) : bon socle pour la performance.

---

## 3. Points faibles

Classés du plus structurant au plus mineur.

### 3.1 Aucune preuve sociale (critique)
La page ne contient **aucun** témoignage, logo, note agrégée, avis, chiffre d'adoption ou badge. Les bonnes pratiques en font un pilier (« social proof stratifié », « 83 % des utilisateurs font confiance aux sources identifiables »). Pour un objet premium pré-commandé par des inconnus, l'absence totale de réassurance par les pairs est le frein de conversion n°1.

### 3.2 Aucun prix ni réassurance (critique)
On demande de « pré-commander » sans jamais afficher **un prix, une fourchette, une date de livraison, une garantie ou une politique de retour**. Aucune micro-copy de réassurance près des CTA (« sans engagement », « livraison incluse », « satisfait ou remboursé », mentions RGPD/paiement sécurisé). C'est une friction majeure sur l'acte d'achat.

### 3.3 Le titre ne passe pas le test des 5 secondes
« Votre énergie change. L'espace suit. » est un **slogan**, pas une proposition de valeur. Il est beau mais n'explique ni *ce que c'est*, ni *pour qui*, ni *pourquoi maintenant*. L'eyebrow « Objet lumineux connecté » corrige partiellement, mais un visiteur froid ne comprend pas immédiatement le bénéfice concret. La copie est orientée *ambiance*, jamais *résultat*.

### 3.4 La « preuve » n'en est pas une
Le commentaire de section indique `6. PREUVE / USAGE`, mais le contenu est un scénario d'usage, pas une preuve. La page promet structurellement une preuve qu'elle ne délivre jamais (ni démo chiffrée, ni étude de cas, ni avis).

### 3.5 Aucune FAQ / traitement des objections
Pas de section répondant aux questions réelles d'un acheteur : compatibilité (iOS/Android/montres), autonomie/alimentation, dimensions, connectivité, garantie, données personnelles. Un prospect convaincu à 80 % part faute de réponse.

### 3.6 Le système à deux modes est sous-exploité
Le brief présente le mode manuel et le mode adaptif comme une fonctionnalité produit centrale (section 8). La page n'évoque le mode adaptif qu'en passant (bénéfice 03) et **n'explique jamais le mode manuel**. Les assets `mode_manuel.png` et `mode_adaptif.png` existent mais **ne sont pas utilisés**.

### 3.7 Friction & technique
- Le **scroll reveal met `opacity: 0` par défaut** : si le JS échoue ou est lent, une grande partie du contenu reste invisible. Risque sur la robustesse et potentiellement sur le LCP.
- Images en **PNG** (le hero notamment) au lieu de WebP/AVIF recommandés → poids et LCP dégradés sur mobile.
- Navigation sticky complète conservée : acceptable pour une page organique, mais à supprimer/réduire si la page sert des campagnes payantes.

---

## 4. Incohérences

| # | Incohérence | Localisation | Détail |
|---|---|---|---|
| 1 | **« Pré-commander » vs « Disponible dès maintenant »** | CTA hero/nav vs. eyebrow section CTA finale | On invite à *pré-commander* (futur) tout en annonçant *Disponible dès maintenant*. Contradiction directe sur le statut commercial. |
| 2 | **Faute d'orthographe** | Section usage, carte 9h00 | « L'espace **signal** que c'est l'heure » → devrait être « **signale** ». |
| 3 | **Étiquetage de section trompeur** | Commentaire `6. PREUVE / USAGE` | La section est annoncée comme preuve mais ne contient aucune preuve. |
| 4 | **« Fonctionnalités » vs « Ambiances »** | Nav (`Ambiances`) → section `#fonctions` (eyebrow « Fonctionnalités ») | Le label de nav et l'intitulé de la section ne coïncident pas tout à fait. |
| 5 | **« Ambiances focus » incluant Zen et Énergie** | Section fonctionnalités | Méditation (Zen) et sport (Énergie) sont rangés sous « focus ». Hérité du brief, mais conceptuellement un peu étiré. |
| 6 | **Renommage Méditation → Zen** | Section ambiances | Le brief parle de « méditation » ; la page affiche « Zen ». Divergence mineure de nomenclature à arbitrer. |
| 7 | **Multiplicité des signatures** | Title, hero, footer, CTA | « Votre énergie change. L'espace suit. » / « Changez d'état, pas d'espace. » / « La lumière comme rituel quotidien ». Toutes issues du brief, mais leur cumul dilue le message principal. |
| 8 | **Assets disponibles non utilisés** | `/img/` | `mode_manuel.png` et `mode_adaptif.png` sont fournis mais absents de la page. |

---

## 5. Améliorations prioritaires

Classées par impact conversion décroissant. Les 4 premières sont les leviers à fort effet.

### Priorité 1 — Ajouter des signaux de confiance et de la preuve sociale
- Bandeau de réassurance sous le hero (ex. note agrégée, nombre de pré-commandes/early adopters, mention presse design).
- 2–3 témoignages avec prénom + rôle (télétravailleur, créatif, indépendant — la cible du brief), idéalement avec un verbatim sensoriel concret.
- Transformer la section `#usage` en véritable section preuve, ou ajouter une section dédiée juste avant le CTA final.

### Priorité 2 — Lever la friction d'achat
- Afficher **un prix ou une fourchette**, une **date de disponibilité/livraison**, ce qui est inclus.
- Ajouter une **micro-copy de réassurance** sous chaque CTA : garantie, retour, paiement sécurisé, RGPD.
- **Trancher « pré-commande » vs « disponible »** et harmoniser tous les libellés (corrige l'incohérence n°1).

### Priorité 3 — Réécrire le hero pour passer le test des 5 secondes
- Conserver le slogan en eyebrow/baseline, mais ajouter un **H1 ou un sous-titre explicite** : *ce que c'est*, *pour qui*, *bénéfice concret*. S'appuyer sur les éléments de langage du brief (« Une lumière pour chaque état d'esprit », « Pour travailler, bouger, respirer — sans changer d'objet »).
- Nommer explicitement la cible (télétravailleurs, créatifs, indépendants sensibles au design).

### Priorité 4 — Ajouter une FAQ / traitement des objections
- 4 à 6 questions : compatibilité smartphone/montre, alimentation & autonomie, dimensions/matériaux, connectivité, données personnelles (réaffirmer « pas de surveillance »), garantie/retour.

### Priorité 5 — Exploiter le système à deux modes
- Ajouter une courte section **Mode manuel / Mode adaptif** avec `mode_manuel.png` et `mode_adaptif.png`, en reprenant la formulation anti-surveillance du brief. Cela valorise une fonctionnalité produit clé aujourd'hui invisible.

### Priorité 6 — Fiabilité & performance
- Rendre le contenu **visible sans JS** (animer via une classe `.js` sur `<html>`, ou prévoir un fallback `no-js`) pour ne jamais masquer le contenu si le scroll reveal échoue.
- Convertir les images en **WebP/AVIF** et viser un **LCP < 2,5 s** sur mobile (hero en priorité).
- Vérifier que les zones tactiles des CTA atteignent **≥ 44 × 44 px**.

### Priorité 7 — Corrections de cohérence rapides
- Corriger « signal » → « **signale** ».
- Aligner le label de nav et l'intitulé de la section ambiances.
- Rationaliser les signatures pour une **promesse principale unique**, les autres en appui.

---

### Critère de réussite visé
Une fois ces recommandations appliquées, la landing devrait satisfaire la majorité de la checklist d'audit des bonnes pratiques — notamment sur **clarté du message**, **preuve sociale**, **réassurance** et **traitement des objections**, qui sont aujourd'hui les blocs manquants — tout en préservant la qualité de marque, qui est déjà son principal atout.
