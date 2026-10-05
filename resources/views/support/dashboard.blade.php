@extends('layouts.app')

@section('title', 'Espace Support')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- En-tête de bienvenue -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-6 sm:p-10 text-white shadow-xl shadow-emerald-900/10 relative overflow-hidden">
        <!-- Décoration d'arrière-plan -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white mb-3 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 mr-2 animate-pulse"></span>
                    Espace Agent Support
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight brand-font">
                    Bienvenue, {{ $user->name }} !
                </h1>
                <p class="mt-2 text-sm text-emerald-100 max-w-xl">
                    Vous êtes connecté avec les accès de l'équipe Support. Votre compte est actif et configuré avec les autorisations nécessaires.
                </p>
            </div>
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white text-2xl font-bold shadow-inner">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Carte des informations du compte -->
    <div class="bg-white rounded-2xl border border-slate-200 card-shadow overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 brand-font flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Fiche d'information du collaborateur</span>
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                Compte Opérationnel
            </span>
        </div>

        <div class="p-6 sm:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Nom -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Nom complet</span>
                    <span class="text-base font-bold text-slate-800 mt-1 block">{{ $user->name }}</span>
                </div>

                <!-- Email -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Adresse email</span>
                    <span class="text-base font-bold text-slate-800 mt-1 block">{{ $user->email }}</span>
                </div>

                <!-- Rôle Attribué -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Rôle système</span>
                    <div class="mt-1 flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Support
                        </span>
                        <span class="text-xs text-slate-500">(Géré par l'administrateur)</span>
                    </div>
                </div>

                <!-- Date d'inscription -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Date de création du compte</span>
                    <span class="text-base font-bold text-slate-800 mt-1 block">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y à H:i') : 'Non renseignée' }}
                    </span>
                </div>
            </div>

            <!-- Notice indicative -->
            <div class="mt-6 rounded-xl bg-slate-50 border border-slate-200 p-4 flex items-start space-x-3 text-slate-600 text-xs leading-relaxed">
                <svg class="w-5 h-5 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <strong class="font-semibold text-slate-700">Rappel de sécurité :</strong>
                    Votre compte est restreint à l'espace Support conformément aux politiques définies par l'Administrateur. Vous ne disposez pas des privilèges d'administration ni de modification de la structure des utilisateurs.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
