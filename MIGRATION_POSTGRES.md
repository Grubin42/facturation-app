# Migration de SQLite vers PostgreSQL

Ce document explique comment migrer l'application de SQLite vers PostgreSQL en utilisant Docker.

## Prérequis

- Docker
- Docker Compose
- PHP 8.x
- Composer
- Make (pour utiliser le Makefile)

## Étapes de migration

### 1. Configuration de l'environnement Docker

Un fichier `docker-compose.yml` a été créé pour configurer PostgreSQL et pgAdmin :

```yaml
version: '3'

services:
  postgres:
    image: postgres:16
    container_name: facturation_postgres
    restart: always
    environment:
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: laravel
      POSTGRES_DB: facturation
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - facturation_network

  pgadmin:
    image: dpage/pgadmin4
    container_name: facturation_pgadmin
    restart: always
    environment:
      PGADMIN_DEFAULT_EMAIL: admin@example.com
      PGADMIN_DEFAULT_PASSWORD: admin
    ports:
      - "5050:80"
    depends_on:
      - postgres
    networks:
      - facturation_network

networks:
  facturation_network:
    driver: bridge

volumes:
  postgres_data:
```

### 2. Modification du fichier .env

Modifiez le fichier `.env` pour utiliser PostgreSQL :

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=facturation
DB_USERNAME=laravel
DB_PASSWORD=laravel
```

### 3. Exécution de la migration

Vous pouvez utiliser le Makefile pour faciliter la migration :

```bash
# Afficher l'aide et les commandes disponibles
make help

# Effectuer la migration complète
make migrate-full
```

Ou utiliser le script de migration automatique :

```bash
./migrate-to-postgres.sh
```

Ces deux méthodes vont :
- Démarrer les conteneurs Docker
- Sauvegarder votre base de données SQLite actuelle
- Exécuter les migrations sur PostgreSQL
- Transférer les données existantes vers PostgreSQL

### 4. Réinitialisation des séquences PostgreSQL

Après avoir transféré des données, il est important de réinitialiser les séquences d'auto-incrémentation pour éviter les erreurs de clé dupliquée lors de la création de nouveaux enregistrements :

```bash
# Réinitialiser toutes les séquences
make fix-sequences
```

Cette commande va exécuter un script qui met à jour les séquences d'auto-incrémentation pour toutes les tables ayant un ID séquentiel. C'est une étape cruciale pour éviter des erreurs du type :

```
Illuminate\Database\UniqueConstraintViolationException
SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint "users_pkey" DETAIL: Key (id)=(1) already exists.
```

### 5. Utilisation du Makefile

Le Makefile offre plusieurs commandes utiles :

```bash
# Démarrer uniquement PostgreSQL
make postgres

# Démarrer PostgreSQL et pgAdmin
make pgadmin

# Lancer les migrations
make migrate

# Transférer les données de SQLite vers PostgreSQL
make transfer-data

# Effectuer une migration complète
make migrate-full

# Réinitialiser les séquences PostgreSQL
make fix-sequences

# Afficher le statut des conteneurs
make status

# Arrêter et nettoyer l'environnement
make clean
```

### 6. Vérification

Après la migration, vous pouvez vérifier que tout fonctionne correctement :

1. Accédez à votre application et testez les fonctionnalités
2. Connectez-vous à pgAdmin sur http://localhost:5050 avec :
   - Email: admin@example.com
   - Mot de passe: admin

3. Dans pgAdmin, ajoutez un nouveau serveur avec les paramètres :
   - Nom: Facturation App
   - Hôte: postgres (ou 127.0.0.1 si vous êtes hors Docker)
   - Port: 5432
   - Base de données: facturation
   - Utilisateur: laravel
   - Mot de passe: laravel

## Troubleshooting

### Problèmes de connexion à PostgreSQL

Si votre application ne peut pas se connecter à PostgreSQL, vérifiez :

1. Que les conteneurs Docker sont bien en cours d'exécution :
   ```bash
   make status
   ```

2. Que les paramètres de connexion dans `.env` sont corrects

3. Que PostgreSQL accepte les connexions :
   ```bash
   docker compose exec postgres pg_isready
   ```

### Problèmes de séquences et clés primaires dupliquées

Si vous rencontrez des erreurs de clé primaire dupliquée lors de la création de nouveaux enregistrements, exécutez :

```bash
make fix-sequences
```

### Problèmes de migration des données

Si vous rencontrez des erreurs lors du transfert des données :

1. Vérifiez les logs de la commande `db:transfer-data`
2. Assurez-vous que les structures de tables sont compatibles entre SQLite et PostgreSQL
3. Si nécessaire, modifiez la commande `TransferDataToPostgres.php` pour gérer les cas spécifiques
