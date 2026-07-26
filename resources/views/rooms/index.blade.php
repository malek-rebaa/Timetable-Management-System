<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Gestion des Salles
        </h2>
        
        <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-room')">
            <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une Salle
        </x-form.button>
    </div>

    <x-ui.card>
        <x-ui.table :headers="['Nom', 'Type', 'Capacité', 'Actions']">
            <tr>
                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">A101</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">CLASSROOM</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">30 places</td>
                <td class="px-5 py-4">
                    <div class="flex gap-2">
                        <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        <button class="text-error-500 hover:text-error-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Lab 1</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">LABORATORY</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">25 places</td>
                <td class="px-5 py-4">
                    <div class="flex gap-2">
                        <button class="text-brand-500 hover:text-brand-600"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                        <button class="text-error-500 hover:text-error-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                </td>
            </tr>
        </x-ui.table>
    </x-ui.card>

    <x-ui.modal name="create-room" title="Ajouter une Salle">
        <form action="#" method="POST" class="flex flex-col gap-4">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">Nom de la salle</label>
                <x-form.input type="text" placeholder="Ex: A101" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">Type</label>
                <x-form.select>
                    <option value="CLASSROOM">Classe Normale</option>
                    <option value="LABORATORY">Laboratoire (TP)</option>
                    <option value="AMPHITHEATER">Amphithéâtre</option>
                </x-form.select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">Capacité</label>
                <x-form.input type="number" placeholder="30" />
            </div>
            
            <div class="mt-4 flex justify-end gap-3">
                <x-form.button type="button" variant="secondary" @click="show = false">Annuler</x-form.button>
                <x-form.button type="submit" variant="primary">Créer</x-form.button>
            </div>
        </form>
    </x-ui.modal>
</x-layout.app>
