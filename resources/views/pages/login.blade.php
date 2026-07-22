<x-layout.guest>
    <div class="mb-4 text-sm text-bodytext text-center">
        Veuillez vous connecter pour accéder à votre espace de gestion d'emploi du temps.
    </div>

    <!-- Session Status -->
    {{-- <x-auth-session-status class="mb-4" :status="session('status')" /> --}}

    <form method="POST" action="{{ route('login') ?? '#' }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-form.label for="email" value="Adresse Email" />
            <x-form.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            {{-- <x-form.error :messages="$errors->get('email')" class="mt-2" /> --}}
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-form.label for="password" value="Mot de passe" />
            <x-form.input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            {{-- <x-form.error :messages="$errors->get('password')" class="mt-2" /> --}}
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <x-form.checkbox id="remember_me" name="remember" />
                <span class="ml-2 text-sm text-bodytext">Se souvenir de moi</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-primary hover:text-primary-emphasis rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @else
                <span></span>
            @endif

            <x-button variant="primary" type="submit">
                Connexion
            </x-button>
        </div>
    </form>
</x-layout.guest>
