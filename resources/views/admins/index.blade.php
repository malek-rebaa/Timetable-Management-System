<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Administrateurs
        </h2>
        
        <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-admin')">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Ajouter un Admin
        </x-form.button>
    </div>

    <x-ui.card>
        <x-ui.table :headers="['Nom', 'Email', 'Rôle', 'Actions']">
            <tr>
                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Directeur</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">directeur@ecole.com</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                    <span class="inline-block px-2 py-1 bg-error-50 text-error-600 rounded text-xs font-semibold uppercase">Super Admin</span>
                </td>
                <td class="px-5 py-4">
                    <!-- Cannot delete super admin easily -->
                </td>
            </tr>
            <tr>
                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Secrétariat</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">secretariat@ecole.com</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                    <span class="inline-block px-2 py-1 bg-brand-50 text-brand-600 rounded text-xs font-semibold uppercase">Admin</span>
                </td>
                <td class="px-5 py-4">
                    <div class="flex gap-2">
                        <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        <button class="text-error-500 hover:text-error-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                </td>
            </tr>
        </x-ui.table>
    </x-ui.card>
</x-layout.app>
