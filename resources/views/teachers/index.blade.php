<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Gestion des Enseignants
        </h2>
        
        <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-teacher')">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Ajouter un Enseignant
        </x-form.button>
    </div>

    <x-ui.card>
        <x-ui.table :headers="['Nom', 'Email', 'Matières Affectées', 'Actions']">
            <tr>
                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">Ahmed</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">ahmed@ecole.com</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                    <span class="inline-block px-2 py-1 bg-brand-50 text-brand-600 rounded text-xs mr-1">Laravel</span>
                    <span class="inline-block px-2 py-1 bg-brand-50 text-brand-600 rounded text-xs">PHP</span>
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

    <!-- Modal Create Teacher -->
    <x-ui.modal name="create-teacher" title="Ajouter un Enseignant">
        <form action="#" method="POST" class="flex flex-col gap-4">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">Nom complet</label>
                <x-form.input type="text" placeholder="Entrez le nom de l'enseignant" />
                <p class="mt-1 text-xs text-gray-500">L'email et le mot de passe seront générés automatiquement.</p>
            </div>
            
            <div class="mt-4 flex justify-end gap-3">
                <x-form.button type="button" variant="secondary" @click="show = false">Annuler</x-form.button>
                <x-form.button type="submit" variant="primary">Créer</x-form.button>
            </div>
        </form>
    </x-ui.modal>

</x-layout.app>
