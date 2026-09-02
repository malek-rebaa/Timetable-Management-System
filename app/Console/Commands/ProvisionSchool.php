<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Multitenancy\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProvisionSchool extends Command
{
    protected $signature = 'school:provision
                            {school : Identifiant numérique ou slug de l’école}
                            {--force : Exécute les migrations en environnement de production}';

    protected $description = 'Crée la base d’une école, applique ses migrations et active le tenant.';

    public function handle(TenantDatabaseManager $tenantDatabase): int
    {
        $school = School::query()
            ->whereKey($this->argument('school'))
            ->orWhere('slug', $this->argument('school'))
            ->first();

        if ($school === null) {
            $this->error('École introuvable.');

            return self::FAILURE;
        }

        if (! preg_match('/^[A-Za-z0-9_]+$/', $school->database_name)) {
            $this->error('Le nom de base de données de l’école est invalide.');

            return self::FAILURE;
        }

        $driver = DB::connection('master')->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->error('Le provisioning automatique nécessite MySQL ou MariaDB.');

            return self::FAILURE;
        }

        try {
            // database_name est validé ci-dessus avant son interpolation SQL.
            DB::connection('master')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$school->database_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );

            // L’état est rétabli à PENDING si les migrations échouent.
            $school->update(['status' => 'ACTIVE']);
            $tenantDatabase->activate($school);

            $exitCode = $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => $this->option('force'),
            ]);

            if ($exitCode !== self::SUCCESS) {
                throw new \RuntimeException('Les migrations tenant ont échoué.');
            }

            DB::connection('tenant')->table('tenant_metadata')->updateOrInsert(
                ['school_id' => $school->getKey()],
                ['schema_version' => '2026_09_01', 'provisioned_at' => now()]
            );

            $this->info("L’école {$school->name} est active (base : {$school->database_name}).");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $school->update(['status' => 'PENDING']);
            report($exception);
            $this->error("Provisioning interrompu : {$exception->getMessage()}");

            return self::FAILURE;
        } finally {
            $tenantDatabase->deactivate();
        }
    }
}
