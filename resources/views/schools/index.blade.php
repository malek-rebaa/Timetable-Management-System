<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">Écoles clientes</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Une école possède sa propre base de données et son super administrateur.</p>
        </div>

        <x-form.button variant="primary" x-data @click="$dispatch('open-modal', 'create-school')">
            <i data-lucide="school" class="h-4 w-4"></i> Nouvelle école
        </x-form.button>
    </div>

    @if(session('generated_password') && session('generated_email'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/20 dark:bg-success-500/10" x-data="{ show: true }" x-show="show">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-success-800 dark:text-success-300">Compte super administrateur créé</h3>
                    <p class="mt-2 text-sm text-success-700 dark:text-success-200">
                        Email : <strong>{{ session('generated_email') }}</strong><br>
                        Mot de passe : <strong class="font-mono">{{ session('generated_password') }}</strong>
                    </p>
                    <p class="mt-1 text-xs text-success-600 dark:text-success-400">Copiez ce mot de passe maintenant : il ne sera plus affiché.</p>
                </div>
                <button @click="show = false" class="text-success-600"><i data-lucide="x" class="h-5 w-5"></i></button>
            </div>
        </div>
    @endif

    @if(session('success') && !session('generated_password'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
            {{ session('error') }}
        </div>
    @endif

    <x-ui.card>
        <x-ui.table :headers="['École', 'Base de données', 'Utilisateurs', 'Statut', 'Action']">
            @forelse($schools as $school)
                <tr>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800 dark:text-white">{{ $school->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $school->slug }}</p>
                    </td>
                    <td class="px-5 py-4 font-mono text-sm text-gray-600 dark:text-gray-300">{{ $school->database_name }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $school->active_members_count }}</td>
                    <td class="px-5 py-4">
                        <span @class([
                            'rounded px-2 py-1 text-xs font-semibold',
                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300' => $school->status === 'ACTIVE',
                            'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300' => $school->status === 'PENDING',
                            'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => !in_array($school->status, ['ACTIVE', 'PENDING']),
                        ])>{{ $school->status }}</span>
                    </td>
                    <td class="px-5 py-4">
                        @if(session('active_school_id') == $school->id)
                            <span class="text-xs font-medium text-brand-600 dark:text-brand-400">Espace actif</span>
                        @elseif($school->status === 'ACTIVE')
                            <form method="POST" action="{{ route('schools.activate', $school) }}">
                                @csrf
                                <button class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Ouvrir</button>
                            </form>
                        @elseif($school->status === 'PENDING')
                            <form method="POST" action="{{ route('schools.provision', $school) }}">
                                @csrf
                                <button class="text-sm font-medium text-warning-600 hover:text-warning-700 dark:text-warning-400">Relancer</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">En attente</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Aucune école créée.</td>
                </tr>
            @endforelse
        </x-ui.table>
    </x-ui.card>

    <x-ui.modal name="create-school" title="Créer une école cliente" maxWidth="2xl">
        <form action="{{ route('schools.store') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de l'école</label>
                <x-form.input name="name" value="{{ old('name') }}" placeholder="École Ibn Khaldoun" required :error="$errors->has('name')" />
                @error('name') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom du super administrateur</label>
                    <x-form.input name="admin_first_name" value="{{ old('admin_first_name') }}" required :error="$errors->has('admin_first_name')" />
                    @error('admin_first_name') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du super administrateur</label>
                    <x-form.input name="admin_last_name" value="{{ old('admin_last_name') }}" required :error="$errors->has('admin_last_name')" />
                    @error('admin_last_name') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <x-form.input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@ecole.tn" required :error="$errors->has('admin_email')" />
                    @error('admin_email') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                    <x-form.input name="admin_phone" value="{{ old('admin_phone') }}" placeholder="+216 XX XXX XXX" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Couleur principale</label>
                    <x-form.input type="color" name="primary_color" value="{{ old('primary_color', '#2563EB') }}" required class="h-10 p-1" />
                    @error('primary_color') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Couleur secondaire</label>
                    <x-form.input type="color" name="secondary_color" value="{{ old('secondary_color', '#1D4ED8') }}" required class="h-10 p-1" />
                    @error('secondary_color') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="rounded-lg bg-brand-50 p-3 text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                La base isolée, les tables de l'école et le compte <strong>SCHOOL_ADMIN</strong> seront créés automatiquement.
            </div>

            <div class="mt-2 flex justify-end gap-3">
                <x-form.button type="button" variant="secondary" @click="$dispatch('close-modal', 'create-school')">Annuler</x-form.button>
                <x-form.button type="submit" variant="primary">Créer l'école</x-form.button>
            </div>
        </form>
    </x-ui.modal>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-school' }));
            });
        </script>
    @endif
</x-layout.app>
