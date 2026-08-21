<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 class="text-title-md font-semibold text-gray-800 dark:text-white">Gestion des salles</h2><p class="mt-1 text-sm text-gray-500">Configurez les salles utilisables par le générateur.</p></div>
        <x-form.button x-data @click="$dispatch('open-modal', 'create-room')"><i data-lucide="plus" class="w-4 h-4"></i> Ajouter une salle</x-form.button>
    </div>

    @if(session('success'))<div class="mb-4 rounded-lg border border-success-200 bg-success-50 p-3 text-sm text-success-700">{{ session('success') }}</div>@endif
    @if(session('error') || $errors->any())<div class="mb-4 rounded-lg border border-error-200 bg-error-50 p-3 text-sm text-error-700">{{ session('error') ?? $errors->first() }}</div>@endif

    <x-ui.card>
        <x-ui.table :headers="['Nom', 'Type', 'Capacité', 'Séances', 'Actions']">
            @forelse($rooms as $room)
                <tr>
                    <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">{{ $room->name }}</td>
                    <td class="px-5 py-4 text-sm"><span class="rounded bg-gray-100 px-2 py-1 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $room->type }}</span></td>
                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $room->capacity }} places</td>
                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $room->academic_sessions_count }}</td>
                    <td class="px-5 py-4"><div class="flex gap-3"><button class="text-brand-500 hover:text-brand-600" x-data @click="$dispatch('open-modal', 'edit-room-{{ $room->id }}')"><i data-lucide="edit-2" class="h-4 w-4"></i></button><form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Supprimer cette salle ?');">@csrf @method('DELETE')<button class="text-error-500 hover:text-error-600" @disabled($room->academic_sessions_count)><i data-lucide="trash-2" class="h-4 w-4"></i></button></form></div></td>
                </tr>
                <x-ui.modal name="edit-room-{{ $room->id }}" title="Modifier la salle" maxWidth="md"><form method="POST" action="{{ route('rooms.update', $room) }}" class="space-y-4">@csrf @method('PUT')<div><label class="mb-1 block text-sm font-medium">Nom</label><x-form.input name="name" value="{{ $room->name }}" required /></div><div><label class="mb-1 block text-sm font-medium">Type</label><x-form.select name="type" required><option value="CLASSROOM" @selected($room->type === 'CLASSROOM')>Classe normale</option><option value="LABORATORY" @selected($room->type === 'LABORATORY')>Laboratoire (TP)</option><option value="AMPHITHEATER" @selected($room->type === 'AMPHITHEATER')>Amphithéâtre</option></x-form.select></div><div><label class="mb-1 block text-sm font-medium">Capacité</label><x-form.input type="number" min="1" name="capacity" value="{{ $room->capacity }}" required /></div><div class="flex justify-end gap-2"><x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'edit-room-{{ $room->id }}')">Annuler</x-form.button><x-form.button type="submit">Enregistrer</x-form.button></div></form></x-ui.modal>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Aucune salle enregistrée.</td></tr>
            @endforelse
        </x-ui.table>
    </x-ui.card>

    <x-ui.modal name="create-room" title="Ajouter une salle" maxWidth="md"><form method="POST" action="{{ route('rooms.store') }}" class="space-y-4">@csrf<div><label class="mb-1 block text-sm font-medium">Nom</label><x-form.input name="name" placeholder="Ex. A101" required /></div><div><label class="mb-1 block text-sm font-medium">Type</label><x-form.select name="type" required><option value="CLASSROOM">Classe normale</option><option value="LABORATORY">Laboratoire (TP)</option><option value="AMPHITHEATER">Amphithéâtre</option></x-form.select></div><div><label class="mb-1 block text-sm font-medium">Capacité</label><x-form.input type="number" min="1" name="capacity" value="30" required /></div><div class="flex justify-end gap-2"><x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'create-room')">Annuler</x-form.button><x-form.button type="submit">Créer</x-form.button></div></form></x-ui.modal>
</x-layout.app>
