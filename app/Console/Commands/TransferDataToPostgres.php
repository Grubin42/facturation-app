<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferDataToPostgres extends Command
{
    protected $signature = 'db:transfer-data';
    protected $description = 'Transfère les données de SQLite vers PostgreSQL';

    public function handle()
    {
        $this->info('Début du transfert des données de SQLite vers PostgreSQL...');

        // Connexion à la base SQLite
        config(['database.connections.sqlite_old' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite.backup'),
            'prefix' => '',
        ]]);

        // Récupération des tables
        $tables = DB::connection('sqlite_old')->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            $tableName = $table->name;

            // Ne pas transférer les tables de migrations
            if ($tableName === 'migrations') {
                continue;
            }

            $this->info("Transfert des données de la table: {$tableName}");

            // Récupération des données de SQLite
            $rows = DB::connection('sqlite_old')->table($tableName)->get();

            if (count($rows) === 0) {
                $this->warn("  - Aucune donnée trouvée dans la table {$tableName}");
                continue;
            }

            $this->info("  - {$rows->count()} enregistrements trouvés");

            // Transfert vers PostgreSQL
            foreach ($rows as $row) {
                $data = (array) $row;

                try {
                    DB::table($tableName)->insert($data);
                } catch (\Exception $e) {
                    $this->error("  - Erreur lors de l'insertion dans {$tableName}: " . $e->getMessage());
                }
            }

            $this->info("  - Transfert terminé pour la table {$tableName}");
        }

        $this->info('Transfert des données terminé!');

        return 0;
    }
}
