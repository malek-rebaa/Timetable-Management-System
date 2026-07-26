<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Matières & Programme Pédagogique
        </h2>
        
        <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-subject')">
            <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle Matière
        </x-form.button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Matières -->
        <div class="xl:col-span-1">
            <x-ui.card>
                <x-slot:header>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Matières</h3>
                </x-slot:header>
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    <li class="py-3 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-800 dark:text-white">Mathématiques</span>
                        <button class="text-brand-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                    </li>
                    <li class="py-3 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 px-2 rounded -mx-2">
                        <span class="text-sm font-medium text-brand-600 dark:text-brand-400">Réseaux</span>
                        <button class="text-brand-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                    </li>
                    <li class="py-3 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-800 dark:text-white">Développement Web</span>
                        <button class="text-brand-500"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                    </li>
                </ul>
            </x-ui.card>
        </div>

        <!-- Subject Plan -->
        <div class="xl:col-span-2">
            <x-ui.card>
                <x-slot:header>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Programme de : Réseaux</h3>
                        <x-form.button variant="secondary" class="py-1.5 px-3 text-xs">
                            <i data-lucide="plus" class="w-3 h-3"></i> Ajouter au plan
                        </x-form.button>
                    </div>
                </x-slot:header>
                
                <x-ui.table :headers="['Niveau', 'Type', 'Volume/Sem', 'Séances/Sem', 'Durée', 'Actions']">
                    <tr>
                        <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Niveau 1</td>
                        <td class="px-5 py-4 text-sm text-brand-600 font-semibold">THEORY</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">4 h</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">2</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">2 h</td>
                        <td class="px-5 py-4">
                            <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Niveau 2</td>
                        <td class="px-5 py-4 text-sm text-success-600 font-semibold">TP</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">6 h</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">2</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">3 h</td>
                        <td class="px-5 py-4">
                            <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        </td>
                    </tr>
                </x-ui.table>
            </x-ui.card>
        </div>
    </div>
</x-layout.app>
