<x-layout.app>
    <div class="mb-6">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">Apparence de l’école</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Définissez les couleurs utilisées par l’espace de {{ $school->name }}.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card class="max-w-2xl">
        <form method="POST" action="{{ route('branding.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="primary_color" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Couleur principale</label>
                    <div class="flex items-center gap-3">
                        <input id="primary_color" name="primary_color" type="color"
                               value="{{ old('primary_color', $school->primary_color) }}"
                               class="h-11 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1 dark:border-gray-700 dark:bg-gray-900">
                        <span class="font-mono text-sm text-gray-600 dark:text-gray-300">{{ old('primary_color', $school->primary_color) }}</span>
                    </div>
                    @error('primary_color') <p class="mt-2 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="secondary_color" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Couleur secondaire</label>
                    <div class="flex items-center gap-3">
                        <input id="secondary_color" name="secondary_color" type="color"
                               value="{{ old('secondary_color', $school->secondary_color ?? '#1D4ED8') }}"
                               class="h-11 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1 dark:border-gray-700 dark:bg-gray-900">
                        <span class="font-mono text-sm text-gray-600 dark:text-gray-300">{{ old('secondary_color', $school->secondary_color ?? '#1D4ED8') }}</span>
                    </div>
                    @error('secondary_color') <p class="mt-2 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Aperçu</p>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg text-white" style="background: {{ $school->primary_color }}">
                        <i data-lucide="palette" class="h-5 w-5"></i>
                    </span>
                    <span class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: {{ $school->secondary_color ?? '#1D4ED8' }}">
                        Bouton exemple
                    </span>
                </div>
            </div>

            <div class="flex justify-end">
                <x-form.button type="submit" variant="primary">Enregistrer les couleurs</x-form.button>
            </div>
        </form>
    </x-ui.card>
</x-layout.app>
