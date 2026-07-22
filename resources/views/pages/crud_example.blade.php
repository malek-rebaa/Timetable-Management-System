<x-layout.app>
    <x-slot name="header">
        Gestion des Classes
    </x-slot>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="w-full lg:max-w-sm">
            <x-form.input type="text" placeholder="Rechercher une classe..." />
        </div>

        <x-button variant="primary" x-data="" x-on:click="$dispatch('open-modal', 'create-classroom')">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouvelle Classe
        </x-button>
    </div>

    <x-card title="Liste des classes" subtitle="Gestion des classes du système">
        <x-table>
            <x-slot name="head">
                <x-table.th>Nom de la classe</x-table.th>
                <x-table.th>Niveau</x-table.th>
                <x-table.th>Capacité</x-table.th>
                <x-table.th>Statut</x-table.th>
                <x-table.th class="text-right">Actions</x-table.th>
            </x-slot>

            <x-table.tr>
                <x-table.td class="font-medium text-dark dark:text-white">Terminale S1</x-table.td>
                <x-table.td>Lycée (Terminale)</x-table.td>
                <x-table.td>30 élèves</x-table.td>
                <x-table.td><x-badge color="success">Active</x-badge></x-table.td>
                <x-table.td class="space-x-2 text-right">
                    <button class="text-info transition-colors hover:text-info-emphasis">Éditer</button>
                    <button class="text-error transition-colors hover:text-error-emphasis">Supprimer</button>
                </x-table.td>
            </x-table.tr>

            <x-table.tr>
                <x-table.td class="font-medium text-dark dark:text-white">1ère L2</x-table.td>
                <x-table.td>Lycée (1ère)</x-table.td>
                <x-table.td>25 élèves</x-table.td>
                <x-table.td><x-badge color="success">Active</x-badge></x-table.td>
                <x-table.td class="space-x-2 text-right">
                    <button class="text-info transition-colors hover:text-info-emphasis">Éditer</button>
                    <button class="text-error transition-colors hover:text-error-emphasis">Supprimer</button>
                </x-table.td>
            </x-table.tr>
        </x-table>
    </x-card>

    <x-modal name="create-classroom" title="Créer une nouvelle classe" maxWidth="md">
        <form action="#" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <x-form.label value="Nom de la classe" />
                    <x-form.input type="text" name="name" required placeholder="ex: Terminale S1" />
                </div>

                <div>
                    <x-form.label value="Niveau" />
                    <x-form.select name="level_id">
                        <option value="">Sélectionnez un niveau</option>
                        <option value="1">Lycée (Terminale)</option>
                        <option value="2">Lycée (1ère)</option>
                    </x-form.select>
                </div>

                <div>
                    <x-form.label value="Capacité maximale" />
                    <x-form.input type="number" name="capacity" value="30" />
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <x-form.checkbox name="is_active" checked id="is_active" />
                    <x-form.label for="is_active" class="!mb-0" value="Classe active" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-button variant="outline" x-on:click="$dispatch('close-modal', 'create-classroom')">
                    Annuler
                </x-button>
                <x-button variant="primary" type="submit">
                    Enregistrer
                </x-button>
            </div>
        </form>
    </x-modal>
</x-layout.app>
