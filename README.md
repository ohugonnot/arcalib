# Arcalib — Logiciel de gestion d'unité de recherche clinique

> ⚠️ **Projet archivé — non maintenu.** Développé en 2019 sous Symfony 3.4 (LTS à l'époque), ce repo n'est plus activement suivi. Les dépendances n'ont pas été mises à jour depuis. À ne pas utiliser en production.

Application web métier développée pour **ArcOffice**, permettant la gestion complète d'une unité de recherche clinique : suivi des patients, des protocoles d'essais, des intervenants et des documents réglementaires.

## Stack

- **Symfony 3.4** (LTS)
- **Doctrine ORM** — modélisation des entités cliniques
- **Twig** — templates
- **Swiftmailer** — notifications email
- **MySQL**

## Fonctionnalités principales

- Gestion des protocoles d'essais cliniques et de leurs étapes
- Suivi des patients inclus par protocole
- Gestion des rôles (investigateur principal, ARC, coordinateur)
- Workflow de validation documentaire
- Tableau de bord par unité

## Installation

```bash
composer install
# Configurer parameters.yml (BDD, mailer)
php bin/console doctrine:migrations:migrate
php bin/console assets:install --symlink
```

## Déploiement

```bash
bash deploy.sh
```

Le script exécute les migrations, vide le cache et rebuild les assets.
