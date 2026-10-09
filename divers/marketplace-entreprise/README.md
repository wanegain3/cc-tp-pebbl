# Marketplace interne de démonstration

Marketplace de démonstration pour une formation Claude Code.

## Plugins

- `darkmode` : ajoute la commande `/darkmode:darkmode`
- `cookies` : ajoute la commande `/cookies:cookies`

## Ajouter la marketplace locale

Depuis Claude Code, utilisez le chemin absolu vers ce dossier, de préférence avec des `/` sous Windows :

```text
/plugin marketplace add C:/chemin/vers/marketplace-entreprise
```

Puis installez les plugins :

```text
/plugin install darkmode@marketplace-entreprise
/plugin install cookies@marketplace-entreprise
```

Redémarrez/rechargez la session Claude Code si nécessaire, puis utilisez :

```text
/darkmode:darkmode
/cookies:cookies
```

## Structure

```text
/marketplace-entreprise
├── /.claude-plugin
│   └── marketplace.json
└── /plugins
    ├── /darkmode
    │   ├── /.claude-plugin
    │   │   └── plugin.json
    │   └── /commands
    │       └── darkmode.md
    └── /cookies
        ├── /.claude-plugin
        │   └── plugin.json
        └── /commands
            └── cookies.md
```

Les commandes sont volontairement simples et demandent à Claude d'adapter l'implémentation au front-end réellement présent dans le projet.
