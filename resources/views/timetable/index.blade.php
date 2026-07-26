<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Emploi du Temps
        </h2>
        
        <div class="flex gap-2">
            <x-form.select class="w-48">
                <option value="">Sélectionner une classe</option>
                <option value="INFO1A">INFO1A</option>
                <option value="INFO1B">INFO1B</option>
            </x-form.select>
            <x-form.button variant="primary">
                <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle Séance
            </x-form.button>
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                <!-- Grille Hebdomadaire -->
                <div class="grid grid-cols-6 gap-px bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                    
                    <!-- En-têtes (Jours) -->
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-sm font-semibold text-gray-500 uppercase">Heure</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-sm font-semibold text-gray-800 dark:text-white uppercase">Lundi</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-sm font-semibold text-gray-800 dark:text-white uppercase">Mardi</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-sm font-semibold text-gray-800 dark:text-white uppercase">Mercredi</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-sm font-semibold text-gray-800 dark:text-white uppercase">Jeudi</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-sm font-semibold text-gray-800 dark:text-white uppercase">Vendredi</div>

                    <!-- Ligne 08:30 - 10:30 -->
                    <div class="bg-white dark:bg-gray-900 p-3 text-center text-sm font-medium text-gray-500 flex items-center justify-center border-r border-gray-100 dark:border-gray-800">08:30 - 10:30</div>
                    
                    <!-- Lundi: Cours Theory -->
                    <div class="bg-white dark:bg-gray-900 p-2">
                        <div class="h-full rounded-lg bg-brand-50 border-l-4 border-brand-500 p-3 shadow-theme-xs">
                            <div class="text-xs font-bold text-brand-600 uppercase mb-1">Mathématiques</div>
                            <div class="text-xs text-gray-600 flex items-center gap-1 mb-1"><i data-lucide="user" class="w-3 h-3"></i> M. Ahmed</div>
                            <div class="text-xs text-gray-600 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> Salle A101</div>
                            <div class="mt-2 text-[10px] font-semibold text-brand-500 bg-brand-100 px-2 py-0.5 rounded inline-block">THEORY</div>
                        </div>
                    </div>
                    
                    <!-- Mardi: Vide -->
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                    
                    <!-- Mercredi: TP (Group 1 & 2) -->
                    <div class="bg-white dark:bg-gray-900 p-2">
                        <div class="flex flex-col gap-2 h-full">
                            <div class="rounded-lg bg-success-50 border-l-4 border-success-500 p-2 shadow-theme-xs">
                                <div class="text-xs font-bold text-success-600 uppercase">Réseaux (G1)</div>
                                <div class="text-[10px] text-gray-600">Lab 1 - M. Karim</div>
                            </div>
                            <div class="rounded-lg bg-warning-50 border-l-4 border-warning-500 p-2 shadow-theme-xs">
                                <div class="text-xs font-bold text-warning-600 uppercase">Dev Web (G2)</div>
                                <div class="text-[10px] text-gray-600">Lab 2 - M. Youssef</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Jeudi: Vide -->
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                    
                    <!-- Vendredi: Cours Theory -->
                    <div class="bg-white dark:bg-gray-900 p-2">
                        <div class="h-full rounded-lg bg-brand-50 border-l-4 border-brand-500 p-3 shadow-theme-xs">
                            <div class="text-xs font-bold text-brand-600 uppercase mb-1">Algorithmique</div>
                            <div class="text-xs text-gray-600 flex items-center gap-1 mb-1"><i data-lucide="user" class="w-3 h-3"></i> Mme. Sara</div>
                            <div class="text-xs text-gray-600 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> Amphi B</div>
                        </div>
                    </div>

                    <!-- Ligne 10:45 - 12:45 -->
                    <div class="bg-white dark:bg-gray-900 p-3 text-center text-sm font-medium text-gray-500 flex items-center justify-center border-r border-gray-100 dark:border-gray-800">10:45 - 12:45</div>
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                    <div class="bg-white dark:bg-gray-900 p-2"></div>
                </div>
            </div>
        </div>
    </x-ui.card>
</x-layout.app>
