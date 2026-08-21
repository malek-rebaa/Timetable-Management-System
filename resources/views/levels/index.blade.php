<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">Niveaux et classes</h2>
            <p class="mt-1 text-sm text-gray-500">Organisez les classes par niveau d’étude.</p>
        </div>
        <div class="flex gap-2">
            <x-form.button variant="secondary" x-data @click="$dispatch('open-modal', 'create-level')"><i data-lucide="layers" class="w-4 h-4"></i> Nouveau niveau</x-form.button>
            <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-class')"><i data-lucide="users" class="w-4 h-4"></i> Nouvelle classe</x-form.button>
        </div>
    </div>

    @if(session('success'))<div class="mb-4 rounded-lg border border-success-200 bg-success-50 p-3 text-sm text-success-700">{{ session('success') }}</div>@endif
    @if(session('error') || $errors->any())<div class="mb-4 rounded-lg border border-error-200 bg-error-50 p-3 text-sm text-error-700">{{ session('error') ?? $errors->first() }}</div>@endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card>
            <x-slot:header><h3 class="text-lg font-semibold text-gray-800 dark:text-white">Niveaux</h3></x-slot:header>
            <x-ui.table :headers="['Niveau', 'Classes', 'Programmes', 'Actions']">
                @forelse($levels as $level)
                    <tr>
                        <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">{{ $level->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $level->class_rooms_count }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $level->subject_plans_count }}</td>
                        <td class="px-5 py-4"><div class="flex gap-3">
                            <button class="text-brand-500 hover:text-brand-600" x-data @click="$dispatch('open-modal', 'edit-level-{{ $level->id }}')"><i data-lucide="edit-2" class="h-4 w-4"></i></button>
                            <form method="POST" action="{{ route('levels.destroy', $level) }}" onsubmit="return confirm('Supprimer ce niveau ?');">@csrf @method('DELETE')<button class="text-error-500 hover:text-error-600" @disabled($level->class_rooms_count || $level->subject_plans_count)><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                        </div></td>
                    </tr>
                    <x-ui.modal name="edit-level-{{ $level->id }}" title="Modifier le niveau" maxWidth="md"><form method="POST" action="{{ route('levels.update', $level) }}" class="space-y-4">@csrf @method('PUT')<div><label class="mb-1 block text-sm font-medium">Nom du niveau</label><x-form.input name="name" value="{{ $level->name }}" required /></div><div class="flex justify-end gap-2"><x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'edit-level-{{ $level->id }}')">Annuler</x-form.button><x-form.button type="submit">Enregistrer</x-form.button></div></form></x-ui.modal>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Aucun niveau enregistré.</td></tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header><h3 class="text-lg font-semibold text-gray-800 dark:text-white">Classes</h3></x-slot:header>
            <x-ui.table :headers="['Classe', 'Niveau', 'Effectif', 'Séances', 'Actions']">
                @forelse($classRooms as $classRoom)
                    <tr>
                        <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">{{ $classRoom->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $classRoom->level->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $classRoom->student_count }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $classRoom->academic_sessions_count }}</td>
                        <td class="px-5 py-4"><div class="flex gap-3">
                            <button class="text-brand-500 hover:text-brand-600" x-data @click="$dispatch('open-modal', 'edit-class-{{ $classRoom->id }}')"><i data-lucide="edit-2" class="h-4 w-4"></i></button>
                            <form method="POST" action="{{ route('classes.destroy', $classRoom) }}" onsubmit="return confirm('Supprimer cette classe ?');">@csrf @method('DELETE')<button class="text-error-500 hover:text-error-600" @disabled($classRoom->academic_sessions_count)><i data-lucide="trash-2" class="h-4 w-4"></i></button></form>
                        </div></td>
                    </tr>
                    <x-ui.modal name="edit-class-{{ $classRoom->id }}" title="Modifier la classe" maxWidth="md"><form method="POST" action="{{ route('classes.update', $classRoom) }}" class="space-y-4">@csrf @method('PUT')<div><label class="mb-1 block text-sm font-medium">Niveau</label><x-form.select name="level_id" required>@foreach($levels as $level)<option value="{{ $level->id }}" @selected($classRoom->level_id === $level->id)>{{ $level->name }}</option>@endforeach</x-form.select></div><div><label class="mb-1 block text-sm font-medium">Nom</label><x-form.input name="name" value="{{ $classRoom->name }}" required /></div><div><label class="mb-1 block text-sm font-medium">Effectif</label><x-form.input type="number" min="1" name="student_count" value="{{ $classRoom->student_count }}" required /></div><div class="flex justify-end gap-2"><x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'edit-class-{{ $classRoom->id }}')">Annuler</x-form.button><x-form.button type="submit">Enregistrer</x-form.button></div></form></x-ui.modal>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Aucune classe enregistrée.</td></tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>

    <x-ui.modal name="create-level" title="Nouveau niveau" maxWidth="md"><form method="POST" action="{{ route('levels.store') }}" class="space-y-4">@csrf<div><label class="mb-1 block text-sm font-medium">Nom du niveau</label><x-form.input name="name" placeholder="Ex. Licence 1" required /></div><div class="flex justify-end gap-2"><x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'create-level')">Annuler</x-form.button><x-form.button type="submit">Créer</x-form.button></div></form></x-ui.modal>
    <x-ui.modal name="create-class" title="Nouvelle classe" maxWidth="md"><form method="POST" action="{{ route('classes.store') }}" class="space-y-4">@csrf<div><label class="mb-1 block text-sm font-medium">Niveau</label><x-form.select name="level_id" required><option value="">Sélectionner</option>@foreach($levels as $level)<option value="{{ $level->id }}">{{ $level->name }}</option>@endforeach</x-form.select></div><div><label class="mb-1 block text-sm font-medium">Nom de la classe</label><x-form.input name="name" placeholder="Ex. INFO1A" required /></div><div><label class="mb-1 block text-sm font-medium">Effectif</label><x-form.input type="number" min="1" name="student_count" value="30" required /></div><div class="flex justify-end gap-2"><x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'create-class')">Annuler</x-form.button><x-form.button type="submit">Créer</x-form.button></div></form></x-ui.modal>
</x-layout.app>
