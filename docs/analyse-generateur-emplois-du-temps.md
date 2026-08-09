# Analyse de la base de données & architecture — Générateur d'emplois du temps

> Périmètre : analyse uniquement. Aucune modification de code proposée ici.
> Base cible : MariaDB 10.4.32 (les `CHECK` sont **appliqués** par MariaDB ≥ 10.2).

---

## 1. État des lieux de la base actuelle

| Table | Colonnes clés | Clés / contraintes existantes |
|---|---|---|
| `users` | first_name, last_name, email, password, role (`SUPER_ADMIN`,`ADMIN`,`TEACHER`), is_active | UNIQUE(email) |
| `levels` | name | UNIQUE(name) |
| `class_rooms` | level_id, name, student_count | FK(level_id)→levels, UNIQUE(level_id,name) |
| `rooms` | name, capacity, type (`CLASSROOM`,`LABORATORY`,`AMPHITHEATER`) | UNIQUE(name) |
| `subjects` | name | UNIQUE(name) |
| `teacher_subject` | teacher_id, subject_id | FK(teacher_id)→users, FK(subject_id)→subjects, UNIQUE(teacher_id,subject_id) |
| `subject_plans` | level_id, subject_id, weekly_hours, sessions_per_week, session_duration, teaching_type (`THEORY`,`TP`) | FK(level_id)→levels, FK(subject_id)→subjects, **UNIQUE(level_id, subject_id)** |
| `academic_sessions` | subject_plan_id, teacher_id, class_room_id, room_id (null), day, start_time, end_time, group_number (défaut 1) | FK vers plans/teachers/classes/rooms, 4 index FK simples |

**L'ossature est saine.** Le schéma couvre déjà : niveaux → classes, salles typées, matières, affectations enseignants↔matières, plans pédagogiques par niveau, et séances horodatées avec groupe (TP). La base est bien normalisée et les noms cohérents. La plupart des tables n'ont **pas** besoin d'être touchées.

---

## 2. Problèmes bloquants pour une génération automatique sans conflit

### P1 — Impossible d'avoir un plan THEORY ET un plan TP pour la même matière

`UNIQUE(level_id, subject_id)` sur `subject_plans` interdit d'avoir, pour un même niveau et une même matière, à la fois un plan de cours magistral et un plan de TP.

**Problème résolu :** cas réel courant (ex. « Maths » = 3 séances THEORY + 1 séance TP).

**Modification :** `UNIQUE(level_id, subject_id, teaching_type)`.

---

### P2 — Aucune garantie que l'enseignant d'une séance maîtrise la matière

Le lien `TeacherSubject` existe, mais rien ne relie `academic_sessions.teacher_id` au fait que cet enseignant enseigne bien `subject_plans.subject_id`. Contrainte du cahier des charges : *« un enseignant ne peut enseigner qu'une matière qui lui est affectée via TeacherSubject »*.

- Une `CHECK` SQL **ne peut pas** référencer une autre table en MariaDB → impossible en base pure.
- **Solution :** règle de validation unique, centralisée dans un `ConflictChecker` / service de validation, utilisée par **tous** les chemins d'écriture (saisie manuelle ET générateur). On vérifie `(teacher_id, subject_plan.subject_id) ∈ teacher_subject`.
- Design pour l'interface : lors de la saisie manuelle d'une séance, ne proposer que les enseignants de `teacher_subject` pour la matière du plan.

---

### P3 — Chevauchements horaires non protégés (enseignant / salle / classe)

Les trois règles *« pas deux séances simultanées »* sont impossibles à exprimer avec `UNIQUE` car ce sont des **intervalles** : deux séances peuvent se chevaucher sans partager la même heure de début (08:00–10:00 vs 09:00–11:00). Un `UNIQUE(day, start_time)` ne détecterait pas ce cas.

**Solution en trois couches :**
1. **Index composites** (voir §6) pour rendre les requêtes de détection de conflit rapides.
2. **`ConflictChecker` central** qui applique les 3 règles de non-chevauchement + les règles de compatibilité. Seul point de vérité.
3. Filet optionnel : `UNIQUE(class_room_id, day, start_time)` (et variantes salle/enseignant) qui bloque les doublons *exacts* à l'insertion. Ne remplace pas le `ConflictChecker`, mais protège contre les insertions directes en SQL.

---

### P4 — `group_number` : convention floue (TP en 2 groupes fixes)

Aujourd'hui `group_number` vaut 1 par défaut, sans signification claire. Le TP suppose « 2 groupes fixes ». Il faut une convention explicite :

- **THEORY** : classe entière → `group_number = NULL` (ou 0).
- **TP** : `group_number = 1` ou `2` (jamais plus — pas de gestion individuelle des étudiants).

**Modification :** rendre la colonne nullable, ajouter `CHECK (group_number IN (0,1,2))` ou `(group_number IS NULL OR group_number IN (1,2))`, et vérifier en service que `teaching_type` et `group_number` sont cohérents. Pas de table `groups` nécessaire : le nombre de groupes est dérivé (`config('timetable.tp_groups') = 2`).

---

### P5 — `sessions_per_week` : s'applique à quel périmètre pour un TP ?

Pour un plan TP, une séance de 2 h pour le groupe 1 + une pour le groupe 2 : `sessions_per_week = 2` signifie-t-il 2 séances *par groupe* (4 séances au total) ou 2 séances *au total* (1 par groupe) ?

**Décision à trancher et à documenter** (pas de colonne à ajouter) — recommandation :
- `sessions_per_week` = nombre de séances **par groupe**.
- Le générateur produit `sessions_per_week × 2` séances pour un plan TP (g1 + g2), réparties dans la semaine.
- `weekly_hours` = volume hebdomadaire **par groupe**.

Cette convention doit vivre dans la doc et être respectée par le `PlanPreflightValidator` (voir §7).

---

### P6 — Un plan s'applique à quelles classes ?

`subject_plans` est rattaché au **niveau**, pas aux classes. Le générateur en déduit : chaque `class_room` du niveau suit le même programme.

**Assomption à documenter** : programme uniforme pour toutes les classes d'un niveau.
**Si** un jour deux classes d'un même niveau doivent avoir des programmes différents → ajouter une table pivot `class_room_subject_plan`. **Pas nécessaire aujourd'hui** — à écarter pour rester simple.

---

## 3. Colonnes redondantes ou ambiguës

### `subject_plans.weekly_hours`
- **Dérivable** : `sessions_per_week × session_duration`. Stockée en plus, elle peut diverger (incohérence) et son unité est ambiguë (heures ? minutes ?).
- **Recommandation A (radicale)** : la supprimer et la calculer à la volée. Colle à « ne garder qu'une source de vérité ».
- **Recommandation B (conservatrice)** : la garder pour le reporting (charge enseignants, récapitulatifs), la définir explicitement **« par groupe »**, et ajouter `CHECK (weekly_hours > 0 AND sessions_per_week > 0 AND session_duration > 0)`.
- Éviter un `CHECK` d'égalité `weekly_hours = sessions_per_week × session_duration / 60` : les arrondis (durées de 90 min) le rendraient fragile.

### `academic_sessions.end_time`
- **Dérivable** de `start_time + session_duration` (via le plan). MAIS la garder : c'est une dénormalisation volontaire qui évite des `JOIN` dans toutes les requêtes d'affichage et de conflit. Contrepartie : `CHECK (end_time > start_time)`.

---

## 4. Tables qui restent **inchangées**

| Table | Justification |
|---|---|
| `users` | Rôles et authentification déjà en place. |
| `levels` | Identité simple, UNIQUE(name) suffit. |
| `subjects` | Idem. |
| `teacher_subject` | Pivot correct, UNIQUE(teacher_id, subject_id) déjà présent. |
| `class_rooms` | Structure correcte ; `student_count` reste utile pour le check capacité (demi-groupes TP dérivés). |
| `rooms` | `type` enum suffit. (Si beaucoup de types de salles à terme, passer à une table `room_types` — non requis aujourd'hui.) |
| `academic_sessions` | Table de sortie du générateur : structure OK, on n'ajoute que index + CHECK + conventions. |

---

## 5. Nouvelles tables : réellement nécessaires ?

### `time_slots` → **évitable en v1**
Le générateur a besoin d'un ensemble fini de créneaux candidats (jours ouvrés, plage horaire, unité de grille, pause). Une table dédiée n'apporte rien tant que ces paramètres ne doivent pas être administrés par l'UI.
**Solution v1** : configuration `config/timetable.php` (jours, plage 08:00–18:00, pas de grille 15 ou 30 min, pause méridienne).
**Évolution** : si l'administrateur doit éditer les créneaux sans déployer, créer `time_slots(day, start_time, end_time)` — elle s'intégrera au `SlotProvider` sans toucher au reste.

### `teacher_availability` → **évitable en v1**
Hypothèse : tous les enseignants disponibles toute la semaine. Plus tard, préférer une table **d'indisponibilités** `teacher_unavailabilities(teacher_id, day, start_time, end_time)` plutôt que de la disponibilité positive (plus simple à maintenir et à étendre). Elle s'ajoutera comme une contrainte supplémentaire dans le `ConflictChecker`.

### Autres tables (année scolaire, semestre) → hors périmètre
À ajouter uniquement si le produit doit gérer plusieurs périodes.

---

## 6. Contraintes SQL & index proposés

### `CHECK` (applicables en MariaDB 10.4)

| Table | Contrainte | Pourquoi |
|---|---|---|
| `academic_sessions` | `end_time > start_time` | Incohérence impossible : séance qui finit avant de commencer. |
| `academic_sessions` | `group_number IS NULL OR group_number IN (0,1,2)` | Bornage des groupes (0 = classe entière si on garde cette convention, 1/2 = TP). |
| `rooms` | `capacity > 0` | Une salle de capacité nulle est inutile au générateur. |
| `class_rooms` | `student_count >= 0` | Garde-fou trivial. |
| `subject_plans` | `sessions_per_week > 0 AND session_duration > 0 AND weekly_hours > 0` | Plans vides interdits. |

### Modifications de contraintes

| Table | Modification | Pourquoi |
|---|---|---|
| `subject_plans` | `UNIQUE(level_id, subject_id, teaching_type)` (au lieu de `UNIQUE(level_id, subject_id)`) | Autoriser THEORY + TP pour la même matière (P1). |
| `academic_sessions` | `group_number` nullable (convention §2-P4) | Distinguer classe entière / groupe TP. |
| `rooms.type` | conserver l'enum | Extensible par migration si besoin ; pas de normalisation nécessaire. |

### Index (performance du générateur)

| Table | Index | Sert à |
|---|---|---|
| `academic_sessions` | `(teacher_id, day, start_time, end_time)` | Détection rapide du conflit enseignant. |
| `academic_sessions` | `(class_room_id, day, start_time, end_time)` | Détection rapide du conflit classe. |
| `academic_sessions` | `(room_id, day, start_time, end_time)` | Détection rapide du conflit salle. |
| `academic_sessions` | `(subject_plan_id, group_number)` | Régénération ciblée par plan / groupe. |
| `subject_plans` | UNIQUE `(level_id, subject_id, teaching_type)` | Lookup du programme d'un niveau. |

Les index FK existants (`subject_plan_id`, `teacher_id`, `class_room_id`, `room_id`) sont conservés tels quels.

---

## 7. Architecture Laravel proposée pour le générateur

```
app/Services/Timetable/
    TimetableGenerator.php        # Orchestrateur : pour chaque niveau, transaction, suppression puis régénération
    ConflictChecker.php           # Point de vérité : applique toutes les contraintes
    Constraints/                  # Un fichier par contrainte, interface commune
        Constraint.php            # interface (check(AcademicSession $candidate, array $context): bool)
        TeacherConflict.php
        RoomConflict.php
        ClassConflict.php
        TeacherSubjectEligibility.php
        RoomTypeAndCapacity.php
        GroupConsistency.php      # teaching_type ↔ group_number
    SlotProvider.php              # Créneaux candidats (config d'abord, table time_slots ensuite)
    RoomMatcher.php               # Sélection salle : type compatible + capacité suffisante
    PlanPreflightValidator.php    # Vérifie avant génération : plans cohérents, volumes, groupes, enseignants dispo
    TimetableReport.php           # Résultat : plans placés / échecs (liste explicite)
```

### Principes clés

1. **Transactions** : toute régénération se fait dans une transaction (`DB::transaction`). En cas d'échec → rollback, l'ancien emploi du temps reste intact. Regénération « destructive + insertion » dans le même périmètre (niveau).
2. **Un seul point de vérité pour les conflits** : `ConflictChecker`, utilisé aussi bien par le générateur que par la saisie manuelle (FormRequests). Impossible de contourner les règles par l'UI.
3. **Pattern « Constraint »** : chaque règle est une classe. Ajouter une contrainte future (indisponibilité enseignant, salle hors-service…) = créer une classe + l'enregistrer. **Aucune modification du générateur ni de la base.** → répond à l'exigence d'évolutivité.
4. **Configuration centralisée** : `config/timetable.php` (jours ouvrés, plages, pas de grille, pause, `tp_groups = 2`, mapping `teaching_type → room types` : THEORY → [CLASSROOM, AMPHITHEATER], TP → [LABORATORY]). Modifiable sans migration ni déploiement de code métier.
5. **Algorithme découplé** : le placement (glouton d'abord, puis backtracking / recuit simulé si besoin) implémente la même interface. On peut changer de stratégie sans toucher à la base ni aux contraintes.
6. **Asynchrone** : génération lancée via un `Job` (volumes réels → temps de calcul non négligeable), avec rapport d'échec stocké/affiché.
7. **Hard vs soft constraints** : les règles du cahier des charges sont des *hard* (jamais violées). Les préférences (pas de journée vide, matières espacées, regroupement des séances d'un TP le même jour) sont des *soft* optimisées après placement — évitent que le générateur échoue pour des raisons non bloquantes.

---

## 8. Résumé des changements

| # | Changement | Table | Critique pour le générateur ? |
|---|---|---|---|
| 1 | UNIQUE(level_id, subject_id, teaching_type) | subject_plans | ✅ Oui — sans ça, pas de THEORY + TP cohabitant |
| 2 | group_number nullable + CHECK (0/1/2) | academic_sessions | ✅ Oui — TP en 2 groupes fixes exploitables |
| 3 | Index composites (enseignant/classe/salle × jour × horaires) | academic_sessions | ✅ Oui — performance et détection de conflit |
| 4 | CHECK end_time > start_time | academic_sessions | 🟡 Filet de sécurité |
| 5 | CHECK > 0 sur volumes | subject_plans | 🟡 Filet de sécurité |
| 6 | CHECK capacity > 0 / student_count ≥ 0 | rooms / class_rooms | 🟡 Filet de sécurité |
| 7 | Validation centralisée TeacherSubject + compatibilité salle/type | — (code service) | ✅ Oui — contraintes P2 et types de salle |
| 8 | Convention sessions_per_week « par groupe » pour TP | — (documentation + validator) | ✅ Oui — volume de séances calculable |
| 9 | Supprimer (ou définir précisément) weekly_hours | subject_plans | 🟠 À trancher — redondance |
| 10 | `time_slots` / `teacher_availability` | — | ❌ **Non nécessaires en v1** (config) |

**Inchangées :** `users`, `levels`, `subjects`, `teacher_subject`, `class_rooms`, `rooms`, et la structure de `academic_sessions` (seulement index/CHECK ajoutés).

Aucune table supplémentaire n'est requise pour démarrer. Le système reste simple : **2 modifications de clés, 3 index, 4 CHECK, une convention documentée, et une couche service bien découpée**.
