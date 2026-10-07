@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')

@section('content')

    <div class="space-y-6">

        {{-- En-tête du dashboard --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    Tableau de bord
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Supervision des utilisateurs et gestion de l'équipe Support.
                </p>
            </div>

            <div>
                <a href="{{ route('admin.supports.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>

                    Créer un agent Support
                </a>
            </div>
        </div>


        {{-- Cartes statistiques --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Total utilisateurs --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            Total utilisateurs
                        </p>

                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $totalUsers }}
                        </h4>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/15">
                        <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H6a4 4 0 01-4-4v-1a4 4 0 014-4h3m5-3a4 4 0 11-8 0 4 4 0 018 0zm4 3a3 3 0 10-2.83-4" />
                        </svg>
                    </div>

                </div>

                <p class="mt-4 text-theme-xs text-gray-500 dark:text-gray-400">
                    Comptes enregistrés dans le système
                </p>
            </div>


            {{-- Administrateurs --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            Administrateurs
                        </p>

                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $totalAdmins }}
                        </h4>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 dark:bg-warning-500/15">
                        <svg class="h-6 w-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15l3.5 2-1-4 3-2.5-4-.25L12 6.5 10.5 10.25l-4 .25 3 2.5-1 4L12 15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 21h14" />
                        </svg>
                    </div>

                </div>

                <p class="mt-4 text-theme-xs text-gray-500 dark:text-gray-400">
                    Comptes disposant des privilèges administrateur
                </p>
            </div>


            {{-- Agents Support --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            Agents Support
                        </p>

                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $totalSupports }}
                        </h4>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 dark:bg-success-500/15">
                        <svg class="h-6 w-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 20a6 6 0 00-12 0m6-10a4 4 0 110-8 4 4 0 010 8zm8 10a4 4 0 00-3-3.87M18 8a3 3 0 100-6" />
                        </svg>
                    </div>

                </div>

                <p class="mt-4 text-theme-xs text-gray-500 dark:text-gray-400">
                    Comptes dédiés au support client
                </p>
            </div>

        </div>


        {{-- Tableau des utilisateurs --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

            {{-- En-tête du tableau --}}
            <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Utilisateurs
                    </h3>

                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                        Liste des comptes utilisateurs du système.
                    </p>
                </div>

                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">
                        {{ $users->count() }} utilisateur(s) affiché(s)
                    </span>
                </div>

            </div>


            {{-- Tableau responsive --}}
            <div class="max-w-full overflow-x-auto">

                <table class="min-w-full">

                    <thead>
                        <tr class="border-t border-gray-100 dark:border-gray-800">

                            <th class="px-6 py-3 text-left rtl:text-right">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    Utilisateur
                                </p>
                            </th>

                            <th class="px-6 py-3 text-left rtl:text-right">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    Email
                                </p>
                            </th>

                            <th class="px-6 py-3 text-left rtl:text-right">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    Rôle
                                </p>
                            </th>

                            <th class="px-6 py-3 text-left rtl:text-right">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    Création
                                </p>
                            </th>

                            <th class="px-6 py-3 text-left rtl:text-right">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    Statut
                                </p>
                            </th>

                        </tr>
                    </thead>


                    <tbody>

                        @forelse($users as $user)
                            <tr class="border-t border-gray-100 dark:border-gray-800">

                                {{-- Utilisateur --}}
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-500 dark:bg-brand-500/15">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                                {{ $user->name }}

                                                @if ($user->id === Auth::id())
                                                    <span class="ml-1 text-xs text-brand-500">
                                                        (Vous)
                                                    </span>
                                                @endif
                                            </p>
                                        </div>

                                    </div>

                                </td>


                                {{-- Email --}}
                                <td class="px-6 py-4">

                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                    </p>

                                </td>


                                {{-- Rôle --}}
                                <td class="px-6 py-4">

                                    @if ($user->hasRole('admin'))
                                        <span
                                            class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                            Administrateur
                                        </span>
                                    @elseif($user->hasRole('support'))
                                        <span
                                            class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                            Support
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Aucun rôle
                                        </span>
                                    @endif

                                </td>


                                {{-- Date --}}
                                <td class="px-6 py-4">

                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->created_at ? $user->created_at->format('d/m/Y à H:i') : 'N/A' }}
                                    </p>

                                </td>


                                {{-- Statut --}}
                                <td class="px-6 py-4">

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-400">

                                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>

                                        Actif

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center">

                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        Aucun utilisateur trouvé
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Aucun compte utilisateur n'est actuellement disponible.
                                    </p>

                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if ($users->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $users->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection
