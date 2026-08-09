<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Mon Compte
        </h2>
    </div>

    @if(session('success'))
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

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Modifier le mot de passe</h3>
            </x-slot:header>
            
            <form action="{{ route('profile.password.update') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe actuel</label>
                    <x-form.input 
                        type="password" 
                        name="current_password" 
                        placeholder="Entrez votre mot de passe actuel" 
                        required 
                        :error="$errors->has('current_password')" />
                    @error('current_password')
                        <p class="mt-1 text-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nouveau mot de passe</label>
                    <x-form.input 
                        type="password" 
                        name="password" 
                        placeholder="Minimum 12 caractères" 
                        required 
                        :error="$errors->has('password')" />
                    @error('password')
                        <p class="mt-1 text-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmer le nouveau mot de passe</label>
                    <x-form.input 
                        type="password" 
                        name="password_confirmation" 
                        placeholder="Confirmez le nouveau mot de passe" 
                        required />
                </div>

                <div class="rounded-lg bg-brand-50 p-3 dark:bg-brand-500/10">
                    <p class="text-xs text-brand-700 dark:text-brand-300">
                        <i data-lucide="info" class="inline w-3 h-3"></i>
                        Le mot de passe doit contenir au moins 12 caractères, avec des majuscules, minuscules, chiffres et symboles.
                    </p>
                </div>

                <div class="flex justify-end gap-3 mt-2">
                    <x-form.button type="submit" variant="primary">
                        Modifier le mot de passe
                    </x-form.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Informations du compte</h3>
            </x-slot:header>
            
            <div class="flex flex-col gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom complet</span>
                    <p class="text-sm text-gray-800 dark:text-white">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                    <p class="text-sm text-gray-800 dark:text-white">{{ auth()->user()->email }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Rôle</span>
                    <p class="text-sm">
                        @switch(auth()->user()->role)
                            @case('SUPER_ADMIN')
                                <span class="inline-block px-2 py-1 bg-error-50 text-error-600 rounded text-xs font-semibold uppercase">Super Admin</span>
                                @break
                            @case('ADMIN')
                                <span class="inline-block px-2 py-1 bg-brand-50 text-brand-600 rounded text-xs font-semibold uppercase">Admin</span>
                                @break
                            @case('TEACHER')
                                <span class="inline-block px-2 py-1 bg-warning-50 text-warning-600 rounded text-xs font-semibold uppercase">Enseignant</span>
                                @break
                        @endswitch
                    </p>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layout.app>
