# Étude architecturale — Générateur automatique d'emplois du temps

> Rôle : Software Architect — étude complète avant toute implémentation.
> Sources : migrations Laravel (7 tables métier), dump SQL réel (`emplois.sql`, MariaDB 10.4.32), modèles Eloquent, diagramme UML hors dépôt (analyse faite sur la base réelle).
> Principe : aucune règle métier supposée — toute information manquante est signalée **[À CONFIRMER]**.

---

# ÉTAPE 1 — ANALYSE DE LA BASE DE DONNÉES

## 1.1 Vue d'ensemble

```
users ─┬─< class_rooms        (FK level_id → levels)
       │
levels ─┬─< class_rooms
        └─< subject_plans ──< academic_sessions ──> rooms
subjects ─< teacher_subject >─ users (enseignants)
```

| Table | Rôle | Nécessaire ? |
|---|---|---|
| `users` | Comptes : SUPER_ADMIN, ADMIN, TEACHER (enseignants dans le même tableau) | Oui |
| `levels` | Niveaux (L1, L2…) : ancre des programmes et des classes | Oui |
| `class_rooms` | Classes concrètes d'un niveau (INFO1A, INFO1B…) | Oui |
| `rooms` | Salles physiques typées (CLASSE, LABO, AMPHI) | Oui |
| `subjects` | Matières | Oui |
| `teacher_subject` | Affectation enseignants ↔ matières | Oui |
| `subject_plans` | Programme d'un niveau : volume, séances, durée, type | Oui |
| `academic_sessions` | Séances générées (résultat du générateur) | Oui |

**Verdict global** : aucune table ne doit être supprimée. Le modèle est une base saine pour du *timetabling*. Les problèmes sont des **manques de précision**, pas des erreurs de structure.

## 1.2 Analyse table par table

### `users`
- Rôle : authentification + entités humaines (Super Admin, Admin, Enseignant).
- **Cardinalité correcte** : enseignant ↔ matière = N:M via pivot ; enseignant ↔ séance = 1:N.
- **Problème** : `role` est un `enum` hardcodé. Ajouter un rôle = migration + modif des conditions.
  - Impact actuel : faible (rôles stables).
  - Alternative (quand le besoin apparaîtra) : table `roles`. **À ne pas faire maintenant**.
- **Redondance** : aucune. **Colonne inutile** : aucune.

### `levels` / `subjects`
- Traits d'union de tout le modèle. Inchangeables : UNIQUE(name) suffit.
- **Manque éventuel** : code court (pour affichage). **[À CONFIRMER]** — non bloquant.

### `class_rooms`
- Rôle : groupes d'étudiants réels qui suivent les cours (une classe est l'unité « ressource » la plus contrainte).
- FK `level_id → levels` ON DELETE CASCADE : correct.
- `student_count` : utile au générateur (capacité minimale requise, demi-groupes TP).
  - **Manque** : explicitement ≥ 0 → aucun `CHECK`.
  - **Redondance potentielle** : si un jour les étudiants sont gérés individuellement, `student_count` devient dérivable — **hors périmètre** (l'utilisateur a exclu la gestion individuelle).
- **Gap critique** : `subject_plans` est rattaché au **niveau**, donc toutes les classes d'un niveau partagent le même programme. Si deux classes d'un même niveau doivent avoir des programmes différents, il manque un pivot `class_room_subject_plan`. **[À CONFIRMER : le programme est-il identique pour toutes les classes d'un même niveau ?]**

### `rooms`
- Rôle : ressources physiques avec type et capacité.
- `type` enum `CLASSROOM | LABORATORY | AMPHITHEATER` : le générateur s'en sert pour associer types de salles ↔ types d'enseignement.
- **Manque** : `capacity > 0` (CHECK) ; possiblement un champ `equipment`/`description` — optionnel.
- **Évolutivité** : si de nouveaux types de salles apparaissent (salle informatique, TP chimie…), l'enum suffit par migration ; une table `room_types` ne se justifie que si on ajoute des **propriétés** aux types (équipement requis par matière). **Non nécessaire en v1.**

### `teacher_subject`
- Pivot correct, UNIQUE(teacher_id, subject_id) déjà présent.
- **Gap pour le générateur** : rien n'indique si un enseignant est habilité pour le **TP** de la matière. Scénario réel : un enseignant fait le cours, un autre les TP. **[À CONFIRMER]**
  - Si oui : ajouter `teaches_tp` (bool) sur le pivot, ou règle métier « tout enseignant affecté à la matière peut faire cours et TP ».
- **Manque** : l'habilité est globale (enseignant→matière), pas par niveau/classe : correct — un enseignant habilité sur une matière peut couvrir plusieurs niveaux.

### `subject_plans`
- Rôle : le **programme**, la source de vérité du « quoi placer ».
- Cardinalité : niveau 1:N plans ; sujet 1:N plans. **Correcte.**
- **Problème bloquant n°1** : `UNIQUE(level_id, subject_id)` interdit THEORY + TP pour la même matière. → `UNIQUE(level_id, subject_id, teaching_type)`.
- **Problème n°2** : `weekly_hours` est **dérivable** (`sessions_per_week × session_duration`) et son unité ambiguë (heures ? minutes ?). Décision requise : supprimer (recommandée) ou définir + documenter + CHECK. **[À TRANCHER]**
- **Manque n°3** : `session_duration` et `sessions_per_week` sans bornes (CHECK > 0).
- **Manque n°4** : pour un plan TP, `sessions_per_week` = par groupe ou au total ? Convention à documenter (§2). **[À TRANCHER]**
- **Manque éventuel** : pondération de priorité des matières (certaines matières doivent être placées en premier). **[À CONFIRMER]**

### `academic_sessions`
- Rôle : **résultat** du générateur (et table de saisie manuelle).
- Cardinalités : plan 1:N ; enseignant 1:N ; classe 1:N ; salle 0..1:N (nullable). **Correctes.**
- **`room_id` nullable** : laisser possible une séance sans salle (cour en extérieur, salle non précisée) — conservé.
- **`day` + `start_time` + `end_time`** : correct, l'intervalle couvre les chevauchements (impossible à unifier par UNIQUE simple).
- **`group_number`** : défaut 1, sémantique floue → nullable + convention (THEORY = NULL, TP = 1/2) + CHECK.
- **Manque** : `CHECK(end_time > start_time)` ; index composites de non-chevauchement (§7).
- **Manque possible** : `is_locked` (verrouiller une séance manuelle contre la régénération) **[À CONFIRMER]** ; `notes`.

## 1.3 Synthèse des modifications proposées

| # | Table | Modification | Problème résolu | Avantages | Inconvénients |
|---|---|---|---|---|---|
| M1 | `subject_plans` | `UNIQUE(level_id, subject_id, teaching_type)` | THEORY + TP impossibles à cohabiter | Programme complet réaliste | Migration à faire avant de saisir les plans TP |
| M2 | `academic_sessions` | `group_number` nullable + `CHECK (group_number IS NULL OR group_number IN (1,2))` | Convention TP/2 groupes floue | Modèle explicite, pas de table `groups` | — |
| M3 | `academic_sessions` | `CHECK (end_time > start_time)` | Séances inversées possibles | Filet de sécurité | — |
| M4 | `subject_plans` | `CHECK (sessions_per_week > 0 AND session_duration > 0)` | Plans vides/absurdes | Garde-fou | — |
| M5 | `rooms` | `CHECK (capacity > 0)` | Salle inutilisable | Garde-fou | — |
| M6 | `class_rooms` | `CHECK (student_count >= 0)` | Valeurs négatives | Garde-fou | — |
| M7 | `academic_sessions` | 3 index composites non-chevauchement (enseignant/classe/salle × jour × horaires) | Détection de conflit rapide | Performance générateur | Léger surcoût écriture |
| M8 | `users` | (reporté) table `roles` si multi-rôles dynamiques | Évolutivité rôles | — | Inutile aujourd'hui |
| M9 | `teacher_subject` | (à décider) `teaches_tp` bool | TP confié à des enseignants habilités | Règle métier explicite | **[À CONFIRMER la règle]** |
| M10 | (`class_room_subject_plan`) | (à décider) pivot class↔plan si programmes par classe | Deux classes d'un même niveau, programmes différents | Flexibilité | **[À CONFIRMER si le besoin existe]** |

---

# ÉTAPE 2 — ANALYSE DES RÈGLES MÉTIER

Légende du stockage :
- **BASE** = contrainte enregistrée comme donnée (colonnes, booléens, tables).
- **GÉNÉRATEUR** = règle codée dans le service de génération (config ou validator), pas dans la base.
- **BASE+GÉN** = donnée en base exploitée par le générateur.

## Règles dures (bloquantes, jamais violées)

| # | Règle | Pourquoi elle existe | Impact algorithme | Stockage |
|---|---|---|---|---|
| R1 | Un enseignant ne peut pas avoir 2 séances simultanées | Un humain ne peut pas être à deux endroits | Registre `teacherBusy` testé à chaque placement | GÉNÉRATEUR (+ index) |
| R2 | Une salle ne peut pas avoir 2 séances simultanées | Une salle = une capacité | Registre `roomBusy` | GÉNÉRATEUR (+ index) |
| R3 | Une classe ne peut pas avoir 2 séances simultanées | Une classe = un groupe d'étudiants | Registre `classBusy` | GÉNÉRATEUR (+ index) |
| R4 | Un enseignant n'enseigne que ses matières (`teacher_subject`) | Compétence | Pré-filtre `teacherCandidates` ; validation à la saisie manuelle aussi | BASE (`teacher_subject`) |
| R5 | Durée/volume/type imposés par `subject_plans` | Cohérence pédagogique | Chaque requête dérive `durationSlots` et `count` du plan | BASE (`subject_plans`) |
| R6 | Salle compatible avec le type d'enseignement | TP = labo, cours = salle normale, etc. | Pré-filtre `roomCandidates` via mapping `teaching_type → room.type` | BASE (type salle) + GÉNÉRATEUR (mapping en config) |
| R7 | TP en 2 groupes fixes (1 et 2), jamais individuels | Organisation de l'établissement | 2 requêtes par plan TP ; `minCapacity = ceil(N/2)` | BASE (group_number) + config `tp_groups = 2` |
| R8 | Durée de séance ≥ pas de grille et multiple du pas | Grille discrète | Preflight : rejet si durée non alignée | GÉNÉRATEUR (config) |
| R9 | Séance dans les créneaux valides (plages, pause, jours ouvrés) | Horaires de l'établissement | `SlotGrid` exclut la pause | GÉNÉRATEUR (config) |
| R10 | Volume hebdomadaire respecté (total ≤ capacité hebdo de la classe) | Programme faisable | Preflight capacity check | GÉNÉRATEUR |

## Règles souples (optimisation, jamais bloquantes)

| # | Règle | Pourquoi | Impact | Stockage |
|---|---|---|---|---|
| R11 | Répartition des séances sur la semaine | Pas de journée vide, meilleur apprentissage | Scoring soft après placement | GÉNÉRATEUR |
| R12 | Espacement des séances d'une même matière | Apprentissage espacé | Scoring soft | GÉNÉRATEUR |
| R13 | TP groupe 1 & 2 consécutifs ou parallèles | Libre choix : 2 labos en même temps, ou un même labo en 2 créneaux | Option config | GÉNÉRATEUR |
| R14 | Charge enseignants équilibrée | Équité | Tri enseignant par charge croissante | GÉNÉRATEUR |
| R15 | Journées compactes (pas de trou de 3 h isolé) | Confort enseignant/élèves | Scoring soft | GÉNÉRATEUR |
| R16 | Salle la plus petite compatible utilisée en premier | Économie des grandes salles | « best fit » | GÉNÉRATEUR |
| R17 | Cours en début de journée, TP en fin | Fatigue cognitive, disponibilité labs | Ordre des créneaux | GÉNÉRATEUR |

## Règles en attente de confirmation

| # | Question | Si OUI | Impact |
|---|---|---|---|
| R18 | Un enseignant habilité pour une matière fait-il aussi le TP ? | Non → ajouter `teaches_tp` sur pivot | Pré-filtre enseignant par type de séance |
| R19 | Le volume est-il identique pour toutes les classes d'un niveau ? | Non → pivot `class_room_subject_plan` | Programmes par classe |
| R20 | Existe-t-il des créneaux interdits par enseignant (disponibilités) ? | Oui → table `teacher_unavailabilities` | Contrainte supplémentaire le jour 2 — design prévu, évitable en v1 |
| R21 | Max de séances/groupe par jour par classe (ex. 6 h) ? | Oui → paramètre config | Contrainte dans le pré-filtre de créneaux |
| R22 | Des matières ont-elles une priorité de placement ? | Oui → colonne `priority` sur `subject_plans` | Tri MRV pondéré |
| R23 | Séances verrouillées manuellement préservées ? | Oui → `academic_sessions.is_locked` | Le générateur ne touche pas aux séances verrouillées |
| R24 | Plusieurs emplois du temps (ex. année A/B, période) ? | Oui → table `timetables` (voir Ét. 8) | Périmètre de génération |

---

# ÉTAPE 3 — CONCEPTION DU GÉNÉRATEUR

## 3.1 Scénario complet de génération

**Périmètre** : on génère pour un ensemble de niveaux (par défaut tous). Les ressources partagées (enseignants, salles) sont **globales** : un même enseignant peut couvrir plusieurs niveaux → registres globaux, une seule passe.

**Par quoi commencer ?** Ni par les classes seules, ni par les matières : on construit une liste **unifiée de requêtes** `(classe, plan, groupe)`, triée par difficulté décroissante (MRV). On place ensuite la requête la plus contrainte en premier. C'est la réponse standard au timetabling : l'ordre de parcours est **par difficulté**, pas par entité.

Détail de l'ordre :
1. Construire toutes les requêtes (Phase 1).
2. Trier par score de difficulté : durée × nombre de séances, rareté des salles, rareté des enseignants, TP d'abord.
3. Pour chaque requête : balayer les créneaux dans l'ordre (round-robin jours, matin puis après-midi) ; pour chaque créneau, essayer tour à tour les enseignants (charge croissante) puis, pour chacun, les salles (capacité croissante).

**Pourquoi ce choix ?** Une séance longue en TP de laboratoire avec un seul enseignant habilité est le cas le plus fragile : la placer tôt évite que les ressources soient déjà capturées par des séances plus flexibles.

## 3.2 Pseudo-algorithme détaillé

```
FONCTION générer(levelIds):
  # Phase 0 — Preflight (aucune écriture)
  erreurs = preflight(levelIds)
  SI erreurs.bloquantes NON VIDE: RENVOYER erreurs   # ne rien écrire

  grille = SlotGrid.fromConfig()
  requêtes = construireRequêtes(levelIds, grille)

  # Phase 1-2 — tri MRV
  requêtes.trier PAR score(difficulté) DÉCROISSANT

  # Phase 3-5 — tentatives
  meilleure = NULL
  POUR tentative = 1 À MAX_ATTEMPTS:
    occ = OccupancyRegistry()                 # registres vides (bitmasks)
    essai = placementGlouton(requêtes, grille, occ, seed=tentative)
    SI essai.échecs EST VIDE: RENVOYER commit(essai, levelIds)
    essai = réparation(essai, occ, profondeurMax=3, budget)
    SI essai.mieuxQue(meilleure): meilleure = essai

  # Phase 6 — commit
  SI meilleure.tauxÉchec ≤ SEUIL: RENVOYER commit(meilleure, levelIds)
  RENVOYER meilleure                                   # échec documenté

FONCTION construireRequêtes(levelIds, grille):
  pour niveau, classe, plan:
    enseignants = habilités(plan.sujetId)                       # via teacher_subject (R4)
    SI plan.type == THEORY:
      requêtes += REQUÊTE(classe, plan, groupe=NULL,
                          count=plan.sessionsParSemaine,
                          capMin=classe.effectif,
                          enseignants, salles(THEORY, capMin))
    SINON: # TP → R7 : 2 groupes
      demi = ceil(classe.effectif / 2)
      salles = salles(TP, demi)                                 # LABORATORY, cap ≥ demi
      POUR g DANS [1, 2]:
        requêtes += REQUÊTE(classe, plan, groupe=g,
                            count=plan.sessionsParSemaine,      # par groupe [R19 à confirmer]
                            capMin=demi, enseignants, salles)

FONCTION placementGlouton(requêtes, grille, occ, seed):
  ordreCréneaux = créneauxCandidats(grille, seed)   # round-robin jours, rotation seedée
  POUR req DANS requêtes:
    k = req.duréeEnCréneaux
    POUR (jour, début) DANS ordreCréneaux:
      SI PAS occ.classeLibre(req.classeId, jour, début, k): CONTINUER
      SI req.groupe ET occ.groupeJumeauOccupé(req, jour, début, k): CONTINUER
      POUR prof DANS req.enseignants.triés(charge croissante):
        SI PAS occ.enseignantLibre(prof, jour, début, k): CONTINUER
        POUR salle DANS req.salles.triées(capacité croissante):
          SI PAS occ.salleLibre(salle, jour, début, k): CONTINUER
          occ.réserver(req, jour, début, prof, salle)
          essai.placements += PLACEMENT(...)
          PASSER À la requête suivante
    essai.échecs += ÉCHEC(req, raison=diagnostic(req, occ))
```

**Comment terminer** : le commit transactionnel supprime les séances du périmètre puis insère les placements choisis. En cas d'insertion impossible → rollback, ancien EDT intact.

---

# ÉTAPE 4 — GESTION DES CONFLITS

## 4.1 Catalogue exhaustif

| # | Conflit | Détection | Résolution | Prévention |
|---|---|---|---|---|
| C1 | Enseignant déjà occupé (chevauchement ou contiguïté non souhaitée) | registre `teacherBusy` : intervalle non vide sur [début, début+k) | créneau suivant ; swap d'enseignant ; relocaliser la séance bloquante ; restart | Tri MRV (l'enseignant rare est placé tôt) ; index `(teacher_id, day, start_time, end_time)` |
| C2 | Salle déjà occupée | registre `roomBusy` | créneau suivant ; autre salle compatible ; déplacement de la séance bloquante | best-fit (préserve les grandes salles) ; index salle |
| C3 | Classe déjà occupée | registre `classBusy` | créneau suivant ; relocaliser la séance bloquante | Tri MRV ; marge de remplissage au preflight (R10) |
| C4 | Enseignant non autorisé pour la matière | vérif `teacher_subject` au pré-filtre ET au `ConflictChecker` (saisie manuelle) | remplacer l'enseignant | L'UI ne propose que les enseignants habilités |
| C5 | Salle incompatible (mauvais type) | mapping `teaching_type → room.type` | autre salle | L'UI ne propose que les types valides |
| C6 | Capacité insuffisante | `room.capacity ≥ minCapacity` | autre salle | best-fit |
| C7 | Durée non alignée sur la grille (ex. 95 min) | preflight (R8) | rejeter la génération avec message | Validation du plan à la saisie (Form Request) |
| C8 | Volume horaire impossible (classe surchargée) | preflight (R10) : Σ > capacité hebdo − marge | rejeter avec rapport chiffré | Validation à la saisie des plans |
| C9 | TP mal réparti (groupes asymétriques) | Convention R7/R19 + `group_number` | générateur produit toujours g1+g2 ; vérif cohérence type↔groupe | CHECK SQL + `ConflictChecker` |
| C10 | Créneau inexistant (pause, hors plage, jour non ouvré) | `SlotGrid.indexOf` renvoie NULL | créneau suivant | la grille ne contient QUE des créneaux valides |
| C11 | Séance générée qui chevauche un cours précédent de la même classe avec un autre enseignant | registre `classBusy` (global, indépendant de l'enseignant) | — | les 3 registres sont *par ressource*, toujours vérifiés |
| C12 | Deux groupes TP du même plan simultanés (si config « consécutifs ») | `siblingGroupBusy` | créneau suivant | option config |

## 4.2 Principes d'évitement (le plus important)
1. **Pré-filtrage des domaines** : C4, C5, C6 ne sont jamais rencontrés pendant le placement (éliminés en amont). Ils ne peuvent surgir qu'à la **saisie manuelle** → le même `ConflictChecker` les attrape.
2. **Registres globaux** : C1, C2, C3 sont détectés en O(1) par bitmask, sans requête DB pendant la boucle.
3. **Preflight** : C7, C8, C9, C10 sont détectés avant toute insertion — aucune écriture partielle.
4. **Filet SQL** : CHECK + index garantissent même en cas d'insertion directe en SQL (hors application).

---

# ÉTAPE 5 — CHOIX DE L'ALGORITHME

| Méthode | Principe | Avantages | Inconvénients | Difficulté | Performance | Qualité |
|---|---|---|---|---|---|---|
| **Greedy (MRV)** | Placer les requêtes les plus contraintes en premier, premier créneau libre | Simple, rapide, déterministe | Peut échouer sur des cas denses ; qualité moyenne sans heuristiques | ★ | ms–s | correcte à bonne |
| **Backtracking (DFS)** | Revenir en arrière à chaque impasse | Solution exacte si elle existe | Combinatoire explosive (> 20 variables), lent, non prédictible | ★★★★ | secondes–heures | optimale |
| **CSP (MAC/AC-3, propagation)** | Réduire les domaines par propagation de contraintes avant affectation | Puissant, gère les contraintes complexes, fondement théorique solide | Complexe à implémenter correctement ; surkill pour les grilles scolaires moyennes | ★★★★★ | variable | très bonne |
| **Graph Coloring** | Classes = bords d'un graphe ; les conflits interdisent la même couleur (créneau) | Modélisation élégante des conflits mutuels ; utile pour le cas simple « un solo cours par classe » | Ne couvre pas naturellement les ressources partagées multiples (prof/salle) ni les séquences multi-créneaux | ★★★ | dépend du colorant | bonne mais insuffisant seul |
| **Métaheuristiques (recuit simulé, génétique, tabou)** | Démarrer d'une solution (éventuellement avec conflits), l'améliorer par itérations locales | Atteint l'excellente qualité sur des gros cas réalistes ; tolère les contraintes souples | Non déterministe (il faut un seed), réglage des paramètres, plus lent | ★★★★ | secondes–minutes | très bonne, parfois meilleure que backtracking en pratique |
| **Min-Conflicts / réparation ciblée** | Ne déplacer que les séances conflictuelles | Rapide, simple, efficace en pratique (utilisé pour les EDT réels) | Pas de garantie d'optimalité ; risque de cycles (d'où `visited` + budget) | ★★ | ms–s | bonne |

## Recommandation pour ce projet Laravel

**Hybride en 2 temps, aligné sur la réalité des collèges/lycées :**
1. **Cœur** : **Greedy ordonné MRV** + registres bitmask + pré-filtrage + **recherche Min-Conflicts** (réparation des séances bloquantes).
2. **Filet** : restarts seedés (5–15 tentatives) et choix de la meilleure tentative.

Justification :
- La plupart des établissements ont 5–10 matières × 5–15 classes : le glouton résout ces grilles en < 1 s.
- L'architecture du `ConflictChecker` rend le passage ultérieur à un solveur CSP complet ou à un recuit simulé **substituable sans toucher la base ni les requêtes** (même interface de contraintes, mêmes registres).
- La marge de prévision (R10) élimine les cas d'impossibilité mathématique avant l'algorithme.
- Backtracking complet et CSP pur sont écartés en v1 : surdimensionnés pour ce volume, risque de temps de génération non maîtrisé.

> **Point d'architecture à retenir** : l'algorithme est **interchangeable** (interface `PlacementStrategy`) ; la base et les structures de données sont neutres vis-à-vis de la stratégie.

---

# ÉTAPE 6 — ARCHITECTURE LARAVEL (SOLID)

## 6.1 Arborescence cible

```
app/
├─ Services/Timetable/
│  ├─ TimetableGenerator.php            # orchestrateur (Single Responsibility)
│  ├─ Placements/
│  │  ├─ PlacementStrategy.php          # interface
│  │  ├─ GreedyPlacementStrategy.php    # cœur v1
│  │  └─ (futur: SimulatedAnnealingStrategy)
│  ├─ Constraints/
│  │  ├─ Constraint.php                 # interface : isSatisfied(Request, PlacementContext): bool
│  │  ├─ TeacherEligibilityConstraint.php
│  │  ├─ RoomCompatibilityConstraint.php
│  │  ├─ TeacherConflictConstraint.php
│  │  ├─ RoomConflictConstraint.php
│  │  ├─ ClassConflictConstraint.php
│  │  ├─ GroupConsistencyConstraint.php
│  │  └─ … (chacun = 1 règle, registrable)
│  ├─ ConflictChecker.php               # aggrège les Constraint, point de vérité
│  ├─ SlotGrid.php                      # grille (config)
│  ├─ SlotProvider.php                  # façade créneaux (config → table demain)
│  ├─ RoomMatcher.php                   # best-fit + compatibilité
│  ├─ TeacherSelector.php               # load-balancing
│  ├─ RequestBuilder.php                # plans×classes → Requests (DTO)
│  ├─ PlanPreflightValidator.php
│  ├─ OccupancyRegistry.php             # bitmasks
│  ├─ TimetableCommitService.php        # transaction DELETE+INSERT
│  └─ DTO/
│     ├─ GenerationRequest.php          # value object : levelIds, strategy, options
│     ├─ SessionRequest.php             # requête de placement
│     ├─ PlacementResult.php
│     └─ TimetableReport.php
├─ Repositories/
│  ├─ SubjectPlanRepository.php         # chargement eager + caches mémoire
│  ├─ AcademicSessionRepository.php     # écritures en masse, purge par périmètre
│  └─ TeacherSubjectRepository.php
├─ Events/
│  ├─ TimetableGenerated.php            # after commit
│  ├─ TimetableGenerationFailed.php     # après échec (log + notification)
│  └─ Listeners/TimetableGenerationListener.php (notifications, logs, cache)
├─ Jobs/
│  └─ GenerateTimetableJob.php          # asynchrone, dispatching en file
├─ Http/Requests/
│  ├─ GenerateTimetableRequest.php
│  ├─ StoreAcademicSessionRequest.php   # saisie manuelle → utilise ConflictChecker
│  └─ StoreSubjectPlanRequest.php       # valide R7/R8/R10 en amont
├─ Policies/
│  ├─ SubjectPlanPolicy.php
│  ├─ AcademicSessionPolicy.php
│  └─ TimetablePolicy.php               # qui peut lancer une génération
├─ Helpers/ (minimal — plutôt DTO/Services)
│  └─ ScheduleMath.php                  # calculs purs : minutes→créneaux, chevauchement
└─ Traits/
   └─ HasRoleChecks.php                 # helpers de rôle réutilisables (isAdmin…)
```

## 6.2 Responsabilités et principes SOLID

| Principe | Application |
|---|---|
| **S** | Chaque classe a une responsabilité unique : l'orchestrateur ne place pas, il coordonne ; le `ConflictChecker` ne génère pas, il valide ; le commit service n'écrit qu'à la fin. |
| **O** | De nouvelles contraintes s'ajoutent **sans modifier** le générateur : on enregistre une classe `Constraint` supplémentaire (pattern Strategy/Registry). De nouvelles stratégies de placement : on implémente l'interface. |
| **L** | `PlacementStrategy`, `Constraint` : les implémentations sont substituables sans changer les appelants. |
| **I** | Interfaces fines : `Constraint` ne connaît que `isSatisfied(...)` ; `PlacementStrategy` ne connaît que `place(Requests, grid): TrialResult`. |
| **D** | `TimetableGenerator` dépend des *interfaces* `Constraint[]`, `PlacementStrategy`, `SlotProvider`, `Repositories` — injectées par le conteneur. |

**Anti-CRM (Controller anti-anémique)** : les Controllers ne font que valider la requête HTTP (FormRequest), appeler un Service, et formater la réponse. Aucune règle métier dans les Controllers. Les queries Eloquent ne fuient pas dans les Controllers : elles vivent dans les Repositories.

**Pourquoi des Repositories ici ?** Le générateur a besoin d'accès **balistiques et câblés** aux données (requêtes précises, eager loading ciblé, purges en masse) : les Repositories centralisent ces requêtes, facilitent les caches mémoire et les tests (mock).

**Pourquoi des Events/Jobs ?**
- La génération peut prendre de quelques secondes à plusieurs dizaines : **Job asynchrone** avec statut, plutôt que bloquer une requête HTTP.
- `TimetableGenerated` / `TimetableGenerationFailed` découplent le cœur du moteur des effets de bord (notifications admin, logs, invalidation de cache).
- **Observers** : uniquement sur `AcademicSession` pour invalider le cache d'affichage de l'EDT — pas pour la logique métier.

**Form Requests** : garde-fous à l'entrée (réécriture de la validation en règles explicites, messages fr). Ils exécutent `ConflictChecker` pour la saisie manuelle, et `PlanPreflightValidator` pour la génération.

**Policies** : l'administration (créer plans/matériels) = SUPER_ADMIN + ADMIN ; lancer la génération = SUPER_ADMIN ; consulter son EDT = TEACHER pour ses séances à lui. Appliquées sur les routes ET dans les vues (double sécurité).

## 6.3 Tests ciblés

| Type | Contenu |
|---|---|
| Unit | `SlotGrid` (pause, alignement), `ScheduleMath` (chevauchements), `OccupancyRegistry` (bitmasks), chaque `Constraint` isolée |
| Feature | Générateur complet sur un jeu de données figé → **résultat déterministe** (seed fixe) ; scenarios de conflit (2 profs, 2 salles, 1 classe) |
| Integration | Transaction commit/rollback : si insertion en masse échoue, aucune séance orpheline ; purge du périmètre correcte |
| Contract | L'interface `PlacementStrategy` avec une implémentation factice prouve la substituabilité ; un `Constraint` factice prouve l'ajout sans modification |

---

# ÉTAPE 7 — PERFORMANCES

## 7.1 Volume cible
Plusieurs centaines de séances (ex. 15 classes × 6 matières × 2-3 séances ≈ 250–600 séances). C'est un volume **modeste** : le vrai coût vient de l'algorithme, pas des index — mais les index rendent le pré-chargement et les vérifications manuelles rapides.

## 7.2 Index SQL

| Index | Table | Usage |
|---|---|---|
| `(teacher_id, day, start_time, end_time)` | academic_sessions | conflit enseignant O(1) |
| `(class_room_id, day, start_time, end_time)` | academic_sessions | conflit classe |
| `(room_id, day, start_time, end_time)` | academic_sessions | conflit salle |
| `(subject_plan_id, group_number)` | academic_sessions | purge ciblée par plan/groupe |
| UNIQUE `(level_id, subject_id, teaching_type)` | subject_plans | lookup programme + contrainte |
| `(teacher_id, subject_id)` UNIQUE | teacher_subject | pré-filtre habilitations |

## 7.3 Optimisations Eloquent / requêtes
1. **Eager loading ciblé** au démarrage : `Level::with(['classRooms', 'subjectPlans.subject', 'subjectPlans.teachers'])`, `Room::all()`, `TeacherSubject::all()` → 3-4 requêtes globales, puis tout est en mémoire.
2. **Caches mémoire par tentative** : `subject → eligibleTeachers`, `roomType → rooms` calculés une fois.
3. **Écritures par lot** : `insert()` en un seul statement (pas de `create()` par ligne).
4. **Purge par périmètre** : `whereIn(class_room_id, …)->delete()` en une requête.
5. **Pas de N+1** : aucune requête pendant la boucle de placement (registres + DTO en mémoire).

## 7.4 Optimisations CPU / mémoire
1. **Bitmasks 64 bits** : `slotsPerDay ≤ 64` → chaque journée par ressource = 1 entier ; tests d'intervalle en 1 opération.
2. **Tri MRV** : réduit drastiquement les explorations inutiles.
3. **Budgets** : `timeBudget` par réparation + `MAX_ATTEMPTS` → temps de réponse plafonné.
4. **Mémoire** : DTO légers (pas d'instances Eloquent dans les registres), tableaux indexés par id.
5. **Transaction unique** : un seul commit groupé, faibles coûts de verrouillage.

## 7.5 Chronométrage prévisionnel (ordre de grandeur)
Grille type (5 jours × 16 créneaux de 30 min) : ~10⁵–10⁶ opérations O(1) par tentative → **< 100 ms en mémoire**, quelques secondes au pire avec réparations + restarts. L'asynchrone (Job) reste justifié pour ne pas bloquer le navigateur.

---

# ÉTAPE 8 — ÉVOLUTIVITÉ

## 8.1 Design extensible sans refonte

| Besoin futur | Solution | Impact actuel |
|---|---|---|
| Multi-établissements | colonne `school_id` sur les tables métier (ou table `schools` + FK) | **Aucun** : ne pas le faire tant que le besoin n'existe pas ; l'architecture Services est déjà neutre sur le périmètre (`levelIds` paramétré) |
| Multi-années/semestres | table `timetables` (ou `periods`) : `academic_sessions.timetable_id` | Les index/R7 sont déjà conçus sur un périmètre ; ajouter FK + filtres |
| Plusieurs EDT par période (A/B) | idem `timetables` + champ `variant` | idem |
| Nouvelles contraintes | nouvelle classe `Constraint` enregistrée | **Aucune modification du générateur** (principe O) |
| Nouveaux types de salles | élargir l'enum `rooms.type` ou table `room_types` | modif migration mineure |
| Nouveaux types de séances (TD ?) | élargir l'enum de `subject_plans.teaching_type` + mapping config + regroupements | R7/R17 paramétrés par config |
| Nouveaux rôles dynamiques | table `roles` + pivot `role_user` | refactor du check de rôle (trait centralisé) |
| Disponibilités enseignants | table `teacher_unavailabilities` + une `Constraint` | design déjà prévu en 6.1 |
| Programmes par classe | pivot `class_room_subject_plan` | `RequestBuilder` modifié localement |

## 8.2 Règle d'or
Toute extension future doit se greffer à **un point d'extension déjà prévu** :
- base : FK/index/CHECK (pas de refonte de schéma),
- algorithme : interface `PlacementStrategy`,
- contraintes : enregistrement d'une `Constraint`,
- périmètre : paramètres (`levelIds`, puis `timetableId`, puis `schoolId`).

Aucune modification ne doit toucher le cœur de l'orchestrateur ni la couche d'affichage.

---

# SYNTHÈSE

**Base de données** : 2 modifications de clés (M1, M2), 4 CHECK (M3-M6), 3 index composites (M7). Aucune table supplémentaire pour la v1. Trois décisions métier à valider d'abord : programme par niveau ou par classe (M10), enseignant TP distinct ou non (M9), sens de `sessions_per_week` pour les TP (R19).

**Règles métier** : 10 règles dures (générateur + base) et 7 règles souples (optimisation). 7 questions ouvertes listées (R18-R24) à confirmer avec l'établissement.

**Algorithme** : Greedy MRV + Min-Conflicts + restarts seedés, stratégie interchangeable derrière une interface.

**Architecture** : Services (`TimetableGenerator`, `ConflictChecker`, `Constraints/*`, registres DTO), Repositories, Events/Jobs, FormRequests, Policies — SOLID, aucun métier dans les Controllers.

**Performance** : registres bitmask, pré-filtrage, eager loading en 3-4 requêtes, écritures par lot, budgets/tentatives → ~100 ms par tentative en mémoire ; Job asynchrone pour l'UI.

**Évolutivité** : chaque extension future (établissements, années, EDT multiples, nouvelles contraintes, nouveaux types) s'appuie sur un point d'extension déjà défini — sans refonte.

---

## DÉCISIONS À CONFIRMER AVANT IMPLÉMENTATION

| # | Question | Impact si non tranché |
|---|---|---|
| D1 | Programme identique pour toutes les classes d'un niveau ? | Le générateur suppose OUI (R19/M10) |
| D2 | `sessions_per_week` des TP = par groupe ou au total ? | Volume de séances calculé différemment (R19) |
| D3 | Un enseignant habilité matière peut-il faire les TP de cette matière ? | Pré-filtre enseignant par type (R18/M9) |
| D4 | `weekly_hours` : supprimer (dérivable) ou garder avec sémantique ? | Redondance ou clarté du reporting (M4 bis) |
| D5 | Créneaux interdits par enseignant dès la v1 ? | Sinon table `teacher_unavailabilities` reportée |
| D6 | Séances manuelles verrouillées contre la régénération ? | Ajout `is_locked` si OUI (R23) |
| D7 | Priorité de placement entre matières ? | Colonne `priority` si OUI (R22) |
