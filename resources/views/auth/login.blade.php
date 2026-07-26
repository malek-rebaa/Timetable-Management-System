<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - EduTime</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800 antialiased dark:bg-gray-900">

    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-theme-xl dark:bg-gray-800 sm:p-10">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <i data-lucide="book-open-check" class="h-8 w-8"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Bienvenue sur EduTime</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Connectez-vous pour accéder à votre espace</p>
            </div>

            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-col gap-5">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse Email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <i data-lucide="mail" class="h-5 w-5"></i>
                        </span>
                        <x-form.input type="email" placeholder="admin@ecole.com" class="pl-11" required />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <i data-lucide="lock" class="h-5 w-5"></i>
                        </span>
                        <x-form.input type="password" placeholder="••••••••" class="pl-11" required />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700">
                        Se souvenir de moi
                    </label>
                    <a href="#" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">Mot de passe oublié ?</a>
                </div>

                <x-form.button type="submit" variant="primary" class="mt-2 w-full py-3">
                    Se connecter
                </x-form.button>
            </form>
            
            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                Vous n'avez pas de compte ? <br> Les comptes sont créés par l'administration.
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
