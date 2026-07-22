<x-layout.app>
    <x-slot name="header">
        Tableau de bord
    </x-slot>

    <!-- Conflits Alert -->
    <x-alert type="warning" title="Conflits détectés dans l'emploi du temps">
        Il y a 3 sessions en conflit cette semaine (salles ou professeurs en double). 
        <a href="#" class="font-bold underline ml-1">Résoudre les conflits</a>
    </x-alert>

    <!-- KPI Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card 
            title="Enseignants" 
            value="42" 
            color="primary"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>' 
        />
        
        <x-stats-card 
            title="Classes" 
            value="18" 
            color="info"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>' 
        />
        
        <x-stats-card 
            title="Salles de cours" 
            value="24" 
            color="warning"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>' 
        />
        
        <x-stats-card 
            title="Séances cette semaine" 
            value="156" 
            color="success"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>' 
        />
    </div>

    <!-- Quick Timetable Preview -->
    <div class="mb-8">
        <x-timetable.grid>
            <!-- Here you would iterate over actual sessions. This is just a visual demo of slots in a cell -->
            <x-timetable.slot 
                type="THEORY" 
                subject="Mathématiques" 
                teacher="Mr. Dupont" 
                room="Salle 101" 
                style="top: 0; height: 100%;" 
            />
        </x-timetable.grid>
    </div>
</x-layout.app>
