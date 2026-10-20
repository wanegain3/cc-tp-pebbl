# I - Introduction et installation

## 1.1 - Mise en place de Claude Code

Windows cmd : 
```text
curl -fsSL https://claude.ai/install.cmd -o install.cmd && install.cmd && del install.cmd
```

Windows powershell : 
```text
irm https://claude.ai/install.ps1 | iex
```

macOS / Linux / WSL : 
```text
curl -fsSL https://claude.ai/install.sh | bash
```

NodeJS : 
```text
npm install -g @anthropic-ai/claude-code 
```

## 1.2 - Mise en place de git

Vérification : 
```text
git --version 
```

Configuration : 
```text
git config --global user.name "Prénom Nom"
```

```text
git config --global user.email "email@example.com"
```

## 1.3 - Récupération des fichiers du cours

```text
cd /formation
```

```text
git clone https://github.com/wanegain3/cc-tp-pebbl.git cc-tp-pebbl
```

```text
cd cc-tp-pebbl
```