<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Niveaux & Classes
        </h2>
        
        <div class="flex gap-2">
            <x-form.button variant="secondary" x-data @click="$dispatch('open-modal', 'create-level')">
                <i data-lucide="layers" class="w-4 h-4"></i> Nouveau Niveau
            </x-form.button>
            <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-class')">
                <i data-lucide="users" class="w-4 h-4"></i> Nouvelle Classe
            </x-form.button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Niveaux -->
        <x-ui.card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Liste des Niveaux</h3>
            </x-slot:header>
            <x-ui.table :headers="['Nom du niveau', 'Actions']">
                <tr>
                    <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Niveau 1</td>
                    <td class="px-5 py-4">
                        <div class="flex gap-2">
                            <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
            </x-ui.table>
        </x-ui.card>

        <!-- Classes -->
        <x-ui.card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Liste des Classes</h3>
            </x-slot:header>
            <x-ui.table :headers="['Classe', 'Niveau', 'Actions']">
                <tr>
                    <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">INFO1A</td>
                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">Niveau 1</td>
                    <td class="px-5 py-4">
                        <div class="flex gap-2">
                            <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">INFO1B</td>
                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">Niveau 1</td>
                    <td class="px-5 py-4">
                        <div class="flex gap-2">
                            <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
            </x-ui.table>
        </x-ui.card>
    </div>

    <!-- Modals go here... -->
</x-layout.app>
