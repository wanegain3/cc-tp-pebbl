# III - Prompts, contexte et modeles

Création de :
- index.html
- .claude/settings.local.json (autorisations)
- /docs/audit-haiku.md
- /docs/audit-opus.md
- /docs/landing-best-practices-low.md
- /docs/landing-best-practices-max.md
- .claude/plan.md
- .claude/rules/html-css.md

## 3.1 - Landing page simple

```text
Genere la landing page de ce produit, index.html.
```

## 3.2 - Landing page detaillee

```text
Genere une landing page en francais pour Pebbl, index.html.

Objectif : presenter le produit decrit dans @docs/pebbl.md
Cible : particuliers CSP+
Style : clair, ressenti premium, a la Apple

Structure attendue :
- hero avec promesse claire
- probleme
- solution
- 3 benefices
- 3 fonctionnalites
- section preuve ou usage concret, sans faux temoignage
- CTA final

Contraintes : fichier HTML autonome avec CSS inclus.
```

## 3.3 - Bonnes pratiques landing page

```text
Tu joues le role d'un expert conversion et UX pour landing pages SaaS.

Objectif :
Etablir une liste generique de bonnes pratiques pour creer une landing page qui convertit.

Tache :
Recherche et synthetise les bonnes pratiques reconnues pour les landing pages SaaS B2B.

Contraintes :
- Ne parle pas du projet Pebbl.
- Ne modifie aucun fichier existant.
- Fais une nouvelle analyse sans prendre en compte les eventuelles autres versions de landing-best-practices.md
- Cite les sources consultees.
- Distingue les principes generaux des recommandations plus contextuelles.
- Evite les conseils vagues du type "faire un beau design".
- N'utilises pas /deep-research

Format attendu :
Cree un fichier docs/landing-best-practices.md avec les sections suivantes :
1. Synthese en 5 lignes
2. Sources consultees
3. Bonnes pratiques recurrentes
4. Structure type d'une landing efficace
5. Erreurs frequentes a eviter
6. Checklist d'audit
7. Points a adapter selon le produit
```

## 3.4 - Audit de landing page

```text
Tu joues le role d'un lead designer SaaS B2B.

Contexte :
Lis @index.html, @CLAUDE.md, @docs/pebbl.md, @docs/landing-best-practices-max.md et les images de @img.

Objectif :
Evaluer la pertinence de la landing. Reflete-t-elle clairement Pebbl ? Donne-t-elle envie de l'acheter ? Suit-elle les bonnes pratiques resumees ?

Tache :
Propose un diagnostic de la landing.

Contraintes :
- Ne modifie aucun fichier a ce stade.

Analyse attendue :
- ce qui fonctionne
- ce qui est faible
- les incoherences
- les ameliorations prioritaires

Criteres de reussite :
- la landing page modifiee selon tes recommandations devra suivre la plupart des bonnes pratiques

Format attendu :
Cree docs/audit.md avec les sections :
- 1. Synthese
- 2. Points forts
- 3. Points faibles
- 4. Incoherences
- 5. Ameliorations prioritaires
```

## 3.5 - Plan d'amelioration de la landing

```text
A partir de @docs/audit-opus.md et @docs/landing-best-practices-max.md, propose un plan d'amelioration de @index.html vers une nouvelle page indexV2.html.

Objectif :
Ameliorer la conversion sans denaturer l'univers Pebbl : calme, premium, sensoriel, pas medical, pas anxiogene.

Contraintes :
- Ne modifie aucun fichier pour l'instant.
- Priorise les changements a fort impact.
- Distingue quick wins et changements plus structurants.
- Explique ce que tu comptes modifier dans index.html.
- Signale les arbitrages necessaires avant execution.
```
