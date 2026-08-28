<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Gestion des Enseignants
        </h2>
        
        @auth
        @if(auth()->user()->role !== 'TEACHER')
        <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-teacher')">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Ajouter un Enseignant
        </x-form.button>
        @endif
        @endauth
    </div>

    {{-- Password Generated Success Alert --}}
    @if(session('generated_password') && session('generated_email'))
    <div class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/20 dark:bg-success-500/10" 
         x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-success-800 dark:text-success-400">✅ Compte créé / Mot de passe réinitialisé</h4>
                <p class="mt-2 text-sm text-success-700 dark:text-success-300">
                    <strong>Email :</strong> {{ session('generated_email') }}<br>
                    <strong>Mot de passe :</strong> <span class="font-mono font-bold text-success-800 dark:text-success-200">{{ session('generated_password') }}</span>
                </p>
                <p class="mt-1 text-xs text-success-600 dark:text-success-400">
                    ⚠️ Ce mot de passe ne sera plus jamais affiché. Transmettez-le de manière sécurisée à l'utilisateur.
                </p>
            </div>
            <button @click="show = false" class="text-success-600 hover:text-success-800 dark:text-success-400">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
    <div class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 dark:border-error-500/20 dark:bg-error-500/10">
        <div class="flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-error-600"></i>
            <p class="text-sm text-error-700 dark:text-error-300">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- Success Alert --}}
    @if(session('success') && !session('generated_password'))
    <div class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/20 dark:bg-success-500/10" 
         x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-success-600"></i>
                <p class="text-sm text-success-700 dark:text-success-300">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-success-600 hover:text-success-800">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
    @endif

    <x-ui.card>
        <x-ui.table :headers="['Nom', 'Email', 'Téléphone', 'Statut', 'Actions']">
            @forelse($teachers as $teacher)
            <tr>
                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                </td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $teacher->email }}</td>
                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $teacher->phone ?? '—' }}</td>
                <td class="px-5 py-4">
                    @if($teacher->is_active)
                        <span class="inline-block px-2 py-1 bg-success-50 text-success-600 rounded text-xs font-semibold uppercase">Disponible</span>
                    @else
                        <span class="inline-block px-2 py-1 bg-error-50 text-error-600 rounded text-xs font-semibold uppercase">Indisponible</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <div class="flex gap-2">
                        <button 
                            class="text-brand-500 hover:text-brand-600" 
                            title="Modifier"
                            x-data 
                            @click="$dispatch('open-modal', 'edit-teacher-{{ $teacher->id }}')">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </button>
                        @if(auth()->user()->role !== 'TEACHER')
                        <button 
                            class="text-warning-500 hover:text-warning-600" 
                            title="Réinitialiser le mot de passe"
                            x-data 
                            @click="$dispatch('open-modal', 'reset-password-{{ $teacher->id }}')">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </button>
                        <button 
                            class="text-error-500 hover:text-error-600" 
                            title="Supprimer"
                            x-data 
                            @click="$dispatch('open-modal', 'delete-teacher-{{ $teacher->id }}')">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Edit Modal --}}
            <x-ui.modal name="edit-teacher-{{ $teacher->id }}" title="Modifier l'Enseignant" maxWidth="lg">
                <form action="{{ route('teachers.update', $teacher) }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom</label>
                        <x-form.input type="text" name="first_name" value="{{ $teacher->first_name }}" required />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                        <x-form.input type="text" name="last_name" value="{{ $teacher->last_name }}" required />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                        <x-form.input type="text" name="phone" value="{{ $teacher->phone }}" placeholder="+216 XX XXX XXX" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                        <x-form.select name="is_active" required>
                            <option value="1" @selected($teacher->is_active)>Disponible (Actif)</option>
                            <option value="0" @selected(!$teacher->is_active)>Indisponible (Inactif)</option>
                        </x-form.select>
                    </div>
                    <fieldset>
                        <legend class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Matières enseignées</legend>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($subjects as $subject)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 p-2 text-sm dark:border-gray-700">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                        @checked($teacher->subjects->contains('id', $subject->id))>
                                    {{ $subject->name }}
                                </label>
                            @endforeach
                        </div>
                        @error('subject_ids') <span class="mt-1 block text-xs text-error-500">{{ $message }}</span> @enderror
                    </fieldset>
                    <div class="flex justify-end gap-3 mt-2">
                        <x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'edit-teacher-{{ $teacher->id }}')">
                            Annuler
                        </x-form.button>
                        <x-form.button type="submit" variant="primary">
                            Enregistrer
                        </x-form.button>
                    </div>
                </form>
            </x-ui.modal>

            {{-- Reset Password Modal --}}
            <x-ui.modal name="reset-password-{{ $teacher->id }}" title="Réinitialiser le mot de passe" maxWidth="md">
                <form action="{{ route('teachers.reset-password', $teacher) }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PUT')
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Êtes-vous sûr de vouloir réinitialiser le mot de passe de <strong>{{ $teacher->first_name }} {{ $teacher->last_name }}</strong> ?
                    </p>
                    <p class="text-xs text-warning-600 dark:text-warning-400">
                        Un nouveau mot de passe sécurisé sera généré et affiché une seule fois.
                    </p>
                    <div class="flex justify-end gap-3 mt-2">
                        <x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'reset-password-{{ $teacher->id }}')">
                            Annuler
                        </x-form.button>
                        <x-form.button type="submit" variant="primary">
                            Réinitialiser
                        </x-form.button>
                    </div>
                </form>
            </x-ui.modal>

            {{-- Delete Confirmation Modal --}}
            <x-ui.modal name="delete-teacher-{{ $teacher->id }}" title="Confirmer la suppression" maxWidth="md">
                <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Êtes-vous sûr de vouloir supprimer <strong>{{ $teacher->first_name }} {{ $teacher->last_name }}</strong> ?
                    </p>
                    <p class="text-xs text-error-600 dark:text-error-400">Cette action est irréversible.</p>
                    <div class="flex justify-end gap-3 mt-2">
                        <x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'delete-teacher-{{ $teacher->id }}')">
                            Annuler
                        </x-form.button>
                        <x-form.button type="submit" variant="danger">
                            Supprimer
                        </x-form.button>
                    </div>
                </form>
            </x-ui.modal>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Aucun enseignant trouvé.
                </td>
            </tr>
            @endforelse
        </x-ui.table>
    </x-ui.card>

    {{-- Create Teacher Modal --}}
    <x-ui.modal name="create-teacher" title="Ajouter un Enseignant" maxWidth="lg">
        <form action="{{ route('teachers.store') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom</label>
                <x-form.input type="text" name="first_name" placeholder="Prénom" required />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                <x-form.input type="text" name="last_name" placeholder="Nom" required />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                <x-form.input type="text" name="phone" placeholder="+216 XX XXX XXX" />
            </div>
            <fieldset>
                <legend class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Matières enseignées</legend>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach($subjects as $subject)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 p-2 text-sm dark:border-gray-700">
                            <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}">
                            {{ $subject->name }}
                        </label>
                    @endforeach
                </div>
                @error('subject_ids') <span class="mt-1 block text-xs text-error-500">{{ $message }}</span> @enderror
            </fieldset>
            <div class="rounded-lg bg-brand-50 p-3 dark:bg-brand-500/10">
                <p class="text-xs text-brand-700 dark:text-brand-300">
                    <i data-lucide="info" class="inline w-3 h-3"></i>
                    L'email et le mot de passe seront générés automatiquement.
                </p>
            </div>
            <div class="flex justify-end gap-3 mt-2">
                <x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'create-teacher')">
                    Annuler
                </x-form.button>
                <x-form.button type="submit" variant="primary">
                    Créer l'Enseignant
                </x-form.button>
            </div>
        </form>
    </x-ui.modal>
</x-layout.app>