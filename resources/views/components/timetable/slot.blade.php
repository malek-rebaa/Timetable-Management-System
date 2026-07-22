@props([
    'type' => 'THEORY', // THEORY, TP, CONFLICT
    'subject' => '',
    'teacher' => '',
    'room' => '',
    'group' => null, // e.g. '1', '2', etc.
    'startTime' => null, // e.g. 8.5 for 08:30 (optional, if using absolute positioning based on JS, otherwise handled by grid)
    'duration' => 2, // in hours
])

@php
    $typeClasses = [
        'THEORY' => 'slot-theory',
        'TP' => 'slot-tp',
        'CONFLICT' => 'slot-conflict',
    ];
    $classes = $typeClasses[$type] ?? $typeClasses['THEORY'];
    
    // Si on veut positionner de manière absolue (style="top: X%; height: Y%;") :
    // On suppose que la cellule mère a min-h-[100px] (1 heure)
    // C'est juste un exemple, dans une vraie implémentation, le backend fournirait le top et la hauteur.
@endphp

<div {{ $attributes->merge(['class' => "timetable-slot $classes"]) }}>
    <div class="flex justify-between items-start gap-1">
        <h4 class="text-xs font-bold truncate" title="{{ $subject }}">{{ $subject }}</h4>
        @if($group)
            <span class="text-[10px] bg-white/50 px-1 rounded font-semibold text-dark">Gr {{ $group }}</span>
        @endif
    </div>
    
    <div class="flex flex-col gap-0.5 mt-auto">
        <div class="text-[11px] font-medium opacity-90 truncate flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            {{ $teacher }}
        </div>
        <div class="text-[11px] font-medium opacity-90 truncate flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            {{ $room }}
        </div>
    </div>
</div>
