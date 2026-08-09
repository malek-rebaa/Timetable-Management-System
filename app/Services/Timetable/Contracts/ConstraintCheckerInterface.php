<?php

namespace App\Services\Timetable\Contracts;

use App\Models\AcademicSession;

/**
 * Interface pour la vérification des conflits.
 * Peut être implémentée par une version SQL (pour la validation des requêtes)
 * ou par une version en mémoire (pour la génération rapide).
 */
interface ConstraintCheckerInterface
{
    /**
     * Vérifie toutes les contraintes pour une séance candidate.
     *
     * @param  AcademicSession  $candidate  séance à valider
     * @param  int|null  $ignoreSessionId  séance à exclure (cas de modification)
     * @return array<int, string>  liste de messages d'erreur (vide si OK)
     */
    public function check(AcademicSession $candidate, ?int $ignoreSessionId = null): array;

    /**
     * Règle : l'enseignant doit être habilité pour la matière du plan.
     */
    public function checkTeacher(AcademicSession $candidate): array;

    /**
     * Règles salle : type compatible avec le type d'enseignement + capacité suffisante.
     */
    public function checkRoom(AcademicSession $candidate): array;

    /**
     * Règles de non-chevauchement : enseignant, salle, classe.
     */
    public function checkOverlaps(AcademicSession $candidate, ?int $ignoreSessionId = null): array;

    /**
     * Règle de cohérence du groupe selon le type d'enseignement.
     */
    public function checkGroupConsistency(AcademicSession $candidate): array;
}
