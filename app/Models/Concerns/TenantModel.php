<?php

namespace App\Models\Concerns;

use App\Multitenancy\CurrentTenant;
use LogicException;

trait TenantModel
{
    /**
     * Chaque requête Eloquent pédagogique passe obligatoirement par la
     * connexion dynamique du tenant actif. L'absence de contexte est une
     * erreur de sécurité, jamais un repli vers la connexion master.
     */
    public function getConnectionName(): ?string
    {
        if (! app()->bound(CurrentTenant::class)) {
            throw new LogicException('Le contexte tenant n’est pas disponible.');
        }

        app(CurrentTenant::class)->requireSchool();

        return 'tenant';
    }
}
