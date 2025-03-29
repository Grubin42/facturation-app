# Guide de déploiement d'une application Laravel sur un hébergement mutualisé Infomaniak

## Table des matières
- [Préparation locale](#préparation-locale)
- [Structure des fichiers sur le serveur](#structure-des-fichiers-sur-le-serveur)
- [Fichiers clés à configurer](#fichiers-clés-à-configurer)
- [Étapes de déploiement](#étapes-de-déploiement)
- [Exécution des commandes PHP sur le serveur](#exécution-des-commandes-php-sur-le-serveur)
- [Dépannage courant](#dépannage-courant)
- [Maintenance](#maintenance)

## Préparation locale

1. **Compiler les assets**
   ```bash
   npm run build
   ```

2. **Optimiser l'application**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

## Structure des fichiers sur le serveur

Organisez votre application avec cette structure :

```
jardi-facture.ch/
│
├── index.php                # Point d'entrée modifié
├── .htaccess                # Règles de redirection
├── robots.txt               # Optionnel
├── favicon.ico              # Optionnel
│
└── laravel/                 # Dossier contenant l'application Laravel
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env                 # Configuration d'environnement
    ├── artisan
    └── public/              # Dossier public original
        └── build/           # Assets compilés
```

## Fichiers clés à configurer

### 1. Fichier index.php à la racine

```php
<?php

define('LARAVEL_START', microtime(true));

// Vérifier si l'application est en mode maintenance
if (file_exists($maintenance = __DIR__.'/laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Charger l'autoloader Composer
require __DIR__.'/laravel/vendor/autoload.php';

// Initialiser l'application Laravel
$app = require_once __DIR__.'/laravel/bootstrap/app.php';

// Traiter la requête
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
```

### 2. Fichier .htaccess à la racine

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Rediriger les requêtes /build/ vers /laravel/public/build/
    RewriteRule ^build/(.*)$ /laravel/public/build/$1 [L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 3. Fichier .env dans le dossier laravel

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jardi-facture.ch

DB_CONNECTION=sqlite
DB_DATABASE=/home/clients/d64acf4bf8778f9251a944be979e6ba9/sites/jardi-facture.ch/laravel/database/database.sqlite

# Configuration email Infomaniak
MAIL_MAILER=smtp
MAIL_HOST=mail.infomaniak.com
MAIL_PORT=587
MAIL_USERNAME=contact@jardi-facture.ch
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@jardi-facture.ch
MAIL_FROM_NAME="Jardi-Facture"
```

## Étapes de déploiement

1. **Transférer les fichiers**
   - Uploadez tous les fichiers en respectant la structure ci-dessus

2. **Définir les permissions**
   ```bash
   chmod -R 755 laravel
   chmod -R 775 laravel/storage
   chmod -R 775 laravel/bootstrap/cache
   ```

3. **Optimiser pour la production**
   ```bash
   cd laravel
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Tester l'application**
   - Visitez votre site pour vérifier que tout fonctionne
   - Testez l'inscription, la connexion et la récupération de mot de passe

## Exécution des commandes PHP sur le serveur

Sur un hébergement mutualisé Infomaniak, vous avez plusieurs options pour exécuter des commandes PHP et Artisan :

### 1. Via SSH (si disponible)

Si votre hébergement Infomaniak inclut l'accès SSH :

1. **Se connecter au serveur**
   ```bash
   ssh utilisateur@votre-serveur.infomaniak.ch
   ```

2. **Naviguer vers votre dossier d'application**
   ```bash
   cd sites/jardi-facture.ch/laravel
   ```

3. **Exécuter des commandes Artisan**
   ```bash
   php artisan migrate
   php artisan cache:clear
   ```

### 2. Via le gestionnaire de fichiers FTP

Si vous n'avez pas d'accès SSH :

1. **Créer un script PHP temporaire** dans un endroit non accessible publiquement (par exemple dans `/laravel/storage/`) :
   ```php
   <?php
   // artisan-helper.php
   
   // Définir le répertoire de travail
   chdir(__DIR__ . '/..');
   
   // Exécuter une commande Artisan
   $command = isset($_GET['cmd']) ? $_GET['cmd'] : 'list';
   
   // Protection par mot de passe simple
   $password = 'votre-mot-de-passe-secret';
   $provided = isset($_GET['key']) ? $_GET['key'] : '';
   
   if ($provided !== $password) {
       die('Accès non autorisé');
   }
   
   echo '<pre>';
   system('php artisan ' . escapeshellcmd($command));
   echo '</pre>';
   ```

2. **Accéder au script via un navigateur**
   ```
   https://jardi-facture.ch/laravel/storage/artisan-helper.php?key=votre-mot-de-passe-secret&cmd=migrate
   ```

3. **IMPORTANT :** Supprimez ce fichier immédiatement après usage pour des raisons de sécurité.

### 3. Via le Manager Infomaniak (Panel de contrôle)

1. **Accédez à votre hébergement** dans le Manager Infomaniak
2. **Recherchez la section "Terminal"** ou "Console" si disponible dans votre offre
3. **Utilisez l'interface de terminal** fournie pour exécuter vos commandes

### 4. Via les tâches planifiées (Cron)

Pour automatiser certaines commandes :

1. **Accédez à la section "Cron"** dans votre Manager Infomaniak
2. **Ajoutez une nouvelle tâche** avec la commande PHP complète :
   ```
   php /home/clients/d64acf4bf8778f9251a944be979e6ba9/sites/jardi-facture.ch/laravel/artisan schedule:run
   ```

### Notes importantes sur la sécurité

- **Ne placez jamais** de scripts d'administration dans des dossiers accessibles publiquement
- **Utilisez toujours** une authentification forte pour tous les scripts personnalisés
- **Supprimez** les scripts d'administration après utilisation
- **Limitez l'accès IP** aux outils d'administration quand c'est possible
- **Préférez l'accès SSH** quand il est disponible, car c'est la méthode la plus sécurisée

## Dépannage courant

### Erreur 500 ou page blanche
- Vérifiez les logs dans `laravel/storage/logs/laravel.log`
- Activez temporairement `APP_DEBUG=true` dans `.env`

### Problèmes d'assets (CSS/JS)
- Vérifiez la règle de redirection dans `.htaccess`
- Assurez-vous que les fichiers existent dans `laravel/public/build/`

### Problèmes d'envoi d'email
- Vérifiez les identifiants SMTP dans `.env`
- Testez avec `php artisan tinker` et `Mail::raw('Test', function($message) { $message->to('votre@email.com')->subject('Test'); })`

## Maintenance

Pour mettre à jour votre application :
1. Effectuez les modifications en local
2. Compilez les assets avec `npm run build`
3. Uploadez les fichiers modifiés sur le serveur
4. Videz les caches avec `php artisan optimize:clear`

---

*Ce guide a été créé pour le déploiement d'une application Laravel avec Vue.js et SQLite sur un hébergement mutualisé Infomaniak.* 