<?php

namespace App\Multitenancy;

use App\Models\School;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;
use LogicException;

class TenantDatabaseManager
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly CurrentTenant $currentTenant,
    ) {
    }

    /**
     * Configure la connexion tenant pour la requête en cours.
     * Les modèles pédagogiques devront déclarer $connection = 'tenant'.
     */
    public function activate(School $school): void
    {
        if ($school->status !== 'ACTIVE') {
            throw new InvalidArgumentException('Impossible d’activer une école inactive.');
        }

        $template = config('database.connections.tenant');

        if (! is_array($template)) {
            throw new LogicException('La connexion tenant est absente de la configuration.');
        }

        config([
            'database.connections.tenant' => array_replace($template, [
                'database' => $school->database_name,
            ]),
        ]);

        $this->database->purge('tenant');
        $this->database->reconnect('tenant');
        $this->currentTenant->set($school);
    }

    public function deactivate(): void
    {
        $this->database->disconnect('tenant');
        $this->currentTenant->clear();
    }
}
