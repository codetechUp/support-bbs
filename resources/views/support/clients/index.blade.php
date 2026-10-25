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
                        Consultation des partenaires et entreprises connectés à la plateforme ManyPayCash.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Dernière synchronisation : <span class="font-medium text-gray-700 dark:text-gray-300"
                            x-text="data.summary.checked_at">{{ $checkedAt }}</span>
                    </span>

                    {{-- Bouton Actualiser --}}
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
         BARRE D'OUTILS ET FILTRES MULTIPLES (CÔTÉ SUPPORT)
    ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Recherche textuelle multi-champs --}}
                <div class="relative w-full lg:w-80">
                    <input type="text" x-model="searchQuery" placeholder="Rechercher par nom, Partner ID ou email..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-9 pr-4 text-xs text-gray-800 placeholder-gray-400 transition focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500" />
                    <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                {{-- Sélecteurs de filtres côté client --}}
                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-3">

                    {{-- Filtre Environnement --}}
                    <div class="flex flex-col">
                        <label class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Environnement</label>
                        <select x-model="selectedEnv"
                            class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option value="ALL">Tous les environnements</option>
                            <option value="Production">Production</option>
                            <option value="Sandbox">Sandbox</option>
                        </select>
                    </div>

                    {{-- Filtre Statut Compte --}}
                    <div class="flex flex-col">
                        <label class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Statut Compte</label>
                        <select x-model="selectedStatus"
                            class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option value="ALL">Tous les statuts</option>
                            <option value="ACTIVE">Actif (ACTIVE)</option>
                            <option value="INACTIVE">Inactif (INACTIVE)</option>
                            <option value="SUSPENDED">Suspendu (SUSPENDED)</option>
                        </select>
                    </div>

                    {{-- Filtre Statut Services --}}
                    <div class="flex flex-col">
                        <label class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Statut Services</label>
                        <select x-model="selectedServiceStatus"
                            class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <option value="ALL">Tous les services</option>
                            <option value="UP">Opérationnels (UP)</option>
                            <option value="DEGRADED">Dégradés (DEGRADED)</option>
                            <option value="DOWN">Indisponibles (DOWN)</option>
                        </select>
                    </div>

                    {{-- Réinitialiser les filtres --}}
                    <div class="flex flex-col justify-end">
                        <button type="button" @click="resetFilters()" x-show="hasActiveFilters"
                            class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                            Effacer
                        </button>
                    </div>

                </div>

            </div>

            {{-- Compteur de résultats filtrés --}}
            <div
                class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <span>
                    Affichage de <strong class="text-gray-800 dark:text-white" x-text="filteredClients.length"></strong>
                    client(s) sur <span x-text="data.clients.length"></span>
                </span>
            </div>

        </div>


        {{-- ============================================================
         TABLEAU PRINCIPAL DES CLIENTS INTÉGRÉS
    ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

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
                            {{-- <th class="px-6 py-4">Statut des services utilisés</th> --}}
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                        <template x-for="client in filteredClients" :key="client.partner_id">
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/50">

                                {{-- Nom du client & email --}}
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

                                {{-- Statut des services utilisés --}}
                                {{-- <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5 min-w-[220px]">
                                        <template x-for="service in client.used_services" :key="service.code">
                                            <div class="flex items-center justify-between gap-2 rounded-lg border px-2.5 py-1.5 text-xs transition"
                                                :class="serviceBadgeClass(service.status)">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-2 w-2 rounded-full"
                                                        :class="serviceDotClass(service.status)"></span>
                                                    <span class="font-medium text-gray-800 dark:text-gray-200"
                                                        x-text="service.name"></span>
                                                    <span
                                                        class="rounded bg-black/5 px-1 py-0.5 font-mono text-3xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300"
                                                        x-text="service.code"></span>
                                                </div>
                                                <span
                                                    class="rounded px-1.5 py-0.5 text-3xs font-bold uppercase tracking-wider"
                                                    :class="serviceTagClass(service.status)"
                                                    x-text="service.status"></span>
                                            </div>
                                        </template>
                                    </div>
                                </td> --}}

                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>

            {{-- Aucun résultat trouvé --}}
            <template x-if="filteredClients.length === 0">
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-800 dark:text-white">Aucun client ne correspond aux
                        critères</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Modifiez votre recherche ou réinitialisez les
                        filtres sélectionnés.</p>
                    <button type="button" @click="resetFilters()"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-medium text-white transition hover:bg-brand-600">
                        Réinitialiser les filtres
                    </button>
                </div>
            </template>

        </div>

    </div>

    @push('scripts')
        <script>
            function clientListApp(initialData) {
                return {
                    data: initialData,
                    searchQuery: '',
                    selectedEnv: 'ALL',
                    selectedStatus: 'ALL',
                    selectedServiceStatus: 'ALL',
                    isLoading: false,

                    get hasActiveFilters() {
                        return this.searchQuery !== '' ||
                            this.selectedEnv !== 'ALL' ||
                            this.selectedStatus !== 'ALL' ||
                            this.selectedServiceStatus !== 'ALL';
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.selectedEnv = 'ALL';
                        this.selectedStatus = 'ALL';
                        this.selectedServiceStatus = 'ALL';
                    },

                    get filteredClients() {
                        const query = this.searchQuery.toLowerCase().trim();

                        return this.data.clients.filter(client => {
                            // 1. Recherche texte (Nom, Email, Partner ID)
                            const matchSearch = !query ||
                                String(client.client_name ?? '').toLowerCase().includes(query) ||
                                String(client.id ?? '').toLowerCase().includes(query) ||
                                String(client.partner_id ?? '').toLowerCase().includes(query);

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

                    serviceStatusBadgeClass(status) {
                        switch (status) {
                            case 'UP':
                                return 'bg-success-50 text-success-700 border border-success-200 dark:bg-success-500/15 dark:text-success-400 dark:border-success-500/30';
                            case 'DEGRADED':
                                return 'bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-500/15 dark:text-warning-400 dark:border-warning-500/30';
                            case 'DOWN':
                                return 'bg-error-50 text-error-700 border border-error-200 dark:bg-error-500/15 dark:text-error-400 dark:border-error-500/30';
                            default:
                                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                        }
                    },

                    serviceStatusDotClass(status) {
                        switch (status) {
                            case 'UP':
                                return 'bg-success-500';
                            case 'DEGRADED':
                                return 'bg-warning-500';
                            case 'DOWN':
                                return 'bg-error-500';
                            default:
                                return 'bg-gray-400';
                        }
                    },

                    serviceBadgeClass(status) {
                        switch (status) {
                            case 'UP':
                                return 'border-success-200 bg-success-50/40 dark:border-success-900/40 dark:bg-success-950/20';
                            case 'DEGRADED':
                                return 'border-warning-200 bg-warning-50/40 dark:border-warning-900/40 dark:bg-warning-950/20';
                            case 'DOWN':
                                return 'border-error-200 bg-error-50/40 dark:border-error-900/40 dark:bg-error-950/20';
                            default:
                                return 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800';
                        }
                    },

                    serviceDotClass(status) {
                        switch (status) {
                            case 'UP':
                                return 'bg-success-500';
                            case 'DEGRADED':
                                return 'bg-warning-500';
                            case 'DOWN':
                                return 'bg-error-500';
                            default:
                                return 'bg-gray-400';
                        }
                    },

                    serviceTagClass(status) {
                        switch (status) {
                            case 'UP':
                                return 'bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300';
                            case 'DEGRADED':
                                return 'bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300';
                            case 'DOWN':
                                return 'bg-error-100 text-error-800 dark:bg-error-900/50 dark:text-error-300';
                            default:
                                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                        }
                    },

                    async refreshClients() {
                        this.isLoading = true;
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
                            this.isLoading = false;
                        }
                    }
                };
            }
        </script>
    @endpush

@endsection
