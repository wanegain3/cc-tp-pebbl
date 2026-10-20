# VIII - Plugins

Le chapitre presente surtout des commandes et manipulations d'installation de plugins.
Ils sont installés au niveau utilisateur, mais ./.claude/settings.json a aussi été modifié.

## 8.1 - Test de poneytail

Ajout : 
```text 
/plugin marketplace add DietrichGebert/ponytail
```

Mode ultra : 
```text 
/ponytail ultra
```

Prompt :
```text
Analyse ce projet et propose 3 simplifications qui réduisent le code ou la complexité sans supprimer de fonctionnalité
```

Ponytail review : 
```text 
/ponytail-review
```

## 8.2 - Marketplace interne

Ajout de la marketplace interne : 
```text 
claude plugin marketplace add repo-ou-chemin
```

Installation du plugin darkmode de la markeplace interne: 
```text 
claude plugin install darkmode@marketplace-entreprise --scope project
```

