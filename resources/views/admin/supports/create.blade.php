@extends('layouts.app')

@section('title', 'Créer un agent Support')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Lien de retour -->
    <div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour au tableau de bord
        </a>
    </div>

    <!-- Carte du formulaire -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-6 sm:px-8 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 brand-font">
                        Créer un nouvel agent Support
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Renseignez les informations de connexion pour le nouveau collaborateur Support.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.supports.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Nom Complet -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nom complet <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" required
                       value="{{ old('name') }}"
                       placeholder="Ex: Fatou Ndiaye"
                       class="block w-full px-3.5 py-2.5 sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors {{ $errors->has('name') ? 'border-rose-400' : '' }}">
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Adresse email professionnelle <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" required
                       value="{{ old('email') }}"
                       placeholder="Ex: support.fatou@exemple.com"
                       class="block w-full px-3.5 py-2.5 sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors {{ $errors->has('email') ? 'border-rose-400' : '' }}">
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Mot de passe <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required minlength="8"
                           placeholder="Minimum 8 caractères"
                           class="block w-full px-3.5 py-2.5 sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors {{ $errors->has('password') ? 'border-rose-400' : '' }}">
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmation Mot de passe -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Confirmer le mot de passe <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                           placeholder="Confirmer à l'identique"
                           class="block w-full px-3.5 py-2.5 sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <!-- Note explicative sur le rôle attribué -->
            <div class="rounded-xl bg-emerald-50/80 border border-emerald-200/80 p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-xs text-emerald-900 leading-relaxed">
                    <strong class="font-semibold">Attribution automatique de rôle :</strong>
                    Cet utilisateur recevra automatiquement le rôle <span class="px-1.5 py-0.5 rounded bg-emerald-200/70 font-semibold text-emerald-900">support</span>. Il aura un accès direct et sécurisé à l'espace dédié aux agents support après sa connexion.
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg shadow-md text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all card-hover">
                    Enregistrer l'agent Support
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
