<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Grille hebdomadaire
    |--------------------------------------------------------------------------
    | Définit les horaires de l'établissement. Modifiable sans toucher au code.
    */

    // Jours ouvrés de la semaine (ordre d'affichage et de placement)
    'days' => ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'],

    // Durée d'un créneau de base, en minutes
    'slot_step' => 30,

    // Plage horaire ouverte
    'day_start' => '08:00',
    'day_end' => '18:00',

    // Pauses (périodes exclues du placement)
    'breaks' => [
        ['start' => '12:00', 'end' => '13:00'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organisation des TP
    |--------------------------------------------------------------------------
    */

    // Nombre de groupes fixes pour les TP (2 par convention : G1 et G2)
    'tp_groups' => 2,

    // Les groupes 1 et 2 d'un même TP peuvent-ils être parallèles (deux labos)
    // ou doivent-ils être consécutifs (même labo, deux créneaux) ?
    'parallel_tp_groups' => false,

    /*
    |--------------------------------------------------------------------------
    | Compatibilité salles / types d'enseignement
    |--------------------------------------------------------------------------
    */

    'room_types' => [
        'THEORY' => ['CLASSROOM', 'AMPHITHEATER'],
        'TP' => ['LABORATORY'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Algorithme de génération
    |--------------------------------------------------------------------------
    */

    // Nombre maximal de tentatives (restarts seedés)
    'max_attempts' => 10,

    // Marge de liberté hebdomadaire par classe (en %) : si le programme
    // demandé dépasse (100 - marge) % de la capacité, la génération est
    // rejetée avant même d'essayer (programme mathématiquement irréalisable).
    'weekly_capacity_margin_percent' => 20,

    // Budget de réparation par échec, en secondes
    'repair_time_budget' => 2,
];
