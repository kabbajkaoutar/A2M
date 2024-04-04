# A2M

## Introduction
A2M (Application Manage Article) est une application Symfony qui gere les articles de différentes sources (API externes, flux RSS, fichiers locaux) puis les stocke en DB. En phase 2, une API REST permet d'y accéder.
## Prérequis
Avant de commencer, assurez-vous d'avoir rencontré les exigences suivantes :

- [PHP](https://www.php.net/) installé sur votre machine locale.
- [Composer](https://getcomposer.org/) installé sur votre machine locale.
- [Git](https://git-scm.com/) installé sur votre machine locale.
- [Symfony CLI](https://symfony.com/download) installé sur votre machine locale.

## Pour Commencer

Pour obtenir une copie locale opérationnelle, suivez ces étapes :

1. Cloner le dépôt :

   ```bash
   git clone https://github.com/kabbajkaoutar/A2M.git

2. Naviguer dans le répertoire du projet :

   ```bash
   cd A2M
3. Vérifier les prérequis de Symfony :
    ```bash
   symfony check:requirements
Assurez-vous que tous les prérequis sont satisfaits avant de continuer.

4. Installer les dépendances:
   ```bash
   composer install
5. Créer un fichier .env :
   ```bash
   cp .env.dist .env
6. Générer la clé secrète de l'application:
   ```bash
   php bin/console secrets:generate-keys
7. Configurer votre base de données:
- Mettez à jour le fichier .env avec vos identifiants de base de données.
- Créez la base de données :
  ```bash
  php bin/console doctrine:database:create
- Créez le schéma de la base de données :
  ```bash
  php bin/console doctrine:schema:create
8. Start the Symfony server:
   ```bash
   symfony serve
