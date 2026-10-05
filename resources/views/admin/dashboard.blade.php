@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="space-y-8">
    <!-- En-tête de page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 brand-font">
                Tableau de Bord Administrateur
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Supervision des utilisateurs et gestion des comptes de l'équipe Support.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.supports.create') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl shadow-md text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all card-hover">
                <svg class="w-5 h-5 mr-2 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Créer un agent Support
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Stat Total -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 card-shadow flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Utilisateurs</p>
                <p class="text-2xl font-bold text-slate-900">{{ $totalUsers }}</p>
            </div>
        </div>

        <!-- Stat Admins -->
        <div class="bg-white p-5 rounded-2xl border border-indigo-100 card-shadow flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Administrateurs</p>
                <p class="text-2xl font-bold text-indigo-950">{{ $totalAdmins }}</p>
            </div>
        </div>

        <!-- Stat Support -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-100 card-shadow flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wider">Agents Support</p>
                <p class="text-2xl font-bold text-emerald-950">{{ $totalSupports }}</p>
            </div>
        </div>
    </div>

    <!-- Tableau de la liste des utilisateurs -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden card-shadow">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 brand-font">
                Liste des Utilisateurs et Rôles
            </h2>
            <span class="text-xs text-slate-500">
                Affichage de {{ $users->count() }} sur {{ $totalUsers }} utilisateur(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Rôle
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Date de création
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Statut
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- Nom & Email -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs {{ $user->hasRole('admin') ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $user->name }}
                                            @if($user->id === Auth::id())
                                                <span class="ml-1 text-[11px] text-slate-400 font-normal">(Vous)</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Rôle -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->hasRole('admin'))
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <svg class="w-3.5 h-3.5 mr-1.5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/>
                                        </svg>
                                        Administrateur
                                    </span>
                                @elseif($user->hasRole('support'))
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        Support
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        Aucun rôle
                                    </span>
                                @endif
                            </td>

                            <!-- Date de création -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y à H:i') : 'N/A' }}
                            </td>

                            <!-- Statut -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Actif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
