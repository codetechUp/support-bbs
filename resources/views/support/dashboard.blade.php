@extends('layouts.app')

@section('title', 'Disponibilité des Services Partenaires')

@section('content')

    @php
        $summary = $statusData['summary'] ?? [];
        $partners = $statusData['partners'] ?? [];
        $services = $statusData['services'] ?? [];

        $upCount = $summary['up_count'] ?? 0;
        $degradedCount = $summary['degraded_count'] ?? 0;
        $downCount = $summary['down_count'] ?? 0;
        $totalServices = $summary['total_services'] ?? count($services);
        $globalStatus = $summary['global_status'] ?? 'UP';
        $checkedAtHuman = $summary['checked_at_human'] ?? now()->format('H:i:s');
    @endphp

    <div x-data="partnerServicesApp(@js($statusData))" class="space-y-6">

        {{-- ============================================================
         EN-TÊTE : DISPONIBILITÉ DES SERVICES PARTENAIRES
    ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">

                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">
                        Disponibilité des Services Partenaires
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Surveillance en temps réel de la disponibilité opérationnelle de chaque service (UP, DOWN,
                        DEGRADED).
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Dernier contrôle : <span class="font-medium text-gray-700 dark:text-gray-300"
                            x-text="data.summary.checked_at_human">{{ $checkedAtHuman }}</span>
                    </span>

                    {{-- Bouton Actualiser --}}
                    <button type="button" @click="refreshData()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            :class="isLoading ? 'animate-spin text-brand-500' : ''" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isLoading ? 'Vérification...' : 'Actualiser'">Actualiser</span>
                    </button>
                </div>

            </div>
        </div>


        {{-- ============================================================
         LISTE ET FILTRES DES SERVICES PARTENAIRES
    ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            {{-- BARRE DE RECHERCHE & ONGLETS DE FILTRE --}}
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Onglets de statut --}}
                    <div class="flex items-center rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                        <button type="button" @click="selectedTab = 'ALL'"
                            :class="selectedTab === 'ALL' ?
                                'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' :
                                'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                            Tous (<span x-text="data.partners.length">{{ count($partners) }}</span>)
                        </button>

                        <button type="button" @click="selectedTab = 'UP'"
                            :class="selectedTab === 'UP' ?
                                'bg-white text-success-700 shadow-sm dark:bg-gray-700 dark:text-success-400' :
                                'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                            UP (<span x-text="countByStatus('UP')">{{ $upCount }}</span>)
                        </button>

                        <button type="button" @click="selectedTab = 'DEGRADED'"
                            :class="selectedTab === 'DEGRADED' ?
                                'bg-white text-warning-700 shadow-sm dark:bg-gray-700 dark:text-warning-400' :
                                'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                            DEGRADED (<span x-text="countByStatus('DEGRADED')">{{ $degradedCount }}</span>)
                        </button>

                        <button type="button" @click="selectedTab = 'DOWN'"
                            :class="selectedTab === 'DOWN' ?
                                'bg-white text-error-700 shadow-sm dark:bg-gray-700 dark:text-error-400' :
                                'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                            DOWN (<span x-text="countByStatus('DOWN')">{{ $downCount }}</span>)
                        </button>
                    </div>

                    {{-- Champ de recherche --}}
                    <div class="relative w-full sm:w-72">
                        <input type="text" x-model="searchQuery" placeholder="Filtrer par nom ou service..."
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-9 pr-4 text-xs text-gray-800 placeholder-gray-400 transition focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500" />
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                </div>
            </div>

            {{-- GRILLE DES PARTENAIRES --}}
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                    <template x-for="partner in filteredPartners" :key="partner.nom">
                        <div class="flex flex-col justify-between rounded-2xl border p-5 transition-all hover:shadow-theme-md"
                            :class="partnerCardBorderClass(partner.status)">
                            <div>
                                {{-- En-tête de carte : Nom et Statut Principal --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white"
                                            x-text="partner.nom"></h3>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Services : <span class="font-mono font-medium text-gray-700 dark:text-gray-300"
                                                x-text="partner.services_list"></span>
                                        </p>
                                    </div>

                                    {{-- Badge officiel UP / DEGRADED / DOWN --}}
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase"
                                        :class="statusBadgeClass(partner.status)">
                                        <span class="h-2 w-2 rounded-full" :class="statusDotClass(partner.status)"></span>
                                        <span x-text="partner.status"></span>
                                    </span>
                                </div>

                                {{-- Détails qualitatifs des balances (sans montant) --}}
                                <div class="mt-4 space-y-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <template x-if="partner.balances.length === 0">
                                        <div
                                            class="flex items-center justify-between rounded-xl bg-error-50/50 px-3 py-2 text-xs text-error-700 dark:bg-error-950/20 dark:text-error-400">
                                            <span>Aucun service ou solde actif</span>
                                            <span class="font-semibold uppercase">DOWN</span>
                                        </div>
                                    </template>

                                    <template x-for="balance in partner.balances" :key="balance.service">
                                        <div
                                            class="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2 text-xs dark:bg-gray-800">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-semibold text-gray-700 dark:text-gray-200"
                                                    x-text="balance.service"></span>
                                                <span class="text-gray-400">•</span>
                                                <span class="text-gray-500 dark:text-gray-400"
                                                    x-text="balance.status_label"></span>
                                            </div>

                                            <span
                                                class="rounded px-2 py-0.5 text-2xs font-semibold uppercase tracking-wider"
                                                :class="balanceBadgeClass(balance.status_balance)"
                                                x-text="balance.status_balance"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Conseil opérationnel Support --}}
                            <div class="mt-4 border-t border-gray-100 pt-3 text-2xs dark:border-gray-800">
                                <span class="text-gray-500 dark:text-gray-400"
                                    x-text="operationalHint(partner.status)"></span>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- Message aucun résultat si filtre vide --}}
                <template x-if="filteredPartners.length === 0">
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-800 dark:text-white">Aucun service correspondant
                        </h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Essayez de modifier votre recherche ou le
                            filtre de statut sélectionné.</p>
                    </div>
                </template>
            </div>

        </div>

        {{-- ============================================================
         NOTE DE CONFIDENTIALITÉ & RÔLE SUPPORT
    ============================================================= --}}
        <div
            class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                    Disponibilité opérationnelle uniquement
                </p>
                <p class="mt-0.5 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                    Cette vue présente uniquement la disponibilité technique des partenaires (UP, DOWN, DEGRADED) pour
                    assister le diagnostic opérationnel du Support.
                    Aucun montant chiffré de solde ni balance confidentielle n'est affiché.
                </p>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function partnerServicesApp(initialData) {
                return {
                    data: initialData,
                    searchQuery: '',
                    selectedTab: 'ALL',
                    isLoading: false,

                    countByStatus(status) {
                        return this.data.partners.filter(p => p.status === status).length;
                    },

                    get filteredPartners() {
                        const query = this.searchQuery.toLowerCase().trim();
                        return this.data.partners.filter(partner => {
                            const matchTab = this.selectedTab === 'ALL' || partner.status === this.selectedTab;
                            const matchSearch = !query ||
                                partner.nom.toLowerCase().includes(query) ||
                                partner.services_list.toLowerCase().includes(query);
                            return matchTab && matchSearch;
                        });
                    },

                    statusBadgeClass(status) {
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

                    statusDotClass(status) {
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

                    partnerCardBorderClass(status) {
                        switch (status) {
                            case 'UP':
                                return 'border-gray-200 bg-white hover:border-success-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-success-500/40';
                            case 'DEGRADED':
                                return 'border-gray-200 bg-warning-50/20 hover:border-warning-400 dark:border-gray-800 dark:bg-warning-950/10 dark:hover:border-warning-600';
                            case 'DOWN':
                                return 'border-gray-200 bg-error-50/20 hover:border-error-400 dark:border-gray-800 dark:bg-error-950/10 dark:hover:border-error-600';
                            default:
                                return 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
                        }
                    },

                    balanceBadgeClass(rawStatus) {
                        switch (rawStatus) {
                            case 'NORMAL':
                                return 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-gray-300';
                            case 'MEDIUM':
                                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-gray-300';
                            case 'LOW':
                                return 'bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-gray-300';
                            case 'EMPTY':
                            case 'DOWN':
                            case 'CRITICAL':
                                return 'bg-error-100 text-error-800 dark:bg-error-900/40 dark:text-error-300';
                            default:
                                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                        }
                    },

                    operationalHint(status) {
                        switch (status) {
                            case 'UP':
                                return 'Service opérationnel pour les transactions.';
                            case 'DEGRADED':
                                return 'Alerte solde bas : risques de ralentissement ou refus sur les retraits.';
                            case 'DOWN':
                                return 'Service indisponible : suspendre les requêtes vers ce partenaire.';
                            default:
                                return 'Vérification requise.';
                        }
                    },

                    async refreshData() {
                        this.isLoading = true;
                        try {
                            const response = await fetch('{{ route('support.services.status') }}?refresh=1', {
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
                            console.error('Erreur rafraîchissement services:', err);
                        } finally {
                            this.isLoading = false;
                        }
                    }
                };
            }
        </script>
    @endpush

@endsection
