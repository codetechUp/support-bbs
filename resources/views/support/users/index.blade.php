@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs - Support')

@section('content')

    @php
        $meta = $usersData['meta'] ?? [];
        $users = $usersData['data'] ?? [];
    @endphp

    <div
        x-data="userListApp(
            @js($meta),
            @js($users),
            @js($error ?? null)
        )"
        class="space-y-6"
    >

        {{-- ============================================================
             EN-TÊTE
        ============================================================= --}}

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">
                        Gestion des Utilisateurs
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Consultation des comptes utilisateurs enregistrés sur la plateforme ManyPayCash.
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Total :
                        <span class="font-semibold text-gray-700 dark:text-gray-300"
                              x-text="meta.total ?? 0">
                            {{ $meta['total'] ?? 0 }}
                        </span>
                        utilisateurs
                    </span>

                    <button
                        type="button"
                        @click="refreshUsers()"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <svg
                            class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            :class="isLoading ? 'animate-spin text-brand-500' : ''"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>

                        <span x-text="isLoading ? 'Actualisation...' : 'Actualiser'">
                            Actualiser
                        </span>
                    </button>

                </div>

            </div>
        </div>


        {{-- ============================================================
             RECHERCHE ET FILTRES
        ============================================================= --}}

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            <div class="flex flex-col gap-4">

                {{-- Recherche --}}
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    <div class="relative flex-1">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">

                            <svg
                                x-show="!isSearching"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>

                            <svg
                                x-show="isSearching"
                                x-cloak
                                class="h-5 w-5 animate-spin text-brand-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>

                        </div>

                        <input
                            type="text"
                            :value="rawSearchInput"
                            @input="onSearchInput($event.target.value)"
                            placeholder="Rechercher par nom, email, ID utilisateur ou ID client..."
                            class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 pl-11 pr-24 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                        />

                        <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-3">

                            <span
                                x-show="isSearching"
                                x-cloak
                                class="animate-pulse text-xs font-medium text-brand-600 dark:text-brand-400"
                            >
                                Recherche...
                            </span>

                            <button
                                type="button"
                                x-show="rawSearchInput.length > 0"
                                @click="clearSearch()"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                        </div>

                    </div>

                    {{-- Tri --}}
                    <div class="flex items-center gap-2">

                        <label
                            for="user_sort_by"
                            class="shrink-0 text-3xs font-semibold uppercase tracking-wider text-gray-400"
                        >
                            Trier par :
                        </label>

                        <select
                            id="user_sort_by"
                            x-model="sortBy"
                            @change="onFilterChange()"
                            class="h-12 rounded-xl border border-gray-200 bg-gray-50 px-3.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="date_desc">Plus récents d'abord</option>
                            <option value="date_asc">Plus anciens d'abord</option>
                            <option value="name_asc">Nom (A - Z)</option>
                            <option value="name_desc">Nom (Z - A)</option>
                            <option value="id_desc">ID décroissant</option>
                            <option value="id_asc">ID croissant</option>
                        </select>

                    </div>

                </div>


                {{-- Filtres --}}
                <div class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">

                    {{-- Filtre rôle --}}
                    <div class="flex min-w-[170px] flex-1 flex-col sm:flex-initial">

                        <label class="mb-1 text-3xs font-semibold uppercase tracking-wider text-gray-400">
                            Rôle
                        </label>

                        <select
                            x-model="selectedRole"
                            @change="onFilterChange()"
                            class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs font-medium text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="ALL">Tous les rôles</option>

                            <template x-for="role in availableRoles" :key="role">
                                <option :value="role" x-text="role"></option>
                            </template>
                        </select>

                    </div>


                    {{-- Filtre état --}}
                    <div class="flex min-w-[170px] flex-1 flex-col sm:flex-initial">

                        <label class="mb-1 text-3xs font-semibold uppercase tracking-wider text-gray-400">
                            État du compte
                        </label>

                        <select
                            x-model="selectedStatus"
                            @change="onFilterChange()"
                            class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs font-medium text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="ALL">Tous les états</option>

                            <template x-for="status in availableStatuses" :key="status">
                                <option :value="status" x-text="status"></option>
                            </template>
                        </select>

                    </div>


                    {{-- Réinitialisation --}}
                    <div class="flex items-center">

                        <button
                            type="button"
                            @click="resetFilters()"
                            x-show="hasActiveFilters"
                            x-cloak
                            class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-red-200 bg-red-50/70 px-3.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>

                            Effacer les filtres
                        </button>

                    </div>

                </div>

            </div>


            {{-- Compteur --}}
            <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:text-gray-400">

                <div class="flex items-center gap-2">

                    <span
                        x-show="isLoading"
                        x-cloak
                        class="inline-block h-2 w-2 animate-ping rounded-full bg-brand-500"
                    ></span>

                    <span>
                        Affichage de
                        <strong class="text-gray-800 dark:text-white" x-text="startIndex"></strong>
                        à
                        <strong class="text-gray-800 dark:text-white" x-text="endIndex"></strong>
                        sur
                        <strong class="text-gray-800 dark:text-white" x-text="meta.total ?? 0"></strong>
                        utilisateur(s)
                    </span>

                </div>

                <span
                    x-show="totalPages > 1"
                    class="font-medium text-brand-600 dark:text-brand-400"
                >
                    Page <span x-text="currentPage"></span>
                    sur <span x-text="totalPages"></span>
                </span>

            </div>

        </div>


        {{-- ============================================================
             MESSAGE D'ERREUR
        ============================================================= --}}

        <div
            x-show="errorMessage"
            x-cloak
            class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400"
        >
            <div class="flex items-start gap-3">

                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.73-2.99L13.73 4a2 2 0 00-3.46 0L3.34 16.01A2 2 0 005.07 19z"/>
                </svg>

                <div>
                    <p class="font-semibold">Impossible de récupérer les utilisateurs</p>
                    <p class="mt-1" x-text="errorMessage"></p>

                    <button
                        type="button"
                        @click="refreshUsers()"
                        class="mt-2 font-semibold underline"
                    >
                        Réessayer
                    </button>
                </div>

            </div>
        </div>


        {{-- ============================================================
             TABLEAU DES UTILISATEURS
        ============================================================= --}}

        <div class="relative min-h-[350px] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            {{-- Loader --}}
            <div
                x-show="isLoading"
                x-cloak
                x-transition
                class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-white/85 backdrop-blur-xs dark:bg-gray-900/85"
            >
                <div class="flex flex-col items-center gap-3 p-6 text-center">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 shadow-theme-xs dark:bg-brand-500/15 dark:text-brand-400">

                        <svg class="h-7 w-7 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>

                    </div>

                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">
                            Chargement des utilisateurs...
                        </p>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Synchronisation avec l'API ManyPayCash
                        </p>
                    </div>

                </div>
            </div>


            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">

                    <thead>
                        <tr class="bg-gray-50/70 text-left text-2xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800/40 dark:text-gray-400">

                            <th class="px-6 py-4">Utilisateur</th>
                            <th class="px-6 py-4">Identifiants</th>
                            <th class="px-6 py-4">Rôle</th>
                            <th class="px-6 py-4">État du compte</th>
                            <th class="px-6 py-4">Dernière modification du mot de passe</th>
                            <th class="px-6 py-4">Date de création</th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                        <template x-for="user in visibleUsers" :key="user.id">

                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/50">

                                {{-- Utilisateur --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                            <span x-text="getInitials(user)"></span>
                                        </div>

                                        <div>
                                            <p
                                                class="text-sm font-semibold text-gray-800 dark:text-white"
                                                x-text="getFullName(user)"
                                            ></p>

                                            <p
                                                class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"
                                                x-text="user.email || 'Email non renseigné'"
                                            ></p>
                                        </div>

                                    </div>

                                </td>


                                {{-- Identifiants --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="space-y-1">

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Utilisateur :
                                            <span
                                                class="font-mono font-semibold text-gray-700 dark:text-gray-200"
                                                x-text="user.id ?? '—'"
                                            ></span>
                                        </p>

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Client :
                                            <span
                                                class="font-mono font-semibold text-gray-700 dark:text-gray-200"
                                                x-text="user.idCustomer ?? '—'"
                                            ></span>
                                        </p>

                                    </div>

                                </td>


                                {{-- Rôle --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="roleBadgeClass(user.role)"
                                        x-text="formatLabel(user.role)"
                                    ></span>

                                </td>


                                {{-- État --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
                                        :class="statusBadgeClass(user.etat)"
                                    >

                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="statusDotClass(user.etat)"
                                        ></span>

                                        <span x-text="user.etat || 'INCONNU'"></span>

                                    </span>

                                </td>


                                {{-- Dernière modification du mot de passe --}}
                                <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300">
                                    <span x-text="formatDate(user.lastPasswordUpdate)"></span>
                                </td>


                                {{-- Création --}}
                                <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300">
                                    <span x-text="formatDate(user.createdAt)"></span>
                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>


            {{-- Aucun résultat --}}
            <template x-if="!isLoading && visibleUsers.length === 0">

                <div class="py-14 text-center">

                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>

                    <h3 class="mt-2 text-sm font-semibold text-gray-800 dark:text-white">
                        Aucun utilisateur ne correspond aux critères
                    </h3>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Modifiez votre recherche ou réinitialisez les filtres.
                    </p>

                    <button
                        type="button"
                        @click="resetFilters()"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-medium text-white transition hover:bg-brand-600"
                    >
                        Réinitialiser les filtres
                    </button>

                </div>

            </template>


            {{-- ============================================================
                 PAGINATION DISTANTE
            ============================================================= --}}

            <div
                x-show="meta.total > 0"
                class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-6 py-4 sm:flex-row dark:border-gray-800"
            >

                {{-- Informations et limite --}}
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">

                    <span>
                        Affichage de
                        <strong class="text-gray-800 dark:text-white" x-text="startIndex"></strong>
                        à
                        <strong class="text-gray-800 dark:text-white" x-text="endIndex"></strong>
                        sur
                        <strong class="text-gray-800 dark:text-white" x-text="meta.total"></strong>
                    </span>

                    <div class="flex items-center gap-1.5 border-l border-gray-200 pl-3 dark:border-gray-700">

                        <label
                            for="user_per_page"
                            class="text-2xs font-semibold uppercase tracking-wider text-gray-400"
                        >
                            Par page :
                        </label>

                        <select
                            id="user_per_page"
                            x-model.number="perPage"
                            @change="changePerPage()"
                            class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>

                    </div>

                </div>


                {{-- Navigation --}}
                <div
                    class="flex items-center gap-1.5"
                    x-show="totalPages > 1"
                >

                    {{-- Précédent --}}
                    <button
                        type="button"
                        @click="prevPage()"
                        :disabled="currentPage <= 1 || isLoading"
                        class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>

                        Précédent
                    </button>


                    {{-- Numéros de page --}}
                    <div class="flex items-center gap-1">

                        <template x-for="(page, index) in pageNumbers" :key="index">

                            <div>

                                <template x-if="page === '...'">
                                    <span class="select-none px-2 py-1 text-xs text-gray-400">...</span>
                                </template>

                                <template x-if="page !== '...'">

                                    <button
                                        type="button"
                                        @click="goToPage(page)"
                                        :disabled="isLoading"
                                        :class="currentPage === page
                                            ? 'bg-brand-500 text-white font-bold shadow-theme-xs'
                                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                                        class="h-8 min-w-[32px] rounded-lg px-2 text-xs font-medium transition disabled:opacity-50"
                                        x-text="page"
                                    ></button>

                                </template>

                            </div>

                        </template>

                    </div>


                    {{-- Suivant --}}
                    <button
                        type="button"
                        @click="nextPage()"
                        :disabled="currentPage >= totalPages || isLoading"
                        class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Suivant

                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         LOGIQUE ALPINE.JS
    ============================================================= --}}

    @push('scripts')

        <script>

            function userListApp(initialMeta, initialUsers, initialError = null) {

                return {

                    // Données reçues du contrôleur
                    meta: initialMeta || {},
                    users: initialUsers || [],

                    // Erreurs
                    errorMessage: initialError,

                    // Recherche
                    rawSearchInput: '',
                    searchQuery: '',
                    searchTimeout: null,
                    isSearching: false,

                    // Filtres
                    selectedRole: 'ALL',
                    selectedStatus: 'ALL',
                    sortBy: 'date_desc',

                    // Chargement
                    isLoading: false,

                    // Pagination
                    currentPage: Number(initialMeta?.currentPage || 1),
                    perPage: Number(initialMeta?.perPage || 10),

                    // URL de l'endpoint JSON
                    dataUrl: @js(route('support.users.data')),

                    init() {
                        // Synchroniser l'état de pagination avec les métadonnées API.
                        this.currentPage = Number(this.meta.currentPage || 1);
                        this.perPage = Number(this.meta.perPage || 10);
                    },


                    // =====================================================
                    // DONNÉES FILTRÉES ET TRIÉES
                    // =====================================================

                    get filteredUsers() {

                        const query = this.searchQuery.toLowerCase().trim();

                        let list = this.users.filter(user => {

                            const fullName = `${user.prenom || ''} ${user.nom || ''}`.toLowerCase();

                            const matchSearch = !query ||
                                fullName.includes(query) ||
                                String(user.email || '').toLowerCase().includes(query) ||
                                String(user.id ?? '').toLowerCase().includes(query) ||
                                String(user.idCustomer ?? '').toLowerCase().includes(query);

                            const matchRole =
                                this.selectedRole === 'ALL' ||
                                user.role === this.selectedRole;

                            const matchStatus =
                                this.selectedStatus === 'ALL' ||
                                user.etat === this.selectedStatus;

                            return matchSearch && matchRole && matchStatus;
                        });

                        list = [...list].sort((a, b) => {

                            switch (this.sortBy) {

                                case 'date_asc':
                                    return this.toTimestamp(a.createdAt) - this.toTimestamp(b.createdAt);

                                case 'name_asc':
                                    return this.getFullName(a).localeCompare(this.getFullName(b));

                                case 'name_desc':
                                    return this.getFullName(b).localeCompare(this.getFullName(a));

                                case 'id_asc':
                                    return Number(a.id || 0) - Number(b.id || 0);

                                case 'id_desc':
                                    return Number(b.id || 0) - Number(a.id || 0);

                                case 'date_desc':
                                default:
                                    return this.toTimestamp(b.createdAt) - this.toTimestamp(a.createdAt);
                            }

                        });

                        return list;
                    },


                    get visibleUsers() {
                        return this.filteredUsers;
                    },


                    // =====================================================
                    // FILTRES DISPONIBLES
                    // =====================================================

                    get availableRoles() {

                        const roles = new Set();

                        this.users.forEach(user => {
                            if (user.role) roles.add(user.role);
                        });

                        return Array.from(roles).sort();
                    },


                    get availableStatuses() {

                        const statuses = new Set();

                        this.users.forEach(user => {
                            if (user.etat) statuses.add(user.etat);
                        });

                        return Array.from(statuses).sort();
                    },


                    get hasActiveFilters() {
                        return this.rawSearchInput !== '' ||
                            this.searchQuery !== '' ||
                            this.selectedRole !== 'ALL' ||
                            this.selectedStatus !== 'ALL' ||
                            this.sortBy !== 'date_desc';
                    },


                    // =====================================================
                    // PAGINATION
                    // =====================================================

                    get totalPages() {
                        return Math.max(1, Number(this.meta.lastPage || 1));
                    },


                    get startIndex() {
                        if (!this.users.length) return 0;

                        return (this.currentPage - 1) * this.perPage + 1;
                    },


                    get endIndex() {
                        if (!this.users.length) return 0;

                        return this.startIndex + this.users.length - 1;
                    },


                    get pageNumbers() {

                        const total = this.totalPages;
                        const current = this.currentPage;

                        if (total <= 7) {
                            return Array.from({ length: total }, (_, i) => i + 1);
                        }

                        if (current <= 4) {
                            return [1, 2, 3, 4, 5, '...', total];
                        }

                        if (current >= total - 3) {
                            return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                        }

                        return [1, '...', current - 1, current, current + 1, '...', total];
                    },


                    // =====================================================
                    // CHARGEMENT DISTANT DES DONNÉES
                    // =====================================================

                    async loadPage(page = 1, limit = this.perPage) {

                        if (this.isLoading) return;

                        this.isLoading = true;
                        this.errorMessage = null;

                        try {

                            const url = new URL(this.dataUrl, window.location.origin);

                            url.searchParams.set('page', page);
                            url.searchParams.set('limit', limit);

                            const response = await fetch(url.toString(), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const result = await response.json();

                            if (!response.ok || result.status !== 'success') {
                                throw new Error(
                                    result.message || 'Une erreur est survenue lors du chargement.'
                                );
                            }

                            if (!Array.isArray(result.users) || !result.meta) {
                                throw new Error('La réponse reçue de ManyPayCash est invalide.');
                            }

                            // Remplacement des données uniquement après une réponse valide.
                            this.users = result.users;
                            this.meta = result.meta;

                            this.currentPage = Number(result.meta.currentPage || page);
                            this.perPage = Number(result.meta.perPage || limit);

                            // Les filtres sont réinitialisés après changement de page.
                            this.rawSearchInput = '';
                            this.searchQuery = '';
                            this.selectedRole = 'ALL';
                            this.selectedStatus = 'ALL';

                        } catch (error) {

                            console.error('Erreur chargement utilisateurs :', error);

                            this.errorMessage = error.message ||
                                'Impossible de récupérer les utilisateurs auprès de ManyPayCash.';

                        } finally {

                            this.isLoading = false;
                        }
                    },


                    async refreshUsers() {
                        await this.loadPage(this.currentPage, this.perPage);
                    },


                    async goToPage(page) {

                        const target = Number(page);

                        if (
                            !Number.isInteger(target) ||
                            target < 1 ||
                            target > this.totalPages ||
                            target === this.currentPage
                        ) {
                            return;
                        }

                        await this.loadPage(target, this.perPage);
                    },


                    async nextPage() {

                        if (this.currentPage < this.totalPages) {
                            await this.goToPage(this.currentPage + 1);
                        }
                    },


                    async prevPage() {

                        if (this.currentPage > 1) {
                            await this.goToPage(this.currentPage - 1);
                        }
                    },


                    async changePerPage() {

                        const limit = Number(this.perPage);

                        if (![10, 25, 50, 100].includes(limit)) {
                            this.perPage = 10;
                            return;
                        }

                        await this.loadPage(1, limit);
                    },


                    // =====================================================
                    // RECHERCHE ET FILTRES LOCAUX
                    // =====================================================

                    onSearchInput(value) {

                        this.rawSearchInput = value;
                        this.isSearching = true;

                        clearTimeout(this.searchTimeout);

                        this.searchTimeout = setTimeout(() => {
                            this.searchQuery = value;
                            this.isSearching = false;
                        }, 250);
                    },


                    clearSearch() {

                        clearTimeout(this.searchTimeout);

                        this.rawSearchInput = '';
                        this.searchQuery = '';
                        this.isSearching = false;
                    },


                    onFilterChange() {
                        // Les filtres concernent uniquement la page actuellement chargée.
                    },


                    resetFilters() {

                        clearTimeout(this.searchTimeout);

                        this.rawSearchInput = '';
                        this.searchQuery = '';

                        this.selectedRole = 'ALL';
                        this.selectedStatus = 'ALL';

                        this.sortBy = 'date_desc';

                        this.isSearching = false;
                    },


                    // =====================================================
                    // FORMATAGE
                    // =====================================================

                    getFullName(user) {

                        const name = `${user.prenom || ''} ${user.nom || ''}`.trim();

                        return name || user.email || `Utilisateur #${user.id ?? ''}`;
                    },


                    getInitials(user) {

                        const first = String(user.prenom || '').trim().charAt(0);
                        const last = String(user.nom || '').trim().charAt(0);

                        const initials = `${first}${last}`.toUpperCase();

                        return initials || 'U';
                    },


                    formatLabel(value) {

                        if (!value) return 'Non renseigné';

                        return String(value)
                            .replace(/_/g, ' ')
                            .toLowerCase()
                            .replace(/\b\w/g, letter => letter.toUpperCase());
                    },


                    formatDate(value) {

                        if (!value) return 'Non renseignée';

                        const date = new Date(value);

                        if (Number.isNaN(date.getTime())) {
                            return 'Date invalide';
                        }

                        return new Intl.DateTimeFormat('fr-FR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }).format(date);
                    },


                    toTimestamp(value) {

                        if (!value) return 0;

                        const timestamp = new Date(value).getTime();

                        return Number.isNaN(timestamp) ? 0 : timestamp;
                    },


                    // =====================================================
                    // BADGES
                    // =====================================================

                    roleBadgeClass(role) {

                        switch (role) {

                            case 'super_admin':
                                return 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400';

                            case 'admin':
                                return 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400';

                            case 'support':
                                return 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400';

                            default:
                                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                        }
                    },


                    statusBadgeClass(status) {

                        switch (String(status || '').toUpperCase()) {

                            case 'ACTIVE':
                                return 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400';

                            case 'INACTIVE':
                                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';

                            case 'SUSPENDED':
                                return 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400';

                            default:
                                return 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400';
                        }
                    },


                    statusDotClass(status) {

                        switch (String(status || '').toUpperCase()) {

                            case 'ACTIVE':
                                return 'bg-success-500';

                            case 'INACTIVE':
                                return 'bg-gray-400';

                            case 'SUSPENDED':
                                return 'bg-error-500';

                            default:
                                return 'bg-warning-500';
                        }
                    }

                };
            }

        </script>

    @endpush

@endsection
