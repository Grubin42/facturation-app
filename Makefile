# Makefile pour la migration vers PostgreSQL

.PHONY: help postgres postgres-stop pgadmin migrate transfer-data migrate-full clean fix-sequences

# Afficher l'aide
help:
	@echo "Makefile pour la migration vers PostgreSQL"
	@echo ""
	@echo "Commandes disponibles:"
	@echo "  make help           - Affiche cette aide"
	@echo "  make postgres       - Démarre uniquement le conteneur PostgreSQL"
	@echo "  make pgadmin        - Démarre PostgreSQL et pgAdmin"
	@echo "  make migrate        - Lance les migrations sur PostgreSQL"
	@echo "  make transfer-data  - Transfère les données de SQLite vers PostgreSQL"
	@echo "  make migrate-full   - Effectue la migration complète (équivalent au script migrate-to-postgres.sh)"
	@echo "  make fix-sequences  - Réinitialise les séquences PostgreSQL après le transfert de données"
	@echo "  make clean          - Arrête tous les conteneurs et supprime les volumes"
	@echo "  make status         - Affiche le statut des conteneurs"
	@echo ""

# Démarrer PostgreSQL
postgres:
	@echo "Démarrage de PostgreSQL..."
	docker compose up -d postgres
	@echo "Attente de 5 secondes pour que PostgreSQL démarre complètement..."
	@sleep 5
	@echo "PostgreSQL est prêt!"

# Démarrer pgAdmin
pgadmin: postgres
	@echo "Démarrage de pgAdmin..."
	docker compose up -d pgadmin
	@echo "pgAdmin est disponible sur http://localhost:5050"
	@echo "  - Email: admin@example.com"
	@echo "  - Mot de passe: admin"

# Arrêter les conteneurs
postgres-stop:
	@echo "Arrêt des conteneurs..."
	docker compose stop
	@echo "Conteneurs arrêtés."

# Lancer les migrations
migrate: postgres
	@echo "Lancement des migrations..."
	php artisan migrate:fresh
	@echo "Migrations terminées!"

# Transférer les données
transfer-data: postgres
	@echo "Sauvegarde de la base SQLite..."
	cp database/database.sqlite database/database.sqlite.backup
	@echo "Transfert des données..."
	php artisan db:transfer-data
	@echo "Transfert terminé!"

# Migration complète
migrate-full: postgres
	@echo "Sauvegarde de la base SQLite..."
	cp database/database.sqlite database/database.sqlite.backup
	@echo "Lancement des migrations..."
	php artisan migrate:fresh
	@echo "Transfert des données..."
	php artisan db:transfer-data
	@echo ""
	@echo "Migration complète terminée!"
	@echo "Vous pouvez maintenant accéder à pgAdmin sur http://localhost:5050"
	@echo "  - Email: admin@example.com"
	@echo "  - Mot de passe: admin"

# Réinitialiser les séquences
fix-sequences: postgres
	@echo "Réinitialisation des séquences PostgreSQL..."
	./fix-sequences.sh
	@echo "Séquences réinitialisées!"

# Afficher le statut des conteneurs
status:
	@echo "Statut des conteneurs:"
	docker compose ps

# Nettoyer l'environnement
clean:
	@echo "Arrêt et suppression des conteneurs..."
	docker compose down -v
	@echo "Nettoyage terminé."
