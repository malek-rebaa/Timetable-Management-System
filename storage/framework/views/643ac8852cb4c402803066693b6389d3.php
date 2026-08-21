<?php if (isset($component)) { $__componentOriginalcf7e1d4949dbd350ec830409f7127ebc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf7e1d4949dbd350ec830409f7127ebc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $days = config('timetable.days');
        $dayLabels = [
            'MONDAY' => 'Lundi',
            'TUESDAY' => 'Mardi',
            'WEDNESDAY' => 'Mercredi',
            'THURSDAY' => 'Jeudi',
            'FRIDAY' => 'Vendredi',
            'SATURDAY' => 'Samedi',
        ];
        $grid = new \App\Services\Timetable\SlotGrid();
    ?>

    
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Emploi du Temps
        </h2>

        <div class="flex flex-wrap gap-2">
            
            <div x-data="{ viewMode: '<?php echo e(request('view_mode', 'class')); ?>' }" class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                <button @click="viewMode='class'; updateUrl('view_mode', 'class')"
                    :class="viewMode === 'class' ? 'bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500'"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                    Par Classe
                </button>
            </div>

            
            <?php if(isset($classRooms) && $classRooms->count()): ?>
                <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'filter_class','xData' => '{ current: \''.e(request('filter_class', '')).'\' }','@change' => 'updateUrl(\'filter_class\', $event.target.value)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'filter_class','x-data' => '{ current: \''.e(request('filter_class', '')).'\' }','@change' => 'updateUrl(\'filter_class\', $event.target.value)']); ?>
                    <option value="">Toutes les classes</option>
                    <?php $__currentLoopData = $classRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e(request('filter_class') == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->name); ?> (<?php echo e($class->level->name); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
            <?php endif; ?>

            

            
            <?php if(isset($timetables) && $timetables->count()): ?>
                <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'timetable_id','xData' => '{ current: \''.e(request('timetable_id', '')).'\' }','@change' => 'updateUrl(\'timetable_id\', $event.target.value)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'timetable_id','x-data' => '{ current: \''.e(request('timetable_id', '')).'\' }','@change' => 'updateUrl(\'timetable_id\', $event.target.value)']); ?>
                    <option value="">Saisie manuelle</option>
                    <?php $__currentLoopData = $timetables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($tt->id); ?>" <?php echo e(request('timetable_id') == $tt->id ? 'selected' : ''); ?>>
                            <?php echo e($tt->name); ?>

                            <?php if($tt->status === 'COMPLETED'): ?>
                                <span class="text-xs text-success-500">✓</span>
                            <?php elseif($tt->status === 'FAILED'): ?>
                                <span class="text-xs text-error-500">✗</span>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (! (auth()->user()->role === 'TEACHER')): ?>
            <?php if($selectedTimetable): ?>
                <form method="POST" action="<?php echo e(route('timetable.destroy', $selectedTimetable)); ?>"
                    onsubmit="return confirm('Supprimer cet emploi du temps et toutes ses séances, y compris les séances verrouillées ?');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['type' => 'submit','variant' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'danger']); ?>
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
                </form>
            <?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['variant' => 'primary','@click' => '$dispatch(\'open-modal\', \'add-session\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','@click' => '$dispatch(\'open-modal\', \'add-session\')']); ?>
                <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle Séance
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['variant' => 'secondary','xOn:click' => 'generateTimetable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','x-on:click' => 'generateTimetable']); ?>
                <i data-lucide="zap" class="w-4 h-4"></i> Générer
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(session('generation_errors')): ?>
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 p-4 dark:bg-error-900/20 dark:border-error-800">
            <h4 class="text-sm font-semibold text-error-700 dark:text-error-400 mb-2">
                <i data-lucide="alert-circle" class="w-4 h-4 inline"></i>
                Erreurs de génération
            </h4>
            <ul class="list-disc list-inside text-xs text-error-600 dark:text-error-300">
                <?php $__currentLoopData = session('generation_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('generation_success')): ?>
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 p-3 dark:bg-success-900/20 dark:border-success-800">
            <span class="text-xs font-medium text-success-700 dark:text-success-400">
                <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
                <?php echo e(session('generation_success')); ?>

            </span>
        </div>
    <?php endif; ?>

    <?php if(isset($selectedTimetable) && in_array($selectedTimetable->status, ['PENDING', 'RUNNING'], true)): ?>
        <div class="mb-4 rounded-lg bg-warning-50 border border-warning-200 p-3 dark:bg-warning-900/20 dark:border-warning-800">
            <span class="text-xs font-medium text-warning-700 dark:text-warning-300">
                <i data-lucide="loader-circle" class="w-4 h-4 inline animate-spin"></i>
                Génération en cours pour <?php echo e($selectedTimetable->name); ?>. La page se rafraîchira automatiquement.
            </span>
        </div>
    <?php endif; ?>

    
    <?php
        $showAllClasses = ! request()->filled('filter_class');
        $timetableGroups = $showAllClasses
            ? $classRooms->mapWithKeys(fn ($classRoom) => [
                $classRoom->id => $sessions->where('class_room_id', $classRoom->id),
            ])
            : collect([null => $sessions]);
    ?>

    <div class="space-y-6">
    <?php $__currentLoopData = $timetableGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classRoomId => $classSessions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($showAllClasses): ?>
            <?php
                $classRoom = $classRooms->firstWhere('id', $classRoomId);
            ?>
            <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white">
                <?php echo e($classRoom->name); ?>

                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?php echo e($classRoom->level->name); ?>)</span>
            </h3>
        <?php endif; ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                <?php if($classSessions->isNotEmpty()): ?>
                    <?php
                        // Regrouper les séances par jour puis par créneau
                        $slotsPerDay = $grid->slotsPerDay;
                        $maxSlots = $grid->slotsPerDay;
                        $slotsData = [];
                        foreach($grid->slotsForDay($days[0] ?? 'MONDAY') as $index => $slot) {
                            $slotsData[$index] = $slot;
                        }

                        // Organiser: day -> slotIndex -> [sessions]
                        $organized = [];
                        foreach($days as $day) {
                            $organized[$day] = array_fill(0, $maxSlots, []);
                        }

                        foreach($classSessions as $session) {
                            $normalizedStart = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                            $startSlot = $grid->indexOf($session->day, $normalizedStart);
                            $duration = $grid->durationToSlots($session->subjectPlan->session_duration ?? 120);
                            if ($startSlot !== null) {
                                for ($i = 0; $i < $duration && $startSlot + $i < $maxSlots; $i++) {
                                    $organized[$session->day][$startSlot + $i][] = $session;
                                }
                            }
                        }

                        // Déterminer les séances uniques par créneau de début
                        $uniqueByStart = [];
                        foreach($classSessions as $session) {
                            $key = $session->day . '|' . \Carbon\Carbon::parse($session->start_time)->format('H:i');
                            if (!isset($uniqueByStart[$key])) {
                                $uniqueByStart[$key] = [];
                            }
                            $uniqueByStart[$key][] = $session;
                        }
                    ?>

                    <div class="grid gap-px bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800"
                        style="grid-template-columns: 100px repeat(<?php echo e(count($days)); ?>, 1fr);">

                        
                        <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-xs font-semibold text-gray-500 uppercase">Heure</div>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-xs font-semibold text-gray-800 dark:text-white uppercase">
                                <?php echo e($dayLabels[$day] ?? $day); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php
                            $coveredSlots = [];
                        ?>

                        
                        <?php $__currentLoopData = $slotsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slotIndex => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white dark:bg-gray-900 p-2 text-center text-xs font-medium text-gray-500 flex items-center justify-center border-r border-gray-100 dark:border-gray-800">
                                <?php echo e($slot['start']); ?> - <?php echo e($slot['end']); ?>

                            </div>

                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    if (isset($coveredSlots[$day][$slotIndex]) && $coveredSlots[$day][$slotIndex] > 0) {
                                        continue;
                                    }

                                    $key = $day . '|' . $slot['start'];
                                    $slotSessions = $uniqueByStart[$key] ?? [];
                                    $maxSpan = 1;

                                    if (count($slotSessions) > 0) {
                                        foreach ($slotSessions as $session) {
                                            $start = \Carbon\Carbon::parse($session->start_time);
                                            $end = \Carbon\Carbon::parse($session->end_time);
                                            $durationMinutes = $start->diffInMinutes($end);
                                            $duration = (int) ceil($durationMinutes / 30);
                                            
                                            if ($duration > $maxSpan) {
                                                $maxSpan = $duration;
                                            }
                                        }

                                        if ($maxSpan > 1) {
                                            for ($i = 1; $i < $maxSpan; $i++) {
                                                $coveredSlots[$day][$slotIndex + $i] = 1;
                                            }
                                        }
                                    }
                                ?>
                                <div class="bg-white dark:bg-gray-900 p-1 min-h-[60px]" style="<?php echo e($maxSpan > 1 ? 'grid-row: span ' . $maxSpan . ';' : ''); ?>">
                                    <?php if(count($slotSessions) > 0): ?>
                                        <div class="flex flex-col gap-1 h-full">
                                            <?php $__currentLoopData = $slotSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $isTheory = $session->subjectPlan->teaching_type === 'THEORY';
                                                    $colorClass = $isTheory
                                                        ? 'bg-brand-50 border-brand-500 text-brand-600'
                                                        : 'bg-success-50 border-success-500 text-success-600';
                                                    $typeLabel = $isTheory ? 'THEORY' : 'TP';
                                                ?>
                                                <div class="rounded-md border-l-4 <?php echo e($colorClass); ?> p-2 shadow-theme-xs text-xs h-full flex flex-col justify-center">
                                                    <div class="font-bold mb-0.5">
                                                        <?php echo e($session->subjectPlan->subject->name ?? '-'); ?>

                                                        <?php if(!$isTheory && $session->group_number): ?>
                                                            (G<?php echo e($session->group_number); ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-gray-600 dark:text-gray-300 flex items-center gap-1 mb-0.5">
                                                        <i data-lucide="user" class="w-3 h-3"></i>
                                                        <?php echo e($session->teacher ? $session->teacher->first_name . ' ' . $session->teacher->last_name : '-'); ?>

                                                    </div>
                                                    <?php if($session->room): ?>
                                                        <div class="text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                            <?php echo e($session->room->name); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if($session->is_locked): ?>
                                                        <div class="mt-1 text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded inline-block w-max">
                                                            🔒 Verrouillée
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    
                    <div class="<?php echo e($showAllClasses ? 'py-10' : 'py-16'); ?> text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                            <i data-lucide="calendar" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Aucune séance</h3>
                        <p class="text-sm text-gray-500 mb-4">Ajoutez une séance manuellement ou générez un emploi du temps.</p>
                        <?php if (! ($showAllClasses || auth()->user()->role === 'TEACHER')): ?>
                        <div class="flex justify-center gap-2">
                            <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['variant' => 'primary','@click' => '$dispatch(\'open-modal\', \'add-session\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','@click' => '$dispatch(\'open-modal\', \'add-session\')']); ?>
                                <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une séance
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['variant' => 'secondary','xOn:click' => 'generateTimetable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','x-on:click' => 'generateTimetable']); ?>
                                <i data-lucide="zap" class="w-4 h-4"></i> Générer automatiquement
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal7762953202be6518eecd1cfbd075bf2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7762953202be6518eecd1cfbd075bf2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.modal','data' => ['name' => 'add-session','title' => 'Nouvelle séance','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'add-session','title' => 'Nouvelle séance','maxWidth' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('lg')]); ?>
        <?php if (! (auth()->user()->role === 'TEACHER')): ?>
        <form action="<?php echo e(route('timetable.sessions.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php if(request('timetable_id')): ?>
                <input type="hidden" name="timetable_id" value="<?php echo e(request('timetable_id')); ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan de cours</label>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'subject_plan_id','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'subject_plan_id','required' => true]); ?>
                        <option value="">Sélectionner un plan</option>
                        <?php $__currentLoopData = $subjectPlans ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($plan->id); ?>">
                                <?php echo e($plan->subject->name); ?> - <?php echo e($plan->level->name); ?> (<?php echo e($plan->teaching_type); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['subject_plan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enseignant</label>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'teacher_id','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'teacher_id','required' => true]); ?>
                        <option value="">Sélectionner</option>
                        <?php $__currentLoopData = $teachers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($teacher->id); ?>">
                                <?php echo e($teacher->first_name); ?> <?php echo e($teacher->last_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classe</label>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'class_room_id','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'class_room_id','required' => true]); ?>
                        <option value="">Sélectionner</option>
                        <?php $__currentLoopData = $classRooms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class->id); ?>">
                                <?php echo e($class->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['class_room_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salle</label>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'room_id']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'room_id']); ?>
                        <option value="">Sélectionner</option>
                        <?php $__currentLoopData = $rooms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>">
                                <?php echo e($room->name); ?> (<?php echo e($room->type); ?> - <?php echo e($room->capacity); ?> places)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['room_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div x-data="{ showGroup: false }"
                    x-show="showGroup"
                    class="">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Groupe TP</label>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'group_number','xBind:value' => 'showGroup ? \'1\' : \'\'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'group_number','x-bind:value' => 'showGroup ? \'1\' : \'\'']); ?>
                        <option value="">--</option>
                        <option value="1">Groupe 1</option>
                        <option value="2">Groupe 2</option>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jour</label>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['name' => 'day','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'day','required' => true]); ?>
                        <option value="">Sélectionner</option>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($day); ?>"><?php echo e($dayLabels[$day]); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Heure début</label>
                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['name' => 'start_time','type' => 'time','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'start_time','type' => 'time','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Heure fin</label>
                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['name' => 'end_time','type' => 'time','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'end_time','type' => 'time','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                    <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-error-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="flex items-center gap-2 pt-4">
                    <input type="checkbox" name="is_locked" id="is_locked" value="1"
                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    <label for="is_locked" class="text-sm text-gray-700 dark:text-gray-300">
                        Verrouiller (ne pas modifier à la génération)
                    </label>
                </div>
            </div>

            
            <?php $__errorArgs = ['conflict'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mt-4 rounded-md bg-error-50 border border-error-200 p-3 dark:bg-error-900/20">
                    <span class="text-xs text-error-700 dark:text-error-400"><?php echo e($message); ?></span>
                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="mt-6 flex justify-end gap-2">
                <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['variant' => 'secondary','type' => 'button','@click' => '$dispatch(\'close-modal\', \'add-session\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','@click' => '$dispatch(\'close-modal\', \'add-session\')']); ?>
                    Annuler
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['variant' => 'primary','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'submit']); ?>
                    Enregistrer
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7762953202be6518eecd1cfbd075bf2f)): ?>
<?php $attributes = $__attributesOriginal7762953202be6518eecd1cfbd075bf2f; ?>
<?php unset($__attributesOriginal7762953202be6518eecd1cfbd075bf2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7762953202be6518eecd1cfbd075bf2f)): ?>
<?php $component = $__componentOriginal7762953202be6518eecd1cfbd075bf2f; ?>
<?php unset($__componentOriginal7762953202be6518eecd1cfbd075bf2f); ?>
<?php endif; ?>

    
    <script>
        function generateTimetable() {
            if (!confirm('Générer l\'emploi du temps ? Les séances non verrouillées seront remplacées.')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(route("timetable.generate")); ?>';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);

            const params = new URLSearchParams(window.location.search);
            if (params.get('timetable_id')) {
                const tt = document.createElement('input');
                tt.type = 'hidden';
                tt.name = 'timetable_id';
                tt.value = params.get('timetable_id');
                form.appendChild(tt);
            }
            if (params.get('filter_class')) {
                const fc = document.createElement('input');
                fc.type = 'hidden';
                fc.name = 'filter_class';
                fc.value = params.get('filter_class');
                form.appendChild(fc);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        function updateUrl(key, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            window.location.href = url.toString();
        }

        <?php if(isset($selectedTimetable) && in_array($selectedTimetable->status, ['PENDING', 'RUNNING'], true)): ?>
        setTimeout(() => window.location.reload(), 5000);
        <?php endif; ?>
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf7e1d4949dbd350ec830409f7127ebc)): ?>
<?php $attributes = $__attributesOriginalcf7e1d4949dbd350ec830409f7127ebc; ?>
<?php unset($__attributesOriginalcf7e1d4949dbd350ec830409f7127ebc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf7e1d4949dbd350ec830409f7127ebc)): ?>
<?php $component = $__componentOriginalcf7e1d4949dbd350ec830409f7127ebc; ?>
<?php unset($__componentOriginalcf7e1d4949dbd350ec830409f7127ebc); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\timetable-app\resources\views/timetable/index.blade.php ENDPATH**/ ?>