<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['sessions', 'days', 'dayLabels', 'grid', 'className' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['sessions', 'days', 'dayLabels', 'grid', 'className' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Calcul des créneaux (uniquement les séances de début de créneau)
    $slotsData = $grid->slotsForDay($days[0] ?? 'MONDAY');

    // Indexer les séances : day|startTime => [sessions]
    $sessionsByDaySlot = [];
    foreach ($sessions as $session) {
        $normalizedStart = \Carbon\Carbon::parse($session->start_time)->format('H:i');
        $key = $session->day . '|' . $normalizedStart;
        $sessionsByDaySlot[$key][] = $session;
    }

    // Palette de couleurs par matière (pour distinguer visuellement)
    $colorPalette = [
        'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
        'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20',
        'border-violet-400 bg-violet-50 dark:bg-violet-900/20',
        'border-orange-400 bg-orange-50 dark:bg-orange-900/20',
        'border-rose-400 bg-rose-50 dark:bg-rose-900/20',
        'border-cyan-400 bg-cyan-50 dark:bg-cyan-900/20',
        'border-amber-400 bg-amber-50 dark:bg-amber-900/20',
        'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20',
    ];
    $subjectColorMap = [];
    $colorIndex = 0;
?>

<?php if($sessions->count() > 0): ?>

    
    <?php if($className): ?>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-6 bg-brand-500 rounded-full"></div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white"><?php echo e($className); ?></h3>
        </div>
    <?php endif; ?>

    <div class="w-full overflow-hidden">
        <table class="w-full border-collapse table-fixed text-[11px]">
            <colgroup>
                
                <col style="width: 80px;">
                
                <?php $__currentLoopData = $slotsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <col>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </colgroup>

            
            <thead>
                <tr>
                    <th class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-1 text-center text-[10px] font-semibold text-gray-500 uppercase">
                        Jour \ Heure
                    </th>
                    <?php $__currentLoopData = $slotsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-1 text-center text-[10px] font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            <?php echo e($slot['start']); ?>

                            <span class="block text-[9px] font-normal text-gray-400"><?php echo e($slot['end']); ?></span>
                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>

            
            <tbody>
                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        
                        <td class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-1 text-center font-bold text-[11px] text-gray-700 dark:text-gray-200 whitespace-nowrap">
                            <?php echo e($dayLabels[$day] ?? $day); ?>

                        </td>

                        
                        <?php $__currentLoopData = $slotsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slotIndex => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $key = $day . '|' . $slot['start'];
                                $slotSessions = $sessionsByDaySlot[$key] ?? [];
                            ?>
                            <td class="border border-gray-200 dark:border-gray-700 p-0.5 align-top bg-white dark:bg-gray-900 min-h-[56px]" style="height:60px;">
                                <?php if(count($slotSessions) > 0): ?>
                                    <?php $__currentLoopData = $slotSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $subjectName = $session->subjectPlan->subject->name ?? '?';
                                            if (!isset($subjectColorMap[$subjectName])) {
                                                $subjectColorMap[$subjectName] = $colorPalette[$colorIndex % count($colorPalette)];
                                                $colorIndex++;
                                            }
                                            $color = $subjectColorMap[$subjectName];
                                            $isTP = $session->subjectPlan->teaching_type === 'TP';
                                        ?>
                                        <div class="h-full w-full rounded border-l-2 <?php echo e($color); ?> p-1 flex flex-col items-center justify-center text-center gap-0.5">
                                            
                                            <div class="font-bold text-[10px] text-gray-800 dark:text-gray-100 leading-tight">
                                                <?php echo e($subjectName); ?>

                                                <?php if($isTP && $session->group_number): ?>
                                                    <span class="text-[9px] font-normal text-gray-500">(G<?php echo e($session->group_number); ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if($session->teacher): ?>
                                                <?php
                                                    $initial = mb_strtoupper(mb_substr($session->teacher->first_name, 0, 1));
                                                    $lastName = $session->teacher->last_name;
                                                ?>
                                                <div class="text-[9px] text-gray-500 dark:text-gray-400 leading-tight">
                                                    <?php echo e($initial); ?>. <?php echo e($lastName); ?>

                                                </div>
                                            <?php else: ?>
                                                <div class="text-[9px] text-gray-400 leading-tight">-</div>
                                            <?php endif; ?>
                                            
                                            <?php if($session->room): ?>
                                                <div class="text-[9px] text-gray-400 dark:text-gray-500 leading-tight">
                                                    <?php echo e($session->room->name); ?>

                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if($session->is_locked): ?>
                                                <span class="text-[8px] text-gray-400">🔒</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    
                                    <div class="h-full w-full"></div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    
    <div class="py-8 text-center">
        <i data-lucide="calendar" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
        <p class="text-sm text-gray-400">Aucune séance</p>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\timetable-app\resources\views/timetable/partials/grid.blade.php ENDPATH**/ ?>