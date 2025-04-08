#!/bin/bash

echo "Réinitialisation des séquences PostgreSQL..."
echo "========================================"

# Liste des tables avec des ID auto-incrémentés
TABLES=(
  "users"
  "clients"
  "invoices"
  "quotes"
  "invoice_items"
  "quote_items"
  "settings"
  "jobs"
  "failed_jobs"
)

# Réinitialiser chaque séquence
for TABLE in "${TABLES[@]}"; do
  echo "Réinitialisation de la séquence pour $TABLE..."
  docker compose exec postgres psql -U laravel -d facturation -c "SELECT setval('${TABLE}_id_seq', COALESCE((SELECT MAX(id) FROM ${TABLE}), 1), true);"
done

echo ""
echo "Toutes les séquences ont été réinitialisées!"
echo "Vous pouvez maintenant créer de nouveaux enregistrements sans erreurs de contrainte d'unicité."
