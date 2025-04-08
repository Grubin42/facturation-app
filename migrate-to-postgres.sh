#!/bin/bash

# Afficher un message d'introduction
echo "Script de migration de SQLite vers PostgreSQL pour Laravel"
echo "==========================================================="
echo ""

# Vérifier si docker-compose est installé
if ! command -v docker &> /dev/null; then
    echo "Error: docker n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Démarrer les conteneurs PostgreSQL
echo "Démarrage des conteneurs PostgreSQL..."
docker compose up -d postgres
echo "Attente de 5 secondes que PostgreSQL soit complètement démarré..."
sleep 5

# Sauvegarder la base de données SQLite actuelle
echo "Sauvegarde de la base de données SQLite actuelle..."
cp database/database.sqlite database/database.sqlite.backup
echo "Base de données SQLite sauvegardée dans database/database.sqlite.backup"

# Effectuer la migration vers PostgreSQL
echo "Migration des tables vers PostgreSQL..."
php artisan migrate:fresh

# Si des seeders sont nécessaires, décommentez la ligne suivante
# php artisan db:seed

# Vérifier si des données doivent être transférées
read -p "Voulez-vous exécuter un script personnalisé pour transférer les données? (o/n): " transfer_data
if [[ $transfer_data == "o" ]]; then
    echo "Exécution du script de transfert de données..."
    php artisan db:transfer-data
fi

echo ""
echo "Migration vers PostgreSQL terminée!"
echo "Vous pouvez maintenant accéder à pgAdmin sur http://localhost:5050"
echo "  - Email: admin@example.com"
echo "  - Mot de passe: admin"
echo ""
echo "Paramètres de connexion PostgreSQL:"
echo "  - Hôte: postgres"
echo "  - Port: 5432"
echo "  - Base de données: facturation"
echo "  - Utilisateur: laravel"
echo "  - Mot de passe: laravel"
echo ""
echo "N'oubliez pas de vérifier votre application pour vous assurer que tout fonctionne correctement."
