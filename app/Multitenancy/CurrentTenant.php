<?php

namespace App\Multitenancy;

use App\Models\School;
use LogicException;

class CurrentTenant
{
    private ?School $school = null;

    public function set(School $school): void
    {
        $this->school = $school;
    }

    public function clear(): void
    {
        $this->school = null;
    }

    public function school(): ?School
    {
        return $this->school;
    }

    public function id(): ?int
    {
        return $this->school?->getKey();
    }

    public function requireSchool(): School
    {
        return $this->school ?? throw new LogicException('Aucun tenant actif n’est défini.');
    }
}
