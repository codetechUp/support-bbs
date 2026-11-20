@extends('layouts.app')

@section('title', 'Utilisateurs Sandbox - Support')

@section('content')

    <div x-data="sandboxUserListApp()" x-init="init()" class="space-y-6">
        {{-- EN-TÊTE --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
                    Utilisateurs Sandbox
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Consultation des utilisateurs enregistrés dans l'environnement Sandbox ManyPayCash.
                </p>
            </div>

            <button type="button" @click="loadUsers()" :disabled="loading"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60">
                <svg class="h-4 w-4" :class="loading ? 'animate-spin' : ''" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h5M20 20v-5h-5M5.5 9A7 7 0 0118 6l2 3M18.5 15A7 7 0 016 18l-2-3" />
                </svg>

                <span x-text="loading ? 'Chargement...' : 'Actualiser'"></span>
            </button>
        </div>

        {{-- STATISTIQUES --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- TOTAL API --}}
            <div class="rounded-xl border border-gray-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-300">
                            Total utilisateurs API
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-300"
                            x-text="apiTotal"></p>

                        <p class="mt-1 text-xs text-gray-800 dark:text-gray-300">
                            Nombre annoncé par ManyPayCash
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path
                                d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M12 11a4 4 0 100-8 4 4 0 000 8zm5-7.87a4 4 0 010 7.75"
                                stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- UTILISATEURS CHARGÉS --}}
            <div class="rounded-xl border border-gray-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-300">
                            Utilisateurs chargés
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-300"
                            x-text="users.length"></p>

                        <p class="mt-1 text-xs text-gray-800 dark:text-gray-300">
                            Données récupérées avec succès
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12l4 4L19 6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- ERREUR --}}
        <template x-if="error">
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/10 dark:text-red-400">
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z" />
                    </svg>

                    <span x-text="error"></span>
                </div>
            </div>
        </template>

        {{-- AVERTISSEMENT : RÉPONSE INCOMPLÈTE --}}
        <template x-if="!loading && !error && users.length < apiTotal">
            <div
                class="rounded-lg border border-warning-200 bg-warning-50 p-4 text-sm text-warning-800 dark:border-warning-900/40 dark:bg-warning-900/10 dark:text-warning-400">
                L'API annonce
                <strong x-text="apiTotal"></strong>
                utilisateurs, mais seulement
                <strong x-text="users.length"></strong>
                ont été retournés. La liste affichée peut être incomplète.
            </div>
        </template>

        {{-- TABLEAU --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            {{-- BARRE DE RECHERCHE --}}
            <div
                class="flex flex-col gap-4 border-b border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-900/70 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800 dark:text-white">
                        Liste des utilisateurs
                    </h2>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Recherche par identifiant, client, nom ou adresse e-mail.
                    </p>
                </div>

                <div class="w-full sm:max-w-xs">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="7" stroke-width="2" />
                            <path d="M16 16l4 4" stroke-width="2" stroke-linecap="round" />
                        </svg>

                        <input type="text" x-model="search" @input="updateSearch()"
                            placeholder="Rechercher un utilisateur..."
                            class="h-11 w-full rounded-xl border border-gray-300 bg-white pl-12 pr-4 text-sm text-gray-800 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500">
                    </div>

                    {{-- RECHERCHE DIRECTE PAR E-MAIL --}}
                    <button type="button" x-show="/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(search.trim())" x-cloak
                        @click="findUserByEmail()" :disabled="detailLoading"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-300 bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 transition hover:bg-brand-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20">
                        <svg class="h-4 w-4" :class="detailLoading ? 'animate-spin' : ''" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor">
                            <circle cx="11" cy="11" r="7" stroke-width="2" />
                            <path d="M16 16l4 4" stroke-width="2" stroke-linecap="round" />
                        </svg>

                        <span>Rechercher directement dans ManyPayCash</span>
                    </button>
                </div>
            </div>

            {{-- CHARGEMENT --}}
            <template x-if="loading">
                <div class="flex flex-col items-center justify-center gap-3 py-16">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-brand-500"></div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Récupération des utilisateurs Sandbox...
                    </p>
                </div>
            </template>

            {{-- CONTENU --}}
            <div x-show="!loading" x-cloak>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <button type="button" @click="sortBy('id')" class="inline-flex items-center gap-1">
                                        ID utilisateur
                                        <span
                                            x-text="sortColumn === 'id' ? (sortDirection === 'asc' ? '↑' : '↓') : '↕'"></span>
                                    </button>
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <button type="button" @click="sortBy('idCustomer')"
                                        class="inline-flex items-center gap-1">
                                        ID client
                                        <span
                                            x-text="sortColumn === 'idCustomer' ? (sortDirection === 'asc' ? '↑' : '↓') : '↕'"></span>
                                    </button>
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nom complet
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Adresse e-mail
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Informations
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="user in paginatedUsers" :key="user.id">
                                <tr class="transition-colors hover:bg-brand-50/40 dark:hover:bg-white/5">
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="font-medium text-gray-800 dark:text-white"
                                            x-text="'#' + user.id"></span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"
                                            x-text="'#' + user.idCustomer"></span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-300"
                                            x-text="fullName(user)"></span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="text-sm text-gray-700 dark:text-gray-300"
                                            x-text="user.email || 'Non renseigné'"></span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <button type="button" @click="showDetails(user)"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                            Voir
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="!error && filteredUsers.length === 0">
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor">
                                                <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                                                <path d="M16 16l4 4" stroke-width="1.5" stroke-linecap="round" />
                                            </svg>

                                            <p class="font-medium text-gray-700 dark:text-gray-300">
                                                Aucun utilisateur trouvé
                                            </p>

                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Modifiez votre recherche ou actualisez la liste.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION LOCALE --}}
                <div
                    class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50/50 px-5 py-4 dark:border-gray-800 dark:bg-gray-900/40 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Affichage de
                        <strong x-text="filteredUsers.length ? ((currentPage - 1) * perPage + 1) : 0"></strong>
                        à
                        <strong x-text="Math.min(currentPage * perPage, filteredUsers.length)"></strong>
                        sur
                        <strong x-text="filteredUsers.length"></strong>
                        résultat(s)
                    </p>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="currentPage--" :disabled="currentPage <= 1"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">
                            Précédent
                        </button>

                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            Page <strong x-text="currentPage"></strong>
                            /
                            <strong x-text="totalPages"></strong>
                        </span>

                        <button type="button" @click="currentPage++" :disabled="currentPage >= totalPages"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">
                            Suivant
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODALE DÉTAILS --}}
        <div x-show="selectedUser !== null" x-cloak x-transition.opacity @keydown.escape.window="closeDetails()"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm">
            <div x-show="selectedUser !== null" x-transition @click.outside="closeDetails()"
                class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl shadow-gray-950/20 dark:border-gray-700 dark:bg-gray-900">
                {{-- EN-TÊTE MODALE --}}
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-300">
                            Détails utilisateur
                        </h3>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Informations du compte Sandbox
                        </p>
                    </div>

                    <button type="button" @click="closeDetails()"
                        class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                {{-- CHARGEMENT DES DÉTAILS --}}
                <template x-if="detailLoading">
                    <div class="flex flex-col items-center justify-center gap-3 py-10">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-brand-500"></div>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Récupération des informations auprès de ManyPayCash...
                        </p>
                    </div>
                </template>

                {{-- AVERTISSEMENT DE RÉCUPÉRATION --}}
                {{-- <template x-if="detailError">
                    <div
                        class="rounded-lg border border-warning-200 bg-warning-50 p-4 text-sm text-warning-800 dark:border-warning-900/40 dark:bg-warning-900/10 dark:text-warning-400">
                        <p class="font-medium">
                            Les informations détaillées n'ont pas pu être actualisées.
                        </p>
                        <p class="mt-1 text-xs">
                            Les données éventuellement affichées proviennent de la liste des utilisateurs.
                        </p>
                    </div>
                </template> --}}

                {{-- INFORMATIONS UTILISATEUR --}}
                <template x-if="selectedUser">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mt-2">

                        {{-- IDENTIFIANT UTILISATEUR --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                ID utilisateur
                            </p>

                            <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="'#' + (selectedUser.id ?? '—')"></p>
                        </div>

                        {{-- IDENTIFIANT CLIENT --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                ID client
                            </p>

                            <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="'#' + (selectedUser.idCustomer ?? '—')"></p>
                        </div>

                        {{-- PRÉNOM --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Prénom
                            </p>

                            <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="selectedUser.prenom || 'Non renseigné'"></p>
                        </div>

                        {{-- NOM --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Nom
                            </p>

                            <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="selectedUser.nom || 'Non renseigné'"></p>
                        </div>

                        {{-- E-MAIL --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Adresse e-mail
                            </p>

                            <p class="mt-1.5 break-all text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="selectedUser.email || 'Non renseigné'"></p>
                        </div>

                        {{-- DATE DE CRÉATION --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Date de création
                            </p>

                            <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="formatDate(selectedUser.createdAt)"></p>
                        </div>

                        {{-- DATE DE MODIFICATION --}}
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Dernière modification
                            </p>

                            <p class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-gray-300"
                                x-text="formatDate(selectedUser.updatedAt)"></p>
                        </div>

                    </div>
                </template>

                {{-- ACTIONS --}}
                <div class="mt-5 flex justify-end">
                    <button type="button" @click="closeDetails()"
                        class="rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-400">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function sandboxUserListApp() {
            return {
                users: [],
                apiTotal: 0,

                loading: false,
                error: null,

                search: '',
                sortColumn: 'id',
                sortDirection: 'desc',

                currentPage: 1,
                perPage: 10,

                selectedUser: null,
                detailLoading: false,
                detailError: null,
                detailRequestId: 0,

                dataUrl: @json(route('support.sandbox.users.data')),

                // URL de consultation détaillée d'un utilisateur.
                detailUrlTemplate: @json(route('support.sandbox.users.show', ['email' => '__EMAIL__'])),

                // Construction du nom complet à partir des champs API.
                fullName(user) {
                    return [user?.prenom, user?.nom]
                        .map(value => String(value ?? '').trim())
                        .filter(Boolean)
                        .join(' ') || 'Non renseigné';
                },

                // Formatage des dates retournées par ManyPayCash.
                formatDate(value) {
                    if (!value) {
                        return 'Non renseignée';
                    }

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

                get filteredUsers() {
                    let result = [...this.users];

                    const term = this.search.trim().toLowerCase();

                    if (term) {
                        result = result.filter(user => {
                            return String(user.id ?? '').toLowerCase().includes(term) ||
                                String(user.idCustomer ?? '').toLowerCase().includes(term) ||
                                String(user.email ?? '').toLowerCase().includes(term) ||
                                this.fullName(user).toLowerCase().includes(term);
                        });
                    }

                    result.sort((a, b) => {
                        const first = Number(a[this.sortColumn] ?? 0);
                        const second = Number(b[this.sortColumn] ?? 0);

                        return this.sortDirection === 'asc' ?
                            first - second :
                            second - first;
                    });

                    return result;
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredUsers.length / this.perPage));
                },

                get paginatedUsers() {
                    const start = (this.currentPage - 1) * this.perPage;

                    return this.filteredUsers.slice(start, start + this.perPage);
                },

                init() {
                    this.loadUsers();
                },

                // Chargement de la liste des utilisateurs.
                async loadUsers() {
                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch(
                            this.dataUrl + '?page=1&limit=100', {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                cache: 'no-store'
                            }
                        );

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(
                                result.message || 'Erreur lors du chargement des utilisateurs.'
                            );
                        }

                        if (!Array.isArray(result.data)) {
                            throw new Error('La structure de la réponse API est invalide.');
                        }

                        this.users = result.data;
                        this.apiTotal = Number(result.meta?.total ?? result.data.length);

                        this.currentPage = 1;

                    } catch (exception) {
                        console.error('Sandbox users:', exception);

                        this.error = exception.message ||
                            'Une erreur est survenue pendant le chargement.';

                        this.users = [];
                        this.apiTotal = 0;

                    } finally {
                        this.loading = false;
                    }
                },

                sortBy(column) {
                    if (this.sortColumn === column) {
                        this.sortDirection = this.sortDirection === 'asc' ?
                            'desc' :
                            'asc';
                    } else {
                        this.sortColumn = column;
                        this.sortDirection = 'asc';
                    }

                    this.currentPage = 1;
                },

                // Ouvre la modale depuis une ligne du tableau.
                showDetails(user) {
                    if (!user?.email) {
                        this.selectedUser = user;
                        this.detailError = 'Aucune adresse e-mail disponible pour cet utilisateur.';
                        return;
                    }

                    this.selectedUser = user;
                    this.loadUserDetails(user.email);
                },

                // Recherche directe d'un utilisateur à partir de son e-mail.
                findUserByEmail() {
                    const email = this.search.trim();

                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        return;
                    }

                    this.selectedUser = {
                        email: email
                    };

                    this.loadUserDetails(email);
                },

                // Appel de GET /sandbox/user/{email} via Laravel.
                async loadUserDetails(email) {
                    const requestId = ++this.detailRequestId;

                    this.detailLoading = true;
                    this.detailError = null;

                    try {
                        const url = this.detailUrlTemplate.replace(
                            '__EMAIL__',
                            encodeURIComponent(email)
                        );

                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            cache: 'no-store'
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(
                                result.message || 'Impossible de récupérer cet utilisateur.'
                            );
                        }

                        if (
                            !result ||
                            typeof result !== 'object' ||
                            !result.email
                        ) {
                            throw new Error('La réponse détaillée de ManyPayCash est invalide.');
                        }

                        // Évite qu'une ancienne requête écrase une recherche plus récente.
                        if (requestId === this.detailRequestId) {
                            this.selectedUser = result;
                        }

                    } catch (exception) {
                        console.error('Sandbox user details:', exception);

                        if (requestId === this.detailRequestId) {
                            this.detailError = exception.message ||
                                'Une erreur est survenue lors de la récupération des détails.';
                        }

                    } finally {
                        if (requestId === this.detailRequestId) {
                            this.detailLoading = false;
                        }
                    }
                },

                // Fermeture de la modale et invalidation d'une éventuelle requête en cours.
                closeDetails() {
                    this.detailRequestId++;
                    this.selectedUser = null;
                    this.detailLoading = false;
                    this.detailError = null;
                },

                // Revenir à la première page lors d'une nouvelle recherche.
                updateSearch() {
                    this.currentPage = 1;
                }
            };
        }
    </script>

@endsection
