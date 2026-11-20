@extends('layouts.app')

@section('title', 'Clients Sandbox - Support')

@section('content')

<div x-data="sandboxCustomerListApp()" x-init="init()" class="space-y-6">

    {{-- En-tête --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Clients Sandbox</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Consultation des entreprises enregistrées dans ManyPayCash Sandbox.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Synchronisation : <strong x-text="checkedAt">—</strong>
                </span>

                <button @click="refreshCustomers()" :disabled="isLoading"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4" :class="isLoading && 'animate-spin'" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="isLoading ? 'Synchronisation...' : 'Actualiser'"></span>
                </button>

                <a href="{{ route('support.sandbox.customers.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Nouveau client
                </a>
            </div>
        </div>
    </div>

    {{-- Recherche et filtres --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4">

            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <input type="search" x-model="rawSearchInput"
                        @input="onSearchInput($event.target.value)"
                        placeholder="Rechercher par entreprise, ID ou pays..."
                        class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                    <button x-show="rawSearchInput" x-cloak @click="clearSearch()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700">
                        ✕
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <label for="sandbox_sort_by" class="text-xs font-semibold text-gray-400">Trier :</label>
                    <select id="sandbox_sort_by" x-model="sortBy" @change="onFilterChange()"
                        class="h-12 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="date_desc">Plus récents</option>
                        <option value="date_asc">Plus anciens</option>
                        <option value="name_asc">Entreprise (A - Z)</option>
                        <option value="name_desc">Entreprise (Z - A)</option>
                        <option value="id_desc">ID décroissant</option>
                        <option value="id_asc">ID croissant</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div class="flex min-w-[170px] flex-1 flex-col sm:flex-initial">
                    <label class="mb-1 text-xs font-semibold text-gray-400">
                        Pays (<span x-text="availableCountries.length"></span>)
                    </label>
                    <select x-model="selectedCountry" @change="onFilterChange()"
                        class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="ALL">Tous les pays</option>
                        <template x-for="country in availableCountries" :key="country">
                            <option :value="country" x-text="country"></option>
                        </template>
                    </select>
                </div>

                <div class="flex min-w-[170px] flex-1 flex-col sm:flex-initial">
                    <label class="mb-1 text-xs font-semibold text-gray-400">
                        Entreprise (<span x-text="availableCompanies.length"></span>)
                    </label>
                    <select x-model="selectedCompany" @change="onFilterChange()"
                        class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="ALL">Toutes les entreprises</option>
                        <template x-for="company in availableCompanies" :key="company">
                            <option :value="company" x-text="company"></option>
                        </template>
                    </select>
                </div>

                <button x-show="hasActiveFilters" x-cloak @click="resetFilters()"
                    class="h-10 rounded-xl border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400">
                    Effacer les filtres
                </button>
            </div>
        </div>

        {{-- Compteurs --}}
        <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 sm:flex-row sm:justify-between dark:border-gray-800 dark:text-gray-400">
            <span>
                Affichage de <strong x-text="startIndex"></strong> à
                <strong x-text="endIndex"></strong> sur
                <strong x-text="filteredCustomers.length"></strong> client(s)
                (récupérés : <strong x-text="customers.length"></strong>)
            </span>

            <span x-show="totalPages > 1" class="font-medium text-brand-600 dark:text-brand-400">
                Page <span x-text="currentPage"></span> sur <span x-text="totalPages"></span>
            </span>
        </div>

        {{-- Avertissement sur les données manquantes --}}
        <div x-show="!isLoading && !errorMessage && customers.length < apiTotal"
            x-cloak
            class="mt-3 rounded-xl bg-warning-50 border border-warning-500 p-3 text-xs text-gray-900 dark:border-warning-500 dark:bg-gray-800 dark:text-warning-400">
            ManyPayCash annonce <strong x-text="apiTotal"></strong> clients, mais seulement
            <strong x-text="customers.length"></strong> ont été récupérés.
            La pagination de l'API ne renvoie actuellement pas les pages suivantes.
            Les recherches et filtres portent uniquement sur les données récupérées.
        </div>
    </div>

    {{-- Tableau --}}
    <div class="relative min-h-[350px] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

        {{-- Chargement --}}
        <div x-show="isLoading" x-cloak
            class="absolute inset-0 z-20 flex items-center justify-center bg-white/85 backdrop-blur-sm dark:bg-gray-900/85">
            <div class="text-center">
                <svg class="mx-auto h-10 w-10 animate-spin text-brand-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Chargement des clients...
                </p>
                <p class="mt-1 text-xs text-gray-500">Synchronisation avec ManyPayCash Sandbox</p>
            </div>
        </div>

        {{-- Erreur --}}
        <template x-if="errorMessage">
            <div class="m-6 rounded-xl border border-red-200 bg-red-50 p-5 dark:border-red-900/50 dark:bg-red-900/20">
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">
                    Impossible de récupérer les clients Sandbox.
                </p>
                <p class="mt-1 text-xs text-red-600 dark:text-red-300" x-text="errorMessage"></p>
                <button @click="refreshCustomers()"
                    class="mt-3 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                    Réessayer
                </button>
            </div>
        </template>

        {{-- Liste des clients --}}
        <div x-show="!errorMessage" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead>
                    <tr class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800/40 dark:text-gray-400">
                        <th class="px-6 py-4">Entreprise</th>
                        <th class="px-6 py-4">ID Client</th>
                        <th class="px-6 py-4">Pays</th>
                        <th class="px-6 py-4">Date de création</th>
                        <th class="px-6 py-4">Dernière mise à jour</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="customer in paginatedCustomers" :key="customer.id">
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold uppercase text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"
                                        x-text="getInitials(customer.entreprise)"></div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white"
                                            x-text="customer.entreprise || 'Entreprise non renseignée'"></p>
                                        <p class="mt-0.5 text-xs text-gray-400">Client Sandbox</p>
                                    </div>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="rounded-lg bg-gray-100 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    x-text="'#' + customer.id"></span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="rounded-lg bg-gray-100 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    x-text="customer.pays || 'Non renseigné'"></span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300"
                                x-text="formatDate(customer.createdAt)"></td>

                            <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300"
                                x-text="formatDate(customer.updatedAt)"></td>

                                <td class="whitespace-nowrap px-6 py-4 text-right">
    <a :href="usersUrl.replace('__ID__', encodeURIComponent(customer.id))"
        class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20">

        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>

        Voir les utilisateurs
    </a>
</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Aucun résultat --}}
        <template x-if="!isLoading && !errorMessage && filteredCustomers.length === 0">
            <div class="py-14 text-center">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                    Aucun client ne correspond aux critères.
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Modifiez votre recherche ou réinitialisez les filtres.
                </p>
                <button @click="resetFilters()"
                    class="mt-4 rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                    Réinitialiser les filtres
                </button>
            </div>
        </template>

        {{-- Pagination locale --}}
        <div x-show="filteredCustomers.length > 0 && !errorMessage"
            class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-6 py-4 sm:flex-row dark:border-gray-800">

            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                <span>
                    <strong x-text="startIndex"></strong> à
                    <strong x-text="endIndex"></strong> sur
                    <strong x-text="filteredCustomers.length"></strong>
                </span>

                <div class="flex items-center gap-2 border-l border-gray-200 pl-4 dark:border-gray-700">
                    <label for="sandbox_per_page">Par page :</label>
                    <select id="sandbox_per_page" x-model.number="perPage" @change="onFilterChange()"
                        class="rounded-lg border border-gray-200 bg-gray-50 py-1 pl-2 pr-8 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
            </div>

            <div x-show="totalPages > 1" class="flex items-center gap-1.5">
                <button @click="prevPage()" :disabled="currentPage === 1"
                    class="rounded-xl border border-gray-200 px-3 py-2 text-xs disabled:opacity-40 dark:border-gray-700">
                    Précédent
                </button>

                <template x-for="page in pageNumbers" :key="String(page)">
                    <div>
                        <button x-show="page !== '...'" @click="goToPage(page)"
                            :class="currentPage === page
                                ? 'bg-brand-500 text-white'
                                : 'border border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300'"
                            class="h-8 min-w-8 rounded-lg px-2 text-xs font-semibold"
                            x-text="page"></button>
                        <span x-show="page === '...'" class="px-1 text-xs text-gray-400">...</span>
                    </div>
                </template>

                <button @click="nextPage()" :disabled="currentPage === totalPages"
                    class="rounded-xl border border-gray-200 px-3 py-2 text-xs disabled:opacity-40 dark:border-gray-700">
                    Suivant
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function sandboxCustomerListApp() {
        return {
            customers: [],
            apiTotal: 0,
            checkedAt: '—',

            rawSearchInput: '',
            searchQuery: '',
            searchTimeout: null,

            selectedCountry: 'ALL',
            selectedCompany: 'ALL',
            sortBy: 'date_desc',

            isLoading: false,
            errorMessage: null,

            currentPage: 1,
            perPage: 10,

            dataUrl: @js(route('support.sandbox.customers.data')),
            usersUrl: @js(url('/support/sandbox/customers/__ID__/users')),

            init() {
                this.loadCustomers();
            },

            async loadCustomers() {
                if (this.isLoading) return;

                this.isLoading = true;
                this.errorMessage = null;

                try {
                    const url = new URL(this.dataUrl, window.location.origin);
                    url.searchParams.set('page', 1);
                    url.searchParams.set('limit', 100);

                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.message || 'Erreur de récupération des clients.'
                        );
                    }

                    if (!result.meta || !Array.isArray(result.data)) {
                        throw new Error('La réponse de ManyPayCash est invalide.');
                    }

                    // Suppression des doublons éventuels par ID.
                    const uniqueCustomers = new Map();

                    result.data.forEach(customer => {
                        if (customer.id !== undefined && customer.id !== null) {
                            uniqueCustomers.set(String(customer.id), customer);
                        }
                    });

                    this.customers = Array.from(uniqueCustomers.values());
                    this.apiTotal = Number(result.meta.total || 0);
                    this.currentPage = 1;

                    this.checkedAt = new Intl.DateTimeFormat('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    }).format(new Date());

                    if (!this.availableCountries.includes(this.selectedCountry)) {
                        this.selectedCountry = 'ALL';
                    }

                    if (!this.availableCompanies.includes(this.selectedCompany)) {
                        this.selectedCompany = 'ALL';
                    }

                    console.log('Clients récupérés :', this.customers.length);
                    console.log('Total annoncé par API :', this.apiTotal);
                    console.log('Réponse API :', result);

                } catch (error) {
                    console.error('Erreur Sandbox :', error);
                    this.errorMessage = error.message || 'Une erreur est survenue.';
                } finally {
                    this.isLoading = false;
                }
            },

            refreshCustomers() {
                return this.loadCustomers();
            },

            onSearchInput(value) {
                this.rawSearchInput = value;
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {
                    this.searchQuery = value.trim().toLowerCase();
                    this.currentPage = 1;
                }, 250);
            },

            clearSearch() {
                clearTimeout(this.searchTimeout);
                this.rawSearchInput = '';
                this.searchQuery = '';
                this.currentPage = 1;
            },

            onFilterChange() {
                this.currentPage = 1;
            },

            get availableCountries() {
                return [...new Set(
                    this.customers
                        .map(customer => String(customer.pays || '').trim())
                        .filter(Boolean)
                )].sort((a, b) => a.localeCompare(b));
            },

            get availableCompanies() {
                return [...new Set(
                    this.customers
                        .map(customer => String(customer.entreprise || '').trim())
                        .filter(Boolean)
                )].sort((a, b) => a.localeCompare(b));
            },

            get hasActiveFilters() {
                return this.searchQuery !== '' ||
                    this.rawSearchInput !== '' ||
                    this.selectedCountry !== 'ALL' ||
                    this.selectedCompany !== 'ALL' ||
                    this.sortBy !== 'date_desc';
            },

            resetFilters() {
                clearTimeout(this.searchTimeout);
                this.rawSearchInput = '';
                this.searchQuery = '';
                this.selectedCountry = 'ALL';
                this.selectedCompany = 'ALL';
                this.sortBy = 'date_desc';
                this.currentPage = 1;
            },

            get filteredCustomers() {
                const query = this.searchQuery;

                return this.customers.filter(customer => {
                    const company = String(customer.entreprise || '').toLowerCase();
                    const id = String(customer.id || '');
                    const country = String(customer.pays || '').toLowerCase();

                    const matchesSearch = !query ||
                        company.includes(query) ||
                        id.includes(query) ||
                        country.includes(query);

                    const matchesCountry = this.selectedCountry === 'ALL' ||
                        customer.pays === this.selectedCountry;

                    const matchesCompany = this.selectedCompany === 'ALL' ||
                        customer.entreprise === this.selectedCompany;

                    return matchesSearch && matchesCountry && matchesCompany;
                }).sort((a, b) => {
                    switch (this.sortBy) {
                        case 'date_asc':
                            return this.getTimestamp(a.createdAt) - this.getTimestamp(b.createdAt);
                        case 'name_asc':
                            return String(a.entreprise || '').localeCompare(String(b.entreprise || ''));
                        case 'name_desc':
                            return String(b.entreprise || '').localeCompare(String(a.entreprise || ''));
                        case 'id_asc':
                            return Number(a.id) - Number(b.id);
                        case 'id_desc':
                            return Number(b.id) - Number(a.id);
                        default:
                            return this.getTimestamp(b.createdAt) - this.getTimestamp(a.createdAt);
                    }
                });
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredCustomers.length / this.perPage));
            },

            get startIndex() {
                return this.filteredCustomers.length
                    ? (this.currentPage - 1) * this.perPage + 1
                    : 0;
            },

            get endIndex() {
                return Math.min(
                    this.currentPage * this.perPage,
                    this.filteredCustomers.length
                );
            },

            get paginatedCustomers() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredCustomers.slice(start, start + this.perPage);
            },

            get pageNumbers() {
                const total = this.totalPages;
                const current = this.currentPage;

                if (total <= 7) {
                    return Array.from({ length: total }, (_, i) => i + 1);
                }

                if (current <= 4) return [1, 2, 3, 4, 5, '...', total];

                if (current >= total - 3) {
                    return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                }

                return [1, '...', current - 1, current, current + 1, '...', total];
            },

            goToPage(page) {
                const target = Number(page);

                if (Number.isInteger(target) && target >= 1 && target <= this.totalPages) {
                    this.currentPage = target;
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },

            getInitials(name) {
                if (!name) return 'SB';

                return String(name).trim()
                    .split(/\s+/)
                    .slice(0, 2)
                    .map(word => word.charAt(0))
                    .join('')
                    .toUpperCase();
            },

            getTimestamp(value) {
                const timestamp = value ? new Date(value).getTime() : 0;
                return Number.isNaN(timestamp) ? 0 : timestamp;
            },

            formatDate(value) {
                if (!value) return '—';

                const date = new Date(value);

                if (Number.isNaN(date.getTime())) return '—';

                return new Intl.DateTimeFormat('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(date);
            }
        };
    }
</script>
@endpush
