# Conception de l'algorithme de génération automatique des emplois du temps

> Périmètre : conception uniquement — aucune implémentation.
> Se base sur la structure de données validée dans `docs/analyse-generateur-emplois-du-temps.md`.

---

## 1. Modélisation du problème (CSP discret)

Le problème est un **problème de satisfaction de contraintes (CSP)** sur une **grille de temps discrète** :

- La semaine est découpée en créneaux réguliers (`slotStep`, ex. 30 min).
- Une séance de durée `d` (multiple du pas) occupe `k = d / pas` créneaux **contigus**.
- **Variables** : chaque (plan, classe, groupe) × `sessions_per_week` séances.
- **Domaines** : (`jour`, `début`) × enseignant × salle.
- **Contraintes** :
  - unaires : enseignant éligible (`teacher_subject`), salle compatible (type + capacité), cohérence groupe↔type d'enseignement ;
  - binaires : non-chevauchement enseignant, salle, classe.

C'est le placement dans un **graphe de ressource partagée** : la classe et la matière fixent *quoi* placer ; l'enseignant et la salle sont les ressources partagées qui créent les conflits entre niveaux.

---

## 2. Structures de données

### 2.1 Grille de temps — `SlotGrid`
```
SlotGrid {
    days: [MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY]        // config/timetable.php
    slotStep: 30 min                                             // config
    dayStart: "08:00", dayEnd: "18:00"                           // config
    excluded: [pause méridienne 12:00-14:00]                     // config
    slotsPerDay: int                                             // calculé
    slots: map day → [ Slot { index, startTime, endTime } ]
    -- méthodes --
    durationToSlots(minutes) → int        // minutes / slotStep
    indexOf(day, time) → Slot|null        // null si dans la pause ou hors plage
}
```
La grille ne contient **que** des créneaux valides : la pause en est absente, pas de condition à revérifier plus tard.

### 2.2 Registres d'occupation — vérification O(1)
```
classBusy[classId][day]   = bool[slotsPerDay]
teacherBusy[teacherId][day] = bool[slotsPerDay]
roomBusy[roomId][day]     = bool[slotsPerDay]
```
Implémentation : tableaux de booléens, ou **bitmasks (int 64 bits)** si `slotsPerDay ≤ 64` (cas typique : 24 créneaux de 30 min sur une journée de 8 h, ou moins après pause). Un bit à 1 = créneau occupé. Le test « libre sur k créneaux contigus » devient un simple masque.

Avec 8 à 10 niveaux contenant des dizaines de classes et des dizaines de salles/enseignants, ces tableaux tiennent confortablement en mémoire pour une tentative.

### 2.3 Requête de placement — `Request`
```
Request {
    id
    levelId, classId
    subjectPlan { subjectId, teachingType, sessionDuration, sessionsPerWeek }
    groupNumber: int|null          // null = THEORY (classe entière) ; 1 ou 2 = TP
    durationSlots: int             // = sessionDuration / slotStep
    minCapacity: int               // classe entière ou ceil(student_count/2) pour TP
    teacherCandidates: [teacherId] // pré-filtrés : teacher_subject du subjectId
    roomCandidates: [roomId]       // pré-filtrés : type ∈ config[teachingType] ET capacity ≥ minCapacity
}
```
**Point clé** : le pré-filtrage des domaines (enseignants éligibles, salles compatibles) se fait **une seule fois** à la construction. Le placement ne re-vérifie plus que les contraintes qui changent (disponibilités).

### 2.4 Tentative de placement — `Placement`
```
Placement { requestId, day, startSlot, teacherId, roomId }
```

### 2.5 Rapport — `TimetableReport`
```
TimetableReport {
    placed: [Placement]
    unplaced: [ FailedRequest { request, reason: string } ]
    teacherLoad: map teacherId → heures
    roomUsage: map roomId → heures
    qualityScore: int (soft constraints)
}
```

---

## 3. Ordre exact des étapes

```
┌─────────────────────────────────────────────────────────────┐
│ PHASE 0 — PREFLIGHT                                         │
│   valider config + données ; échec immédiat si incohérent   │
└─────────────────────────────────────────────────────────────┘
                             │ OK
                             ▼
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1 — CONSTRUCTION DES REQUÊTES                         │
│   plans × classes → Requests (THEORY×1, TP×2 par plan)      │
└─────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│ PHASE 2 — TRI PAR DIFFICULTÉ (MRV)                          │
│   requêtes les plus contraintes d'abord                     │
└─────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│ PHASE 3 — PLACEMENT GLOUTON  ════►  échecs ?                │
└─────────────────────────────────────────────────────────────┘
                             │ oui
                             ▼
┌─────────────────────────────────────────────────────────────┐
│ PHASE 4 — RÉPARATION (backtracking limité, budget temps)    │
└─────────────────────────────────────────────────────────────┘
                             │ échecs restants ? et tentatives < MAX
                             ▼ oui
┌─────────────────────────────────────────────────────────────┐
│ PHASE 5 — RESTART (nouvel ordre aléatoire seedé)            │
└─────────────────────────────────────────────────────────────┘
                             │ meilleure tentative retenue
                             ▼
┌─────────────────────────────────────────────────────────────┐
│ PHASE 6 — COMMIT TRANSACTIONNEL                             │
│   DELETE anciennes séances → INSERT placements → rapport    │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Détail des phases

### Phase 0 — Preflight (aucune écriture DB)
1. **Config** : grille non vide, pas cohérent, toutes les durées de plans sont des multiples du pas, pause bien définie.
2. **Niveaux** : chaque niveau à générer possède ≥ 1 classe ; chaque classe a `student_count > 0`.
3. **Plans** (un par `(level, subject, teaching_type)`) :
   - ≥ 1 enseignant éligible (`teacher_subject` non vide pour la matière) ;
   - ≥ 1 salle compatible (type + capacité) ;
   - `sessions_per_week ≥ 1`, `session_duration ≥ pas`.
4. **Capacité hebdomadaire par classe** : somme sur tous les plans de `séances × durationSlots` (TP compté ×2 groupes) ≤ créneaux disponibles de la semaine **moins une marge** (ex. 20 % de liberté). Si dépassement → **échec bloquant immédiat** : aucun programme ne peut tenir, inutile d'essayer.
5. **Disponibilité enseignants** : v1 = tous disponibles (pas de table). Le preflight sert de point d'extension.

Toute erreur → rapport renvoyé, **aucune séance supprimée/écrite**.

### Phase 1 — Construction des requêtes
Pour chaque niveau `L`, chaque classe `C` de `L`, chaque plan `P` de `L` :
- `THEORY` → **1 requête** : groupe `null`, count = `P.sessions_per_week`, `minCapacity = C.student_count`, salles = `config[THEORY]` (= CLASSROOM + AMPHITHEATER).
- `TP` → **2 requêtes** (groupe 1 et groupe 2) : count = `P.sessions_per_week` **par groupe**, `minCapacity = ceil(C.student_count / 2)`, salles = `config[TP]` (= LABORATORY).

Produit total attendu pour la classe = `Σ_TH THEORY(sessions) + 2 × Σ_TP TP(sessions)` séances.

### Phase 2 — Tri MRV (Most Constrained First)
Score de difficulté croissante :
```
score = w1 × (durationSlots × count)
      + w2 × (1 / |roomCandidates|)          // peu de salles possibles = plus dur
      + w3 × (teachingType == TP ? 1 : 0)    // TP toujours plus contraint
      + w4 × (1 / |teacherCandidates|)
```
Tri **décroissant** du score. Les séances longues, nombreuses, en TP, avec peu de salles ou d'enseignants sortent d'abord : elles consomment le plus de « place » et ont le moins de flexibilité.

### Phase 3 — Placement glouton (une tentative)
Pour chaque requête, dans l'ordre MRV :

```
for (day, startSlot) in candidateSlots(request):        // ordre défini en 6.1
    if not classFree(request.classId, day, startSlot, k): continue
    if request.groupNumber != null and siblingGroupBusy(...): continue   // v1 : groupes non simultanés
    for teacher in sortedTeachers(request.teacherCandidates):  // par charge croissante
        if not teacherFree(teacher, day, startSlot, k): continue
        for room in sortedRooms(request.roomCandidates):      // capacité croissante
            if not roomFree(room, day, startSlot, k): continue
            place(request, day, startSlot, teacher, room)     // marque les 3 registres
            next request
fail(request, reason)
```

**Sélection enseignant** : parmi les éligibles et libres, prendre celui dont la charge hebdomadaire est la plus faible → **load-balancing** naturel, évite de saturer un enseignant et de créer des impasses.

**Sélection salle** : la **plus petite** salle compatible et libre (« best fit ») → préserve les grandes salles pour les classes nombreuses.

### Phase 4 — Réparation (recherche de conflits, backtracking borné)
Si une requête échoue, plutôt que d'abandonner :
1. Déterminer pour cette requête le **meilleur créneau** (selon l'ordre soft) où elle échoue.
2. Identifier les **séances bloquantes** déjà placées (occupent la classe, un enseignant, ou une salle qu'il faudrait).
3. Tenter de **déplacer une séance bloquante** ailleurs (même procédure récursive, profondeur max 2-3, avec un ensemble `visited` pour éviter les boucles) puis replacer la requête.
4. Budget temps global (ex. 2 s par requête en réparation) : si dépassé → on garde l'échec et on passera aux restarts.

C'est un **backtracking directionnel** (inspiré de *Min-Conflicts*) : on ne fouille pas tout l'espace, on ne déplace que ce qui gêne réellement.

### Phase 5 — Restarts (stratégie multi-tentatives)
- Répéter Phases 3-4 avec un **ordre de créneaux aléatoire seedé** (seed = numéro de tentative) jusqu'à `MAX_ATTEMPTS` (default 5..15, configurable).
- Conserver la meilleure tentative : moins d'échecs d'abord, puis meilleur `qualityScore` (cf. §8 soft).
- En environnement de test, `seed` fixe et `randomize = false` → **résultat 100 % déterministe**.

### Phase 6 — Commit transactionnel
```
DB::transaction {
    AcademicSession::whereIn(class_room_id, classesDuPérimètre)->delete();
    insert(TousLesPlacements.de.la.meilleure.tentative);
}
```
- Rollback automatique si une insertion échoue → **l'ancien emploi du temps reste intact**.
- Puis persistance du rapport (échecs + charges) et retour de `TimetableReport`.

---

## 5. Vérifications par créneau (résumé actionnable)

| # | Contrainte | Où | Test |
|---|---|---|---|
| 1 | Classe libre | registre classBusy | k créneaux contigus vides |
| 2 | Enseignant éligible | pré-filtré `teacherCandidates` | appartenance |
| 3 | Enseignant libre | registre teacherBusy | k créneaux contigus vides |
| 4 | Salle compatible (type) | pré-filtré `roomCandidates` | appartenance |
| 5 | Capacité suffisante | pré-filtré | `capacity ≥ minCapacity` |
| 6 | Salle libre | registre roomBusy | k créneaux contigus vides |
| 7 | Cohérence groupe | `teaching_type` ↔ `group_number` | THEORY → null ; TP → 1/2 |
| 8 | Groupes TP non simultanés (v1) | registre classe | le groupe jumeau n'est pas au même créneau |

Les contraintes 2-4-5 sont **garanties par le pré-filtrage** : le placement n'a plus qu'à vérifier les disponibilités (1-3-6-7-8). C'est ce qui rend le glouton très rapide.

---

## 6. Gestion des conflits

### 6.1 Ordre des créneaux candidats
- **Répartition inter-jours** : balayer les jours en round-robin (Lundi→Vendredi puis Mardi→…), pas jour par jour — évite de tout entasser en début de semaine.
- **Intra-jour** : créneaux du matin d'abord, puis l'après-midi.
- **Soft** : éviter de créer des « journées vides » (un cours isolé au milieu d'une journée sans autre séance de la même classe) — pénalité appliquée au score, pas au placement.
- **Restart** : décalage cyclique du départ (rotation) ou shuffle seedé.

### 6.2 Niveaux de résolution
1. **Local** : créneau suivant (glouton pur).
2. **Swap** : changer d'enseignant ou de salle parmi les candidats libres avant de changer de créneau.
3. **Réparation** : déplacer la séance bloquante (backtracking profondeur limitée).
4. **Restart** : nouvel ordre aléatoire, nouvelle tentative.
5. **Abandon** : au-delà du budget, échec documenté dans le rapport.

---

## 7. Cas d'échec (et raisons explicites)

| # | Symptôme | Cause probable | Détection | Message du rapport |
|---|---|---|---|---|
| 1 | Plan sans enseignant | `teacher_subject` vide pour cette matière | Preflight | « Aucun enseignant n'enseigne la matière X » |
| 2 | Plan sans salle | aucun type compatible ou capacité insuffisante | Preflight | « Aucune salle LABORATORY ≥ 20 places » |
| 3 | Classe surchargée | Σ séances > créneaux hebdo − marge | Preflight | « Programme irréalisable : 38 créneaux demandés / 30 disponibles » |
| 4 | TP sans labo assez grand | labs en nombre/ capacité insuffisants | Preflight | « 4 labos requis, 2 disponibles » |
| 5 | Séance isolée impossible | fragmentation par enseignants/salles | Phase 3/4 | « Aucun créneau sans conflit pour … » |
| 6 | Impasse globale | programme irréalisable malgré restarts | Phase 5 | « Échec après N tentatives — contraintes à relâcher » |

Chaque échec est **explicite et actionnable** : il nomme le plan, la classe, le groupe, et la ressource manquante.

---

## 8. Optimisations (performance & qualité)

**Performance**
1. **Bitmasks** pour `classBusy / teacherBusy / roomBusy` → vérification O(1) par créneau.
2. **Pré-filtrage** des domaines (enseignants, salles) une seule fois par requête.
3. **Cache** en mémoire : `teacher → subjects`, `subject → eligibleTeachers`, `roomType → rooms` (une passe de chargement au début de la tentative).
4. **Tri MRV** : réduire les explorations inutiles en plaçant d'abord le plus contraint.
5. **Budget temps + MAX_ATTEMPTS** : réponse garantie même sur de gros volumes.
6. Parallélisation optionnelle : si les enseignants/salles **ne sont pas partagés entre niveaux**, générer chaque niveau dans un Job parallèle ; sinon séquentiel avec les 3 registres globaux partagés (c'est le cas général : un même enseignant peut intervenir sur plusieurs niveaux → **registres globaux obligatoires**).

**Qualité (soft constraints, évaluées après placement, jamais bloquantes)**
7. Répartition équilibrée des séances sur la semaine (pas de journée vide).
8. Espacement des séances d'une même matière dans la semaine.
9. Groupes 1 et 2 d'un même TP **consécutifs** (même journée, créneaux adjacents) par défaut, ou **parallèles** dans deux labos si activé en config.
10. Charge enseignants équilibrée (`max charge − min charge` minimisé).

---

## 9. Pseudo-code détaillé

```
function generateTimetable(levelIds):
    # ── Phase 0 ─────────────────────────────────────────────
    report = preflight(levelIds)
    if report.hasBlockingErrors():
        return report                                   # aucune écriture DB

    grid = SlotGrid.fromConfig()
    requests = []

    # ── Phase 1 ─────────────────────────────────────────────
    for level in Level.with(classes, subjectPlans).whereIn(id, levelIds):
        for cls in level.classes:
            for plan in level.subjectPlans:
                teachers  = eligibleTeachers(plan.subjectId)            # via teacher_subject
                if plan.teachingType == THEORY:
                    rooms = compatibleRooms(THEORY, cls.studentCount)
                    requests += Request(cls, plan, group=null,
                                        count=plan.sessionsPerWeek,
                                        minCapacity=cls.studentCount,
                                        teachers, rooms)
                else:  # TP
                    half = ceil(cls.studentCount / 2)
                    rooms = compatibleRooms(TP, half)
                    for g in [1, 2]:
                        requests += Request(cls, plan, group=g,
                                            count=plan.sessionsPerWeek,
                                            minCapacity=half,
                                            teachers, rooms)

    # ── Phase 2 ─────────────────────────────────────────────
    sort(requests, by=desc(difficultyScore))            # MRV

    # ── Phases 3-5 ──────────────────────────────────────────
    best = null
    for attempt in 1..MAX_ATTEMPTS:
        occ = OccupancyRegistry(grid)                   # 3 registres vides
        trial = greedyPlace(requests, grid, occ, seed=attempt)
        if trial.unplaced.isEmpty():
            return commit(trial, grid, levelIds)        # succès immédiat

        if attempt < MAX_ATTEMPTS:
            repaired = repair(trial, occ, maxDepth=3, timeBudget)
            if repaired.betterThan(best): best = repaired

    # ── Phase 6 ─────────────────────────────────────────────
    if best.unplacedRemainsBelow(acceptableThreshold):
        return commit(best)                             # partiel, rapport honnête
    return best                                         # ou échec documenté


function greedyPlace(requests, grid, occ, seed):
    slotsOrder = orderedCandidateSlots(grid, seed)      # round-robin jours, puis rotation
    for req in requests:
        placed = false
        for (day, start) in slotsOrder:
            k = req.durationSlots
            if not occ.classFree(req.classId, day, start, k): continue
            if req.group and occ.siblingTpBusy(req, day, start, k): continue
            for teacher in sortByLoad(req.teacherCandidates, occ):
                if not occ.teacherFree(teacher, day, start, k): continue
                for room in sortByCapacityAsc(req.roomCandidates):
                    if not occ.roomFree(room, day, start, k): continue
                    occ.book(req, day, start, teacher, room)
                    trial.placed += Placement(...)
                    placed = true
                    break 2
            if placed: break
        if not placed:
            trial.unplaced += FailedRequest(req, reason=diagnose(req, occ))


function repair(trial, occ, maxDepth, timeBudget):
    for failed in trial.unplaced:
        if timeBudget.exhausted(): break
        (day, start) = bestBlockedSlotFor(failed)       # selon ordre soft
        for blocker in blockingSessions(failed, day, start):
            if not blocker.visited:
                blocker.visited = true
                if relocate(blocker, occ, depth=maxDepth):   # récursif, cf. greedy
                    occ.book(failed at (day, start))
                    trial.unplaced.remove(failed)
                    trial.placed.append(failed)
                    break

    return trial
```

---

## 10. Pourquoi cette architecture d'algorithme ?

1. **Simple d'abord** : un glouton ordonné par difficulté + pré-filtrage résout la grande majorité des grilles réalistes (programme de ~3 à 10 matières par niveau). Pas besoin d'un solveur complet dès la v1.
2. **Résilient** : la réparation ciblée + restarts rattrapent les cas limites sans complexité de backtracking complet.
3. **Déterministe en test** (seed fixe), **robuste en production** (restarts + budgets).
4. **Extensible** : une nouvelle contrainte (indisponibilité enseignant, salle hors-service) s'ajoute au `ConflictChecker`/registres sans changer l'algorithme ; la stratégie de placement peut être remplacée par un recuit simulé ou un solveur externe sans toucher ni la base ni les structures de données.

**Complexité indicative** : pour chaque requête, au pire `jours × créneaux × enseignantsCandidats × sallesCandidates`, soit pour une école typique (10 classes × 6 matières × 2-3 séances) quelques centaines de milliers d'opérations O(1) par tentative — bien en dessous de la seconde.
