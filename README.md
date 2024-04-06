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

- Chargez les fixtures :
  ```bash
  php bin/console doctrine:fixtures:load

8. Start the Symfony server:
   ```bash
   symfony serve
## Remarque 
Dans ce projet, j'ai utilisé la version LTS (Long Term Support) de Symfony 6.1. Initialement, j'avais l'intention de travailler avec la version 7 de Symfony. Cependant, j'ai rencontré des complications lors de l'installation avec API Platform, ce qui m'a amené à revenir à la version 6.1 LTS.
### Implémentation de l'authentification pour les sources de données requérant une authentification.
Développement d'un système permettant à l'API d'authentifier et de valider les connexions réussies. Pour ce faire, l'utilisation de jetons JWT (JSON Web Tokens) est prévue. Lorsqu'un utilisateur se connecte, un jeton encodé contenant ses informations est généré. Ce jeton est ensuite inclus dans l'en-tête Authorization des requêtes suivantes, confirmant ainsi l'authentification de l'utilisateur. Cette approche suit les standards de sécurité établis par JWT.

### Note
Afin de mettre en place JWT, j'ai utilisé le bundle lexik/jwt-authentication-bundle de Symfony. Pour éviter tout problème lors de l'installation de ce bundle, veuillez vérifier que vous avez activé l'extension ext-sodium dans votre fichier php.ini.

Pour générer les clés nécessaires à l'utilisation du bundle LexikJWTAuthenticationBundle dans Symfony, vous pouvez exécuter la commande suivante en ligne de commande :
1. 
   ```bash
   php bin/console lexik:jwt:generate-keypair
#### Intégrer un système de cache afin de limiter des requêtes répétitives vers les mêmes sources de données : 

   Un exemple de configuration de mise en cache dans la classe Article avec Api Platform est d'ajouter dans @ApiResource le cacheHeaders
1.
   ```bash
       cacheHeaders: [
       'max_age' => 60,
       'shared_max_age' => 120
         ]
Pour optimiser les performances d'une classe qui gère efficacement de grandes quantités d'articles provenant de différentes sources

En utilisant le cache dans le service ArticleFetcher, vous pouvez accélérer les appels aux méthodes fetchRssArticles et fetchJsonArticles si les articles ont déjà été récupérés et mis en cache. Cela améliorera les performances de votre application, surtout si les articles sont récupérés depuis des sources distantes.

Pour ceci :
injecter le cache dans le service ArticleFetcher pour mettre en cache les résultats des méthodes fetchRssArticles et fetchJsonArticles. Cela permettra d'accélérer les appels ultérieurs à ces méthodes si les articles ont déjà été récupérés et mis en cache.

utilisation du mecanisme de cache de symfony et de api pltaform