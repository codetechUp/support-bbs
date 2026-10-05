@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center items-center py-6 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo Icon -->
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-xl shadow-indigo-200 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 brand-font">
            Espace d'authentification
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Connectez-vous pour accéder à votre espace Admin ou Support
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-2xl border border-slate-100 sm:px-10">
            
            @if($errors->has('email'))
                <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs font-medium text-rose-700">
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Champ Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Adresse Email
                    </label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               placeholder="nom@exemple.com"
                               class="block w-full pl-9 pr-3 py-2.5 sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors {{ $errors->has('email') ? 'border-rose-400' : '' }}">
                    </div>
                </div>

                <!-- Champ Mot de passe -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Mot de passe
                    </label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               placeholder="••••••••"
                               class="block w-full pl-9 pr-3 py-2.5 sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                        <label for="remember" class="ml-2 block text-xs text-slate-600 font-medium">
                            Se souvenir de moi
                        </label>
                    </div>
                </div>

                <!-- Bouton Connexion -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all hover:shadow-indigo-200">
                        Se connecter
                    </button>
                </div>
            </form>

            <!-- Comptes de test rapide -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <span class="block text-center text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                    Identifiants de démonstration pré-configurés
                </span>
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" onclick="fillCredentials('admin@example.com', 'password')"
                            class="text-left p-2.5 rounded-xl border border-indigo-100 bg-indigo-50/60 hover:bg-indigo-100/70 transition-all group">
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white mb-1">
                            Admin
                        </span>
                        <div class="text-xs font-semibold text-indigo-950 truncate">admin@example.com</div>
                        <div class="text-[11px] text-slate-500">password</div>
                    </button>

                    <button type="button" onclick="fillCredentials('support@example.com', 'password')"
                            class="text-left p-2.5 rounded-xl border border-emerald-100 bg-emerald-50/60 hover:bg-emerald-100/70 transition-all group">
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white mb-1">
                            Support
                        </span>
                        <div class="text-xs font-semibold text-emerald-950 truncate">support@example.com</div>
                        <div class="text-[11px] text-slate-500">password</div>
                    </button>
                </div>
                <p class="mt-2 text-center text-[11px] text-slate-400">
                    Cliquez sur l'un des boutons ci-dessus pour pré-remplir le formulaire instantanément.
                </p>
            </div>

        </div>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
