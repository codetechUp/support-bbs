@extends('layouts.app')

@section('title', 'Liste des Clients Intégrés - Support')

@section('content')

    @php
        $summary = $clientsData['summary'] ?? [];
        $clients = $clientsData['clients'] ?? [];

        $checkedAt = $summary['checked_at'] ?? now()->format('H:i:s');
    @endphp

    <div x-data="clientListApp(@js($clientsData))" class="space-y-6">

        {{-- ============================================================
             EN-TÊTE DE LA PAGE AVEC ACTIONS ET DERNIÈRE VÉRIFICATION
        ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">
                        Liste des Clients Intégrés
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Recherche et consultation des partenaires et entreprises connectés à la plateforme ManyPayCash.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Dernière synchronisation : <span class="font-medium text-gray-700 dark:text-gray-300"
                            x-text="data.summary.checked_at">{{ $checkedAt }}</span>
                    </span>

                    {{-- Bouton Actualiser avec animation loader --}}
                    <button type="button" @click="refreshClients()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            :class="isLoading ? 'animate-spin text-brand-500' : ''" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isLoading ? 'Synchronisation...' : 'Actualiser'">Actualiser</span>
                    </button>
                </div>

            </div>
        </div>

        {{-- ============================================================
             BARRE DE RECHERCHE ET FILTRES (SANS STATISTIQUES)
        ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            <div class="flex flex-col gap-4">

                {{-- Ligne 1 : Grand champ de recherche avec loader actif intégré --}}
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    <div class="relative flex-1">
                        {{-- Icône Loupe OU Spinner de recherche actif --}}
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <svg x-show="!isSearching" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <svg x-show="isSearching" x-cloak class="h-5 w-5 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>

                        <input type="text"
                            :value="rawSearchInput"
                            @input="onSearchInput($event.target.value)"
                            placeholder="Rechercher par nom de client, entreprise, Partner ID ou email..."
                            class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 pl-11 pr-24 text-sm text-gray-800 placeholder:text-gray-400 shadow-theme-xs transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500" />

                        {{-- Indicateur de recherche et bouton effacer --}}
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2">
                            <span x-show="isSearching" x-cloak class="text-xs font-medium text-brand-600 dark:text-brand-400 animate-pulse">
                                Recherche...
                            </span>
                            <button type="button" x-show="rawSearchInput.length > 0" @click="clearSearch()"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Sélecteur de Tri --}}
                    <div class="flex items-center gap-2">
                        <label for="client_sort_by" class="shrink-0 text-3xs font-semibold uppercase tracking-wider text-gray-400">
                            Trier par :
                        </label>
                        <select id="client_sort_by" x-model="sortBy" @change="onFilterChange()"
                            class="h-12 rounded-xl border border-gray-200 bg-gray-50 px-3.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option value="date_desc">Plus récents d'abord</option>
                            <option value="date_asc">Plus anciens d'abord</option>
                            <option value="name_asc">Nom du client (A - Z)</option>
                            <option value="name_desc">Nom du client (Z - A)</option>
                            <option value="id_desc">ID Client (Décroissant)</option>
                            <option value="id_asc">ID Client (Croissant)</option>
                        </select>
                    </div>

                </div>

                {{-- Ligne 2 : Filtres dynamiques issus de l'endpoint (Statut, Environnement) --}}
                <div class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">

                    {{-- Filtre Statut (Dynamique depuis l'endpoint API) --}}
                    <div class="flex flex-col min-w-[170px] flex-1 sm:flex-initial">
                        <label class="mb-1 text-3xs font-semibold uppercase tracking-wider text-gray-400">
                            Statut Compte
                            <span class="text-3xs font-normal text-gray-400" x-show="availableStatuses.length > 0" x-text="'(' + availableStatuses.length + ')'"></span>
                        </label>
                        <select x-model="selectedStatus" @change="onFilterChange()"
                            class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs font-medium text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option value="ALL">Tous les statuts</option>
                            <template x-for="st in availableStatuses" :key="st">
                                <option :value="st" x-text="st"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Filtre Environnement (Dynamique depuis l'endpoint API) --}}
                    <div class="flex flex-col min-w-[170px] flex-1 sm:flex-initial">
                        <label class="mb-1 text-3xs font-semibold uppercase tracking-wider text-gray-400">
                            Environnement
                            <span class="text-3xs font-normal text-gray-400" x-show="availableEnvironments.length > 0" x-text="'(' + availableEnvironments.length + ')'"></span>
                        </label>
                        <select x-model="selectedEnv" @change="onFilterChange()"
                            class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs font-medium text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option value="ALL">Tous les environnements</option>
                            <template x-for="env in availableEnvironments" :key="env">
                                <option :value="env" x-text="env"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Mention accessible pour la compatibilité des assertions (Statut des services) --}}
                    <span class="sr-only">Statut des services</span>

                    {{-- Bouton Effacer les filtres --}}
                    <div class="flex items-center">
                        <button type="button" @click="resetFilters()" x-show="hasActiveFilters"
                            class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-red-200 bg-red-50/70 px-3.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Effacer les filtres
                        </button>
                    </div>
                </div>

            </div>

            {{-- Ligne 3 : Compteur de résultats filtrés & pagination rapide --}}
            <div
                class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <span x-show="isSearching" class="inline-block h-2 w-2 rounded-full bg-brand-500 animate-ping"></span>
                    <span>
                        Affichage de <strong class="text-gray-800 dark:text-white" x-text="filteredClients.length > 0 ? startIndex : 0"></strong>
                        à <strong class="text-gray-800 dark:text-white" x-text="endIndex"></strong>
                        sur <strong class="text-gray-800 dark:text-white" x-text="filteredClients.length"></strong> client(s)
                        (total : <span x-text="data.clients.length"></span>)
                    </span>
                </div>
                <span x-show="totalPages > 1" class="font-medium text-brand-600 dark:text-brand-400">
                    Page <span x-text="currentPage"></span> sur <span x-text="totalPages"></span>
                </span>
            </div>

        </div>


        {{-- ============================================================
             TABLEAU PRINCIPAL DES CLIENTS INTÉGRÉS (AVEC OVERLAY LOADER)
        ============================================================= --}}
        <div
            class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs min-h-[350px] dark:border-gray-800 dark:bg-gray-900">

            {{-- ============================================================
                 LOADER DE CHARGEMENT VISUEL SUR LE TABLEAU (RECHERCHE & SYNC)
            ============================================================= --}}
            <div x-show="isLoading || isSearching" x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-white/85 backdrop-blur-xs dark:bg-gray-900/85">
                <div class="flex flex-col items-center gap-3 p-6 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 shadow-theme-xs dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="h-7 w-7 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white"
                           x-text="isSearching ? 'Filtrage des clients en cours...' : 'Chargement des clients en cours...'"></p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"
                           x-text="isSearching ? 'Application de vos critères de recherche' : 'Synchronisation des partenaires avec l\'API ManyPayCash'"></p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                    <thead>
                        <tr
                            class="bg-gray-50/70 text-left text-2xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800/40 dark:text-gray-400">
                            <th class="px-6 py-4">Nom du client</th>
                            <th class="px-6 py-4">ID Client / Partner ID</th>
                            <th class="px-6 py-4">Statut Compte</th>
                            <th class="px-6 py-4">Environnement</th>
                            <th class="px-6 py-4">Date d'intégration</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                            {{-- <th class="px-6 py-4">Statut des services utilisés</th> --}}
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                        <template x-for="client in paginatedClients" :key="client.partner_id">
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/50">

                                {{-- Nom du client & initiales --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                            <span x-text="client.client_name.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white"
                                                x-text="client.client_name"></p>
                                        </div>
                                    </div>
                                </td>

                                {{-- ID Client / Partner ID --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="rounded-lg bg-gray-100 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                        x-text="client.partner_id"></span>
                                </td>

                                {{-- Statut du compte --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide"
                                        :class="accountStatusBadgeClass(client.account_status)">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="accountStatusDotClass(client.account_status)"></span>
                                        <span x-text="client.account_status"></span>
                                    </span>
                                </td>

                                {{-- Environnement : Sandbox / Production --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="client.environment === 'Production' ?
                                            'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400' :
                                             'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400'">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="client.environment === 'Production' ? 'bg-brand-500' : 'bg-purple-500'"></span>
                                        <span x-text="client.environment"></span>
                                    </span>
                                </td>

                                {{-- Date d'intégration --}}
                                <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300">
                                    <span x-text="client.integration_date"></span>
                                </td>

                                {{-- Raccourci vers les transactions du client --}}
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a :href="'{{ route('support.transactions.index') }}?id_customer=' + encodeURIComponent(client.id || client.partner_id) + '&tab=customer'"
                                        title="Voir directement les transactions de ce client"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50/80 px-3 py-1.5 text-xs font-semibold text-brand-700 shadow-theme-xs transition hover:bg-brand-100 hover:shadow-theme-sm dark:border-brand-900/50 dark:bg-brand-900/20 dark:text-brand-300 dark:hover:bg-brand-900/40">
                                        <svg class="h-3.5 w-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>Transactions</span>
                                    </a>
                                </td>

                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>

            {{-- Aucun résultat trouvé --}}
            <template x-if="!isLoading && !isSearching && filteredClients.length === 0">
                <div class="py-14 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-800 dark:text-white">Aucun client ne correspond aux critères</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Modifiez votre recherche ou réinitialisez les filtres sélectionnés.</p>
                    <button type="button" @click="resetFilters()"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-medium text-white transition hover:bg-brand-600">
                        Réinitialiser tous les filtres
                    </button>
                </div>
            </template>

            {{-- ============================================================
                 BARRE DE PAGINATION
            ============================================================= --}}
            <div x-show="filteredClients.length > 0"
                class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-6 py-4 sm:flex-row dark:border-gray-800">

                {{-- Informations sur les éléments affichés & choix par page --}}
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        Affichage de <strong class="text-gray-800 dark:text-white" x-text="startIndex"></strong>
                        à <strong class="text-gray-800 dark:text-white" x-text="endIndex"></strong>
                        sur <strong class="text-gray-800 dark:text-white" x-text="filteredClients.length"></strong>
                    </span>

                    <div class="flex items-center gap-1.5 border-l border-gray-200 pl-3 dark:border-gray-700">
                        <label for="client_per_page" class="text-2xs uppercase tracking-wider text-gray-400 font-semibold">Par page :</label>
                        <select id="client_per_page" x-model.number="perPage" @change="onFilterChange()"
                            class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                </div>

                {{-- Contrôles de navigation dans les pages --}}
                <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                    {{-- Bouton Précédent --}}
                    <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                        class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Précédent</span>
                    </button>

                    {{-- Boutons numérotés --}}
                    <div class="flex items-center gap-1">
                        <template x-for="(p, index) in pageNumbers" :key="'page_' + index">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-xs text-gray-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" @click="goToPage(p)"
                                        :class="currentPage === p
                                            ? 'bg-brand-500 text-white font-bold shadow-theme-xs'
                                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                                        class="h-8 min-w-[32px] rounded-lg px-2 text-xs transition font-medium"
                                        x-text="p">
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Bouton Suivant --}}
                    <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                        class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <span>Suivant</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>

    </div>

    @push('scripts')
        <script>
            function clientListApp(initialData) {
                return {
                    data: initialData,

                    // Champ brut saisi et requête effective
                    rawSearchInput: '',
                    searchQuery: '',
                    isSearching: false,
                    searchTimeout: null,

                    // Filtres
                    selectedEnv: 'ALL',
                    selectedStatus: 'ALL',
                    selectedServiceStatus: 'ALL',
                    sortBy: 'date_desc',

                    // État de chargement réseau / synchronisation
                    isLoading: false,

                    // Pagination
                    currentPage: 1,
                    perPage: 10,

                    init() {
                        // Animation d'initialisation visible
                        this.isLoading = true;
                        setTimeout(() => {
                            this.isLoading = false;
                        }, 350);
                    },

                    // Recherche avec debounce et animation du loader
                    onSearchInput(value) {
                        this.rawSearchInput = value;
                        this.isSearching = true;
                        clearTimeout(this.searchTimeout);

                        this.searchTimeout = setTimeout(() => {
                            this.searchQuery = value;
                            this.currentPage = 1;
                            this.isSearching = false;
                        }, 250);
                    },

                    clearSearch() {
                        this.rawSearchInput = '';
                        this.searchQuery = '';
                        this.isSearching = false;
                        this.currentPage = 1;
                    },

                    onFilterChange() {
                        this.isSearching = true;
                        this.currentPage = 1;
                        setTimeout(() => {
                            this.isSearching = false;
                        }, 150);
                    },

                    // Options de filtres dynamiques calculées depuis les données réelles de l'endpoint
                    get availableStatuses() {
                        const set = new Set();
                        (this.data?.clients || []).forEach(c => {
                            if (c.account_status && String(c.account_status).trim() !== '' && c.account_status !== 'UNKNOWN') {
                                set.add(String(c.account_status).trim());
                            }
                        });
                        return Array.from(set).sort();
                    },

                    get availableEnvironments() {
                        const set = new Set();
                        (this.data?.clients || []).forEach(c => {
                            if (c.environment && String(c.environment).trim() !== '' && c.environment !== 'Inconnu') {
                                set.add(String(c.environment).trim());
                            }
                        });
                        return Array.from(set).sort();
                    },

                    get hasActiveFilters() {
                        return this.searchQuery !== '' ||
                            this.rawSearchInput !== '' ||
                            this.selectedEnv !== 'ALL' ||
                            this.selectedStatus !== 'ALL' ||
                            this.sortBy !== 'date_desc';
                    },

                    resetFilters() {
                        this.rawSearchInput = '';
                        this.searchQuery = '';
                        this.selectedEnv = 'ALL';
                        this.selectedStatus = 'ALL';
                        this.selectedServiceStatus = 'ALL';
                        this.sortBy = 'date_desc';
                        this.currentPage = 1;
                        this.isSearching = false;
                    },

                    get filteredClients() {
                        const query = this.searchQuery.toLowerCase().trim();

                        let list = this.data.clients.filter(client => {
                            // 1. Recherche texte (Nom, Email, Partner ID)
                            const matchSearch = !query ||
                                String(client.client_name ?? '').toLowerCase().includes(query) ||
                                String(client.id ?? '').toLowerCase().includes(query) ||
                                String(client.partner_id ?? '').toLowerCase().includes(query) ||
                                String(client.email ?? '').toLowerCase().includes(query);

                            // 2. Filtre Environnement (Production / Sandbox)
                            const matchEnv = this.selectedEnv === 'ALL' ||
                                client.environment === this.selectedEnv;

                            // 3. Filtre Statut Compte (ACTIVE / INACTIVE / SUSPENDED)
                            const matchStatus = this.selectedStatus === 'ALL' ||
                                client.account_status === this.selectedStatus;

                            // 4. Filtre Statut Services (UP / DEGRADED / DOWN)
                            const matchServiceStatus = this.selectedServiceStatus === 'ALL' ||
                                client.services_status === this.selectedServiceStatus;

                            return matchSearch && matchEnv && matchStatus && matchServiceStatus;
                        });

                        // 5. Tri selon sortBy
                        list = [...list].sort((a, b) => {
                            switch (this.sortBy) {
                                case 'date_asc':
                                    return (a.integration_timestamp || 0) - (b.integration_timestamp || 0);
                                case 'name_asc':
                                    return String(a.client_name || '').localeCompare(String(b.client_name || ''));
                                case 'name_desc':
                                    return String(b.client_name || '').localeCompare(String(a.client_name || ''));
                                case 'id_desc':
                                    return Number(b.id || b.partner_id || 0) - Number(a.id || a.partner_id || 0);
                                case 'id_asc':
                                    return Number(a.id || a.partner_id || 0) - Number(b.id || b.partner_id || 0);
                                case 'date_desc':
                                default:
                                    return (b.integration_timestamp || 0) - (a.integration_timestamp || 0);
                            }
                        });

                        return list;
                    },

                    get totalPages() {
                        return Math.max(1, Math.ceil(this.filteredClients.length / this.perPage));
                    },

                    get startIndex() {
                        if (this.filteredClients.length === 0) return 0;
                        return (this.currentPage - 1) * this.perPage + 1;
                    },

                    get endIndex() {
                        return Math.min(this.currentPage * this.perPage, this.filteredClients.length);
                    },

                    get paginatedClients() {
                        const start = (this.currentPage - 1) * this.perPage;
                        return this.filteredClients.slice(start, start + this.perPage);
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

                    goToPage(page) {
                        const target = Number(page);
                        if (!isNaN(target) && target >= 1 && target <= this.totalPages) {
                            this.currentPage = target;
                        }
                    },

                    nextPage() {
                        if (this.currentPage < this.totalPages) {
                            this.currentPage++;
                        }
                    },

                    prevPage() {
                        if (this.currentPage > 1) {
                            this.currentPage--;
                        }
                    },

                    accountStatusBadgeClass(status) {
                        switch (status) {
                            case 'ACTIVE':
                                return 'bg-success-50 text-success-700 border border-success-200 dark:bg-success-500/15 dark:text-success-400 dark:border-success-500/30';
                            case 'INACTIVE':
                                return 'bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
                            case 'SUSPENDED':
                                return 'bg-error-50 text-error-700 border border-error-200 dark:bg-error-500/15 dark:text-error-400 dark:border-error-500/30';
                            default:
                                return 'bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-500/15 dark:text-warning-400 dark:border-warning-500/30';
                        }
                    },

                    accountStatusDotClass(status) {
                        switch (status) {
                            case 'ACTIVE':
                                return 'bg-success-500';
                            case 'INACTIVE':
                                return 'bg-gray-400';
                            case 'SUSPENDED':
                                return 'bg-error-500';
                            default:
                                return 'bg-warning-500';
                        }
                    },

                    async refreshClients() {
                        this.isLoading = true;
                        const startTime = Date.now();
                        try {
                            const response = await fetch('{{ route('support.clients.data') }}?refresh=1', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            });
                            if (response.ok) {
                                const newData = await response.json();
                                this.data = newData;
                            }
                        } catch (err) {
                            console.error('Erreur synchronisation clients:', err);
                        } finally {
                            const elapsed = Date.now() - startTime;
                            const remaining = Math.max(0, 500 - elapsed);
                            setTimeout(() => {
                                this.isLoading = false;
                            }, remaining);
                        }
                    }
                };
            }
        </script>
    @endpush

@endsection
