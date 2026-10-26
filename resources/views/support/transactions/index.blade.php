@extends('layouts.app')

@section('title', 'Recherche de Transactions - Support')

@section('content')

    @php
        $initialQuery = $initialQuery ?? '';
        $initialType = $initialType ?? 'external_id';
        $initialResult = $initialResult ?? null;
        $initialCustomerId = $initialCustomerId ?? null;
        $customers = $customers ?? [];
        $customersError = $customersError ?? null;
    @endphp

    <div x-data="transactionSearchApp({
        initialQuery: @js($initialQuery),
        initialType: @js($initialType),
        initialResult: @js($initialResult),
        initialCustomerId: @js($initialCustomerId),
        searchUrl: '{{ route('support.transactions.search') }}',
        customers: @js($customers),
        customersError: @js($customersError),
        customerSearchUrl: '{{ route('support.transactions.customer-search') }}',
        verifyPartnerUrl: '{{ route('support.transactions.verify-partner') }}'
    })"
        x-init="$watch('customerStatusFilter', () => customerPage = 1);
                $watch('customerTextFilter', () => customerPage = 1);
                $watch('customerPerPage', () => customerPage = 1);"
        class="space-y-6">

        {{-- ============================================================
             EN-TÊTE : RECHERCHE DE TRANSACTIONS
        ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">
                                Recherche de Transactions
                            </h1>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                Recherchez instantanément par référence unique (<span
                                    class="font-mono text-xs text-gray-600 dark:text-gray-300">external_id</span> ou <span
                                    class="font-mono text-xs text-gray-600 dark:text-gray-300">internal_id</span>) ou explorez l'historique complet d'un client sur une période.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             SÉLECTEUR DE TYPE DE RECHERCHE (2 MODES DISTINCTS)
        ============================================================= --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">

                {{-- Mode 1 : Par Référence / Identifiant --}}
                <button type="button" @click="activeTab = 'reference'"
                    :class="activeTab === 'reference'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="flex items-center gap-3.5 rounded-xl px-5 py-3.5 text-left transition focus:outline-none">
                    <div
                        :class="activeTab === 'reference' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm">Recherche par Référence</div>
                        <div class="text-2xs opacity-85">ID Externe Partenaire ou ID Interne Système (recherche unitaire)</div>
                    </div>
                </button>

                {{-- Mode 2 : Par Client et Période --}}
                <button type="button" @click="activeTab = 'customer'"
                    :class="activeTab === 'customer'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="flex items-center gap-3.5 rounded-xl px-5 py-3.5 text-left transition focus:outline-none">
                    <div
                        :class="activeTab === 'customer' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm">Recherche par Client & Période</div>
                        <div class="text-2xs opacity-85">Historique des transactions d'un client avec autocomplétion</div>
                    </div>
                </button>

            </div>
        </div>

        {{-- ============================================================
             ONGLET 1 : RECHERCHE PAR RÉFÉRENCE (IDENTIFIANT)
        ============================================================= --}}
        <div x-show="activeTab === 'reference'" x-cloak class="space-y-6">

            {{-- Formulaire de recherche par identifiant --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                <form @submit.prevent="executeSearch()" class="space-y-4">

                    {{-- Type d'identifiant --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mr-2">Rechercher
                            par :</span>

                        <button type="button" @click="searchType = 'external_id'"
                            :class="searchType === 'external_id'
                                ? 'bg-brand-500 text-white shadow-theme-xs'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                            class="rounded-xl px-4 py-2 text-xs font-semibold transition">
                            ID Externe (Partenaire)
                        </button>

                        <button type="button" @click="searchType = 'internal_id'"
                            :class="searchType === 'internal_id'
                                ? 'bg-brand-500 text-white shadow-theme-xs'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                            class="rounded-xl px-4 py-2 text-xs font-semibold transition">
                            ID Interne (Système)
                        </button>

                        <button type="button" @click="searchType = 'auto'"
                            :class="searchType === 'auto'
                                ? 'bg-brand-500 text-white shadow-theme-xs'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                            class="rounded-xl px-4 py-2 text-xs font-semibold transition">
                            Recherche Automatique
                        </button>
                    </div>

                    {{-- Champ de saisie & bouton d'action --}}
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <input type="text" x-model="searchQuery"
                                placeholder="Saisissez ou collez l'identifiant (ex: 916c0a0f-939c-4fc8-93c1-89f25c675718 ou INTERNAL-123)..."
                                class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-11 pr-10 font-mono text-sm text-gray-800 shadow-theme-xs transition placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                                required />

                            {{-- Bouton effacer --}}
                            <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''; result = null;"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <button type="submit" :disabled="isLoading || searchQuery.trim() === ''"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg x-show="isLoading" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span x-text="isLoading ? 'Recherche en cours...' : 'Rechercher la transaction'"></span>
                        </button>
                    </div>

                    {{-- Raccourci exemple --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>Exemple d'identifiant :</span>
                        <button type="button" @click="setQuickQuery('916c0a0f-939c-4fc8-93c1-89f25c675718', 'external_id')"
                            class="inline-flex items-center gap-1 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-2.5 py-1 font-mono text-2xs text-gray-700 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <span>916c0a0f-939c-4fc8-93c1-89f25c675718</span>
                        </button>
                    </div>

                </form>
            </div>

            {{-- Résultats Recherche par Référence --}}
            <div>
                {{-- Chargement en cours --}}
                <div x-show="isLoading"
                    class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">Recherche de la transaction en cours...</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Interrogation de l'API ManyPayCash pour récupérer les détails de la transaction.</p>
                </div>

                {{-- Résultats trouvés --}}
                <template
                    x-if="!isLoading && result && result.success && result.transactions && result.transactions.length > 0">
                    <div class="space-y-4">

                        {{-- En-tête du résultat --}}
                        <div
                            class="flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-3 w-3 rounded-full bg-success-500 ring-4 ring-success-500/20"></span>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                    <span x-text="result.count"></span> transaction(s) trouvée(s) pour la référence :
                                    <span class="font-mono text-brand-600 dark:text-brand-400"
                                        x-text="result.query_id"></span>
                                </p>
                            </div>

                            <span
                                class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                x-text="result.query_type === 'external_id' ? 'ID Externe' : (result.query_type === 'internal_id' ? 'ID Interne' : 'Détection Automatique')"></span>
                        </div>

                        {{-- Liste des cartes de transactions --}}
                        <template x-for="tr in result.transactions" :key="tr.index ?? tr.external_id ?? Math.random()">
                            <div
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:shadow-theme-md dark:border-gray-800 dark:bg-gray-900">

                                {{-- Bandeau supérieur de la transaction --}}
                                <div
                                    class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-800/40">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-theme-xs dark:bg-gray-800">
                                            <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span
                                                class="text-3xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">Statut</span>
                                            <div class="mt-0.5 flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider"
                                                    :class="tr.badge?.class || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                                    <span class="h-1.5 w-1.5 rounded-full" :class="tr.badge?.dot || 'bg-gray-500'"></span>
                                                    <span x-text="tr.badge?.label || tr.etat || 'Inconnu'"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Horodatage & Actions (Détails & Vérifier Partenaire) --}}
                                    <div class="flex flex-wrap items-center gap-3 text-right">
                                        <div>
                                            <span class="text-3xs uppercase tracking-wider text-gray-400 font-semibold">Date de transaction</span>
                                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200"
                                                x-text="tr.created_at_formate || tr.created_at"></p>
                                            <p class="text-3xs text-gray-400" x-text="tr.created_at_human"></p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="verifyPartnerTransaction(tr)"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50/80 px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-theme-xs transition hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                                                <svg class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                <span>Vérifier chez partenaire</span>
                                            </button>
                                            <button type="button" @click="openDetailModal(tr)"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Détails</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Corps principal de la carte --}}
                                <div class="p-6 sm:p-7">
                                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

                                        {{-- Montant net --}}
                                        <div
                                            class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                            <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Montant net</span>
                                            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white"
                                                x-text="tr.montant_formate"></p>
                                        </div>

                                        {{-- Frais --}}
                                        <div
                                            class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                            <span
                                                class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Frais</span>
                                            <p class="mt-1 text-xl font-bold text-gray-700 dark:text-gray-200"
                                                x-text="tr.frais_formate"></p>
                                        </div>

                                        {{-- Bénéficiaire / Client --}}
                                        <div
                                            class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                            <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Client / Bénéficiaire</span>
                                            <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white"
                                                x-text="tr.info_pay?.nom || 'Non spécifié'"></p>
                                            <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400"
                                                x-text="tr.info_pay?.telephone_complet || 'Sans numéro'"></p>
                                        </div>

                                        {{-- Identifiant externe --}}
                                        <div
                                            class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                            <span
                                                class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Référence Externe</span>
                                            <p class="mt-1 font-mono text-xs font-semibold text-brand-600 break-all dark:text-brand-400"
                                                x-text="tr.external_id || result.query_id"></p>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </template>

                    </div>
                </template>

                {{-- Aucun résultat trouvé --}}
                <template x-if="!isLoading && result && (!result.transactions || result.transactions.length === 0)">
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-gray-800 dark:text-white">Aucune transaction trouvée</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            x-text="result.message || 'Aucune transaction ne correspond à cet identifiant.'"></p>
                        <div class="mt-4">
                            <p class="text-xs text-gray-400">
                                Veuillez vérifier l'identifiant saisi ou tenter avec un autre mode de recherche.
                            </p>
                        </div>
                    </div>
                </template>

                {{-- État initial avant toute recherche --}}
                <template x-if="!isLoading && !result">
                    <div
                        class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-gray-800 dark:text-white">Prêt pour la recherche</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Saisissez un identifiant pour afficher les informations détaillées de la transaction.
                        </p>
                    </div>
                </template>

            </div>

        </div>


        {{-- ============================================================
             ONGLET 2 : RECHERCHE PAR CLIENT ET PÉRIODE (AVEC AUTOCOMPLÉTION)
        ============================================================= --}}
        <div x-show="activeTab === 'customer'" x-cloak class="space-y-6">

            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-7">

                <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                            Recherche par Client et Période
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Sélectionnez un client dans la liste avec autocomplétion intelligente et définissez la plage de dates.
                        </p>
                    </div>

                    {{-- Raccourcis de dates rapides --}}
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-3xs uppercase tracking-wider font-semibold text-gray-400 mr-1">Période :</span>
                        <button type="button" @click="setPeriod('today')"
                            class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Aujourd'hui
                        </button>
                        <button type="button" @click="setPeriod('7days')"
                            class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            7 jours
                        </button>
                        <button type="button" @click="setPeriod('30days')"
                            class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            30 jours
                        </button>
                        <button type="button" @click="setPeriod('this_month')"
                            class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Mois en cours
                        </button>
                    </div>
                </div>

                <form @submit.prevent="executeCustomerSearch()" class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                        {{-- ============================================================
                             CHAMP AVEC AUTOCOMPLÉTION CLIENT
                        ============================================================= --}}
                        <div class="relative" @click.outside="customerDropdownOpen = false">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Client / Entreprise <span class="text-error-500">*</span>
                            </label>

                            {{-- Quand un client est sélectionné : afficher sa carte avec bouton changer --}}
                            <div x-show="selectedCustomer"
                                class="flex items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50/50 p-2.5 dark:border-brand-800/60 dark:bg-brand-500/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-500 font-bold text-white text-xs">
                                        <span x-text="(selectedCustomer?.entreprise || 'CL').substring(0, 2).toUpperCase()"></span>
                                    </div>
                                    <div class="min-w-0 truncate">
                                        <p class="truncate text-xs font-bold text-gray-900 dark:text-white"
                                            x-text="selectedCustomer?.entreprise || ('Client #' + selectedCustomer?.id)"></p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="rounded bg-brand-100 px-1.5 py-0.2 font-mono text-3xs font-semibold text-brand-800 dark:bg-brand-900/60 dark:text-brand-300"
                                                x-text="'ID #' + selectedCustomer?.id"></span>
                                            <span class="rounded bg-gray-100 px-1.5 py-0.2 text-3xs text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                                x-text="selectedCustomer?.env || 'Env'"></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="clearCustomer()"
                                    class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-brand-100 hover:text-gray-700 dark:hover:bg-brand-900/40 dark:hover:text-gray-200 transition"
                                    title="Changer de client">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Champ de recherche textuelle autocomplétée (si aucun sélectionné) --}}
                            <div x-show="!selectedCustomer" class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                <input type="text" x-model="customerSearchText"
                                    @focus="customerDropdownOpen = true"
                                    @input="customerDropdownOpen = true"
                                    @keydown.escape="customerDropdownOpen = false"
                                    placeholder="Rechercher par nom, entreprise ou ID..."
                                    autocomplete="off"
                                    class="h-11 w-full rounded-xl border border-gray-300 bg-white pl-10 pr-4 text-xs text-gray-800 placeholder:text-gray-400 shadow-theme-xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500" />

                                <div x-show="customerSearchText.length > 0" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button" @click="customerSearchText = ''; customerDropdownOpen = true;"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Menu déroulant flottant des résultats d'autocomplétion --}}
                            <div x-show="customerDropdownOpen && !selectedCustomer" x-cloak
                                class="absolute left-0 right-0 top-full z-50 mt-1 max-h-72 overflow-y-auto rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-700 dark:bg-gray-800">

                                <div class="px-2 py-1 text-3xs font-semibold uppercase tracking-wider text-gray-400 border-b border-gray-100 dark:border-gray-700/60 mb-1">
                                    <span x-text="filteredCustomers.length + ' client(s) disponible(s)'"></span>
                                </div>

                                <template x-for="c in filteredCustomers" :key="c.id">
                                    <div @click="selectCustomer(c)"
                                        class="flex cursor-pointer items-center justify-between gap-3 rounded-xl p-2 transition hover:bg-brand-50/80 dark:hover:bg-brand-500/10">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                <span x-text="(c.entreprise || 'CL').substring(0, 2).toUpperCase()"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-semibold text-gray-800 dark:text-white"
                                                    x-text="c.entreprise || ('Client #' + c.id)"></p>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="font-mono text-3xs text-gray-500 dark:text-gray-400"
                                                        x-text="'ID: ' + c.id"></span>
                                                    <span class="text-3xs"
                                                        :class="c.env === 'prod' || c.env === 'production' ? 'text-success-600 dark:text-success-400' : 'text-purple-600 dark:text-purple-400'"
                                                        x-text="c.env ? '(' + c.env + ')' : ''"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <span class="rounded-full px-2 py-0.5 text-3xs font-semibold"
                                            :class="c.etat === 'ACTIVE' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                            x-text="c.etat || 'ACTIF'"></span>
                                    </div>
                                </template>

                                <div x-show="filteredCustomers.length === 0" class="py-4 text-center text-xs text-gray-400">
                                    Aucun client ne correspond à votre recherche.
                                </div>
                            </div>

                            {{-- Champ caché pour la validation --}}
                            <input type="hidden" id="customer_id" x-model="customerId" required />

                            <p x-show="customersError" x-text="customersError" class="mt-1.5 text-xs text-red-500"></p>
                            <p x-show="!customersError && customers.length === 0" class="mt-1.5 text-xs text-amber-600">
                                Aucun client disponible.
                            </p>
                        </div>

                        {{-- Date de début --}}
                        <div>
                            <label for="customer_start_date"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Date de début <span class="text-error-500">*</span>
                            </label>

                            <div class="relative">
                                <input x-ref="customerStartDateInput" id="customer_start_date" type="text"
                                    placeholder="JJ/MM/AAAA" autocomplete="off" x-model="customerStartDate" required
                                    class="customer-date-picker h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-11 text-xs text-gray-800 shadow-theme-xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                                <span @click="$refs.customerStartDateInput._flatpickr.open()"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Date de fin --}}
                        <div>
                            <label for="customer_end_date"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Date de fin <span class="text-error-500">*</span>
                            </label>

                            <div class="relative">
                                <input x-ref="customerEndDateInput" id="customer_end_date" type="text"
                                    placeholder="JJ/MM/AAAA" autocomplete="off" x-model="customerEndDate" required
                                    class="customer-date-picker h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-11 text-xs text-gray-800 shadow-theme-xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                                <span @click="$refs.customerEndDateInput._flatpickr.open()"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Dates transmises au format standard AAAA-MM-JJ à l'API ManyPayCash.</span>
                        </div>

                        <button type="submit"
                            :disabled="isCustomerLoading || !customerId || !customerStartDate || !customerEndDate ||
                                customerStartDate > customerEndDate"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg x-show="isCustomerLoading" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>

                            <span x-text="isCustomerLoading ? 'Recherche des transactions...' : 'Rechercher les transactions'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Chargement en cours pour recherche client --}}
            <div x-show="isCustomerLoading"
                class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">Récupération des transactions du client...</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Chargement de l'historique sur la période sélectionnée.</p>
            </div>

            {{-- Erreur recherche client --}}
            <div x-show="customerSearchError && !isCustomerLoading" x-cloak role="alert"
                class="rounded-2xl border border-red-200 bg-red-50 p-6 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="customerSearchError"></span>
                </div>
            </div>

            {{-- ============================================================
                 RÉSULTATS DE LA RECHERCHE PAR CLIENT
            ============================================================= --}}
            <div x-show="customerResult && !customerSearchError && !isCustomerLoading" x-cloak class="space-y-6">

                {{-- Cartes de statistiques / Résumé --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    {{-- Total transactions --}}
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Total Transactions</span>
                        <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white"
                            x-text="customerSummaryStats.total"></p>
                    </div>

                    {{-- Volume cumulé --}}
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Volume Total</span>
                        <p class="mt-1 text-xl font-bold text-brand-600 dark:text-brand-400"
                            x-text="customerSummaryStats.amount"></p>
                    </div>

                    {{-- Succès --}}
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <span class="text-3xs font-semibold uppercase tracking-wider text-success-600 dark:text-success-400">Succès</span>
                        <p class="mt-1 text-2xl font-black text-success-600 dark:text-success-400"
                            x-text="customerSummaryStats.success"></p>
                    </div>

                    {{-- Échecs / Rejets --}}
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <span class="text-3xs font-semibold uppercase tracking-wider text-error-600 dark:text-error-400">Échecs</span>
                        <p class="mt-1 text-2xl font-black text-error-600 dark:text-error-400"
                            x-text="customerSummaryStats.failed"></p>
                    </div>
                </div>

                {{-- Barre de filtrage & recherche interne aux résultats --}}
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1 max-w-sm">
                            <input type="text" x-model="customerTextFilter"
                                placeholder="Filtrer par référence, nom, téléphone..."
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-3xs uppercase font-semibold tracking-wider text-gray-400">Statut :</label>
                                <select x-model="customerStatusFilter"
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    <option value="ALL">Tous les statuts</option>
                                    <option value="SUCCESS">Succès (SUCCESS)</option>
                                    <option value="FAILED">Échecs (FAILED)</option>
                                    <option value="PENDING">En attente (PENDING)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tableau des transactions du client --}}
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                            <thead>
                                <tr
                                    class="bg-gray-50/70 text-left text-2xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800/40 dark:text-gray-400">
                                    <th class="px-6 py-4">Date / Heure</th>
                                    <th class="px-6 py-4">Statut</th>
                                    <th class="px-6 py-4">Montant Net</th>
                                    <th class="px-6 py-4">Frais</th>
                                    <th class="px-6 py-4">Bénéficiaire / Téléphone</th>
                                    <th class="px-6 py-4">Réf. Externe</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="(tr, idx) in paginatedCustomerTransactions" :key="tr.index ?? tr.external_id ?? idx">
                                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/50">

                                        {{-- Date & Heure --}}
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <p class="text-xs font-semibold text-gray-800 dark:text-white"
                                                x-text="tr.created_at_formate || tr.created_at || '—'"></p>
                                            <p class="text-3xs text-gray-400" x-text="tr.created_at_human"></p>
                                        </td>

                                        {{-- Statut --}}
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide"
                                                :class="tr.badge?.class || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                                <span class="h-1.5 w-1.5 rounded-full" :class="tr.badge?.dot || 'bg-gray-400'"></span>
                                                <span x-text="tr.badge?.label || tr.etat || tr.status || 'Inconnu'"></span>
                                            </span>
                                        </td>

                                        {{-- Montant net --}}
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="font-bold text-gray-900 dark:text-white"
                                                x-text="tr.montant_formate || tr.amount || '0 FCFA'"></span>
                                        </td>

                                        {{-- Frais --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300">
                                            <span x-text="tr.frais_formate || tr.fees || '0 FCFA'"></span>
                                        </td>

                                        {{-- Bénéficiaire / info_pay --}}
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <p class="text-xs font-semibold text-gray-800 dark:text-white"
                                                x-text="tr.info_pay?.nom || tr.customer_name || 'Non spécifié'"></p>
                                            <p class="font-mono text-3xs text-gray-500 dark:text-gray-400"
                                                x-text="tr.info_pay?.telephone_complet || '—'"></p>
                                        </td>

                                        {{-- Référence externe --}}
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="font-mono text-xs font-semibold text-brand-600 dark:text-brand-400"
                                                x-text="tr.external_id || '—'"></span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" @click="verifyPartnerTransaction(tr)" title="Vérifier chez le partenaire"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50/80 px-2 py-1 text-xs font-semibold text-indigo-700 shadow-theme-xs transition hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                    <svg class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                    <span>Vérifier</span>
                                                </button>
                                                <button type="button" @click="openDetailModal(tr)"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <span>Voir</span>
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Aucun résultat dans la recherche client --}}
                    <div x-show="filteredCustomerTransactions.length === 0" class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h4 class="mt-2 text-sm font-semibold text-gray-800 dark:text-white">Aucune transaction trouvée</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Aucune transaction ne correspond à ce filtre pour la période demandée.</p>
                    </div>

                    {{-- Barre de pagination pour résultats client --}}
                    <div x-show="filteredCustomerTransactions.length > 0"
                        class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-6 py-4 sm:flex-row dark:border-gray-800">

                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>
                                Affichage de <strong class="text-gray-800 dark:text-white" x-text="customerStartIndex"></strong>
                                à <strong class="text-gray-800 dark:text-white" x-text="customerEndIndex"></strong>
                                sur <strong class="text-gray-800 dark:text-white" x-text="filteredCustomerTransactions.length"></strong> transaction(s)
                            </span>

                            <div class="flex items-center gap-1.5 border-l border-gray-200 pl-3 dark:border-gray-700">
                                <span class="text-2xs uppercase tracking-wider text-gray-400 font-semibold">Par page :</span>
                                <select x-model.number="customerPerPage"
                                    class="rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 transition focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    <option :value="10">10</option>
                                    <option :value="25">25</option>
                                    <option :value="50">50</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5" x-show="customerTotalPages > 1">
                            <button type="button" @click="if (customerPage > 1) customerPage--" :disabled="customerPage === 1"
                                class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Précédent</span>
                            </button>

                            <span class="px-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                                Page <span class="font-bold text-brand-600 dark:text-brand-400" x-text="customerPage"></span> sur <span x-text="customerTotalPages"></span>
                            </span>

                            <button type="button" @click="if (customerPage < customerTotalPages) customerPage++" :disabled="customerPage === customerTotalPages"
                                class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <span>Suivant</span>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- ============================================================
             MODAL DE DÉTAIL D'UNE TRANSACTION
        ============================================================= --}}
        <div x-show="isDetailModalOpen" x-cloak
            class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6 bg-gray-900/70 backdrop-blur-sm overflow-y-auto"
            @keydown.escape.window="closeDetailModal()">
            <div @click.outside="closeDetailModal()"
                class="relative w-full max-w-2xl my-auto max-h-[90vh] overflow-y-auto rounded-3xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-8">

                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800 dark:text-white">Détails de la transaction</h3>
                            <p class="font-mono text-xs text-brand-600 dark:text-brand-400"
                                x-text="selectedDetailTransaction?.external_id || selectedDetailTransaction?.internal_id || '—'"></p>
                        </div>
                    </div>

                    <button type="button" @click="closeDetailModal()"
                        class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Contenu détaillé --}}
                <div class="mt-6 space-y-5" x-if="selectedDetailTransaction">
                    {{-- Statut et Date --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                        <div>
                            <span class="text-3xs uppercase font-semibold tracking-wider text-gray-400">Statut de paiement</span>
                            <div class="mt-1">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider"
                                    :class="selectedDetailTransaction?.badge?.class || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="selectedDetailTransaction?.badge?.dot || 'bg-gray-500'"></span>
                                    <span x-text="selectedDetailTransaction?.badge?.label || selectedDetailTransaction?.etat || 'Inconnu'"></span>
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-3xs uppercase font-semibold tracking-wider text-gray-400">Date et Heure</span>
                            <p class="mt-1 text-xs font-semibold text-gray-800 dark:text-white"
                                x-text="selectedDetailTransaction?.created_at_formate || selectedDetailTransaction?.created_at"></p>
                        </div>
                    </div>

                    {{-- Grille montants & références --}}
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                            <span class="text-3xs uppercase font-semibold text-gray-400">Montant Net</span>
                            <p class="mt-1 font-bold text-gray-900 dark:text-white"
                                x-text="selectedDetailTransaction?.montant_formate || selectedDetailTransaction?.amount || '0 FCFA'"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                            <span class="text-3xs uppercase font-semibold text-gray-400">Frais</span>
                            <p class="mt-1 font-bold text-gray-700 dark:text-gray-300"
                                x-text="selectedDetailTransaction?.frais_formate || selectedDetailTransaction?.fees || '0 FCFA'"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                            <span class="text-3xs uppercase font-semibold text-gray-400">Total Débité</span>
                            <p class="mt-1 font-black text-brand-600 dark:text-brand-400"
                                x-text="selectedDetailTransaction?.total_formate || '—'"></p>
                        </div>
                    </div>

                    {{-- Infos client / bénéficiaire --}}
                    <div class="rounded-2xl border border-gray-100 p-4 dark:border-gray-800">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Informations du Destinataire</h4>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-xs">
                            <div>
                                <span class="text-gray-400">Nom complet :</span>
                                <p class="font-semibold text-gray-800 dark:text-white mt-0.5"
                                    x-text="selectedDetailTransaction?.info_pay?.nom || selectedDetailTransaction?.customer_name || 'Non renseigné'"></p>
                            </div>
                            <div>
                                <span class="text-gray-400">Téléphone :</span>
                                <p class="font-mono font-semibold text-gray-800 dark:text-white mt-0.5"
                                    x-text="selectedDetailTransaction?.info_pay?.telephone_complet || 'Non renseigné'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Identifiants techniques & Partenaire --}}
                    <div class="rounded-2xl border border-gray-100 p-4 dark:border-gray-800 text-xs space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Identifiants Techniques & Partenaire</h4>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                            <span class="text-gray-400">ID Externe :</span>
                            <span class="font-mono text-gray-700 dark:text-gray-200" x-text="selectedDetailTransaction?.external_id || '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                            <span class="text-gray-400">ID Interne :</span>
                            <span class="font-mono text-gray-700 dark:text-gray-200" x-text="selectedDetailTransaction?.internal_id || '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800" x-show="selectedDetailTransaction?.customer_id">
                            <span class="text-gray-400">ID Client (Customer ID) :</span>
                            <span class="font-mono text-gray-700 dark:text-gray-200" x-text="selectedDetailTransaction?.customer_id"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                            <span class="text-gray-400">Partenaire :</span>
                            <span class="font-semibold text-gray-800 dark:text-white" x-text="selectedDetailTransaction?.partner || selectedDetailTransaction?.partenaire || '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                            <span class="text-gray-400">ID Transaction Partenaire (id_partner) :</span>
                            <span class="font-mono text-indigo-600 dark:text-indigo-400 break-all" x-text="selectedDetailTransaction?.id_partner || selectedDetailTransaction?.id_tr_partenaire || selectedDetailTransaction?.partner_transaction_id || '—'"></span>
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" @click="verifyPartnerTransaction(selectedDetailTransaction)"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-xs font-semibold text-indigo-700 shadow-theme-xs transition hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                        <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Vérifier direct chez le partenaire</span>
                    </button>

                    <button type="button" @click="closeDetailModal()"
                        class="rounded-xl bg-gray-100 px-5 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Fermer
                    </button>
                </div>

            </div>
        </div>

        {{-- ============================================================
             MODAL : VÉRIFICATION DIRECTE CHEZ LE PARTENAIRE
        ============================================================= --}}
        {{-- ============================================================
             MODAL : VÉRIFICATION DIRECTE CHEZ LE PARTENAIRE
        ============================================================= --}}
        <div x-show="isPartnerModalOpen" x-cloak
            class="fixed inset-0 z-999999 flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm overflow-y-auto"
            style="display: flex; align-items: center; justify-content: center;"
            @keydown.escape.window="closePartnerModal()">
            <div class="relative w-full max-w-md mx-auto my-auto rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900 max-h-[85vh] overflow-y-auto"
                @click.outside="closePartnerModal()">

                {{-- Header du modal partenaire --}}
                <div class="flex items-start justify-between border-b border-gray-100 pb-3.5 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-gray-800 dark:text-white">Vérification Partenaire</h3>
                            <p class="text-3xs text-gray-400 font-mono">POST /searchTrByPartnerId</p>
                        </div>
                    </div>
                    <button type="button" @click="closePartnerModal()"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Corps du modal partenaire --}}
                <div class="mt-4 space-y-3.5">

                    {{-- Payload envoyé : partner & id_partner --}}
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 dark:border-indigo-900/40 dark:bg-indigo-950/20 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <span class="font-semibold uppercase tracking-wider text-3xs text-indigo-500">Partenaire</span>
                                <p class="mt-0.5 font-bold uppercase text-gray-800 dark:text-white text-xs" x-text="verifyingPartnerName || '—'"></p>
                            </div>
                            <div>
                                <span class="font-semibold uppercase tracking-wider text-3xs text-indigo-500">ID Partenaire</span>
                                <p class="mt-0.5 font-mono text-gray-800 dark:text-white break-all text-xs" x-text="verifyingPartnerId || '—'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Chargement en cours --}}
                    <div x-show="isPartnerVerifying" class="py-6 text-center space-y-2.5">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-800 dark:text-white">Interrogation du partenaire en cours...</p>
                        <p class="text-3xs text-gray-400">Veuillez patienter pendant la validation de la transaction.</p>
                    </div>

                    {{-- Erreur vérification --}}
                    <div x-show="partnerVerificationError && !isPartnerVerifying" x-cloak role="alert"
                        class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                        <div class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-2xs sm:text-xs" x-text="partnerVerificationError"></span>
                        </div>
                    </div>

                    {{-- Résultat avec succès --}}
                    <div x-show="partnerVerificationResult && partnerVerificationResult.success && !isPartnerVerifying" x-cloak class="space-y-3">
                        <div class="rounded-xl border border-success-200 bg-success-50/70 p-3 dark:border-success-900/40 dark:bg-success-900/20">
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-success-500"></span>
                                <h4 class="text-3xs font-bold uppercase tracking-wider text-success-800 dark:text-success-300">
                                    Réponse reçue du Partenaire
                                </h4>
                            </div>
                            <p class="mt-1 text-2xs font-medium text-success-700 dark:text-success-400"
                                x-text="partnerVerificationResult?.message || 'Transaction vérifiée avec succès.'"></p>
                        </div>
                    </div>

                    {{-- Données détaillées retournées avec affichage haute lisibilité --}}
                    <div x-show="partnerVerificationResult && partnerVerificationResult.data && !isPartnerVerifying" x-cloak class="rounded-xl border border-gray-200 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-800/40 text-xs">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-3xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Détails de la réponse brute :</span>
                            <button type="button"
                                @click="navigator.clipboard.writeText(JSON.stringify(partnerVerificationResult?.data, null, 2)); copiedPartnerData = true; setTimeout(() => copiedPartnerData = false, 2000)"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-3xs font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 transition shadow-2xs">
                                <svg x-show="!copiedPartnerData" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <svg x-show="copiedPartnerData" x-cloak class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="copiedPartnerData ? 'Copié !' : 'Copier JSON'"></span>
                            </button>
                        </div>

                        {{-- Synthèse rapide des propriétés clés --}}
                        <template x-if="typeof partnerVerificationResult?.data === 'object' && partnerVerificationResult?.data !== null && !Array.isArray(partnerVerificationResult?.data)">
                            <div class="mb-2.5 space-y-1.5 rounded-lg border border-gray-200/80 bg-white p-2.5 dark:border-gray-700/60 dark:bg-gray-900/60 text-xs">
                                <template x-if="partnerVerificationResult.data.payment_status || partnerVerificationResult.data.status">
                                    <div class="flex items-center justify-between py-0.5 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-3xs font-medium text-gray-400">Statut paiement :</span>
                                        <span class="font-bold uppercase text-3xs px-2 py-0.5 rounded-full"
                                            :class="['succeeded', 'completed', 'success'].includes(String(partnerVerificationResult.data.payment_status || partnerVerificationResult.data.status).toLowerCase())
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                                : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'"
                                            x-text="partnerVerificationResult.data.payment_status || partnerVerificationResult.data.status"></span>
                                    </div>
                                </template>
                                <template x-if="partnerVerificationResult.data.transaction_id">
                                    <div class="flex items-center justify-between py-0.5 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-3xs font-medium text-gray-400">ID Transaction :</span>
                                        <span class="font-mono text-3xs font-semibold text-indigo-600 dark:text-indigo-400" x-text="partnerVerificationResult.data.transaction_id"></span>
                                    </div>
                                </template>
                                <template x-if="partnerVerificationResult.data.amount">
                                    <div class="flex items-center justify-between py-0.5">
                                        <span class="text-3xs font-medium text-gray-400">Montant :</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-100 text-3xs" x-text="(partnerVerificationResult.data.amount) + ' ' + (partnerVerificationResult.data.currency || 'XOF')"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Code JSON brut avec couleurs forcées haute lisibilité (fond sombre #0b0f19, texte ultra-clair #f8fafc) --}}
                        <pre class="max-h-52 overflow-x-auto rounded-lg p-3 font-mono text-2xs leading-relaxed shadow-inner"
                            style="background-color: #0b0f19 !important; color: #f8fafc !important; border: 1px solid #1e293b !important; white-space: pre-wrap !important; word-break: break-all !important;"
                            x-text="JSON.stringify(partnerVerificationResult?.data, null, 2)"></pre>
                    </div>

                </div>

                {{-- Footer du modal partenaire --}}
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="closePartnerModal()"
                        class="rounded-xl bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Fermer
                    </button>
                </div>

            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function transactionSearchApp(config) {
                return {
                    // Type d'onglet actif : 'reference' ou 'customer'
                    activeTab: (config.initialCustomerId || window.location.search.includes('id_customer')) ? 'customer' : (config.initialQuery ? 'reference' : 'reference'),

                    // État recherche par référence
                    searchQuery: config.initialQuery || '',
                    searchType: config.initialType || 'external_id',
                    result: config.initialResult || null,
                    isLoading: false,
                    searchUrl: config.searchUrl,

                    // État recherche par client & période
                    initialCustomerId: config.initialCustomerId || null,
                    customers: config.customers || [],
                    customersError: config.customersError || null,
                    customerSearchUrl: config.customerSearchUrl,

                    customerId: '',
                    selectedCustomer: null,
                    customerSearchText: '',
                    customerDropdownOpen: false,

                    customerStartDate: '',
                    customerEndDate: '',

                    isCustomerLoading: false,
                    customerResult: null,
                    customerSearchError: null,

                    // Filtres & Pagination des résultats client
                    customerStatusFilter: 'ALL',
                    customerTextFilter: '',
                    customerPage: 1,
                    customerPerPage: 10,

                    // Modal détail
                    selectedDetailTransaction: null,
                    isDetailModalOpen: false,

                    // Vérification directe chez le partenaire (POST /searchTrByPartnerId)
                    verifyPartnerUrl: config.verifyPartnerUrl,
                    isPartnerVerifying: false,
                    isPartnerModalOpen: false,
                    partnerVerificationResult: null,
                    partnerVerificationError: null,
                    verifyingPartnerName: '',
                    verifyingPartnerId: '',
                    copiedPartnerData: false,

                    init() {
                        if (this.initialCustomerId || window.location.search.includes('id_customer')) {
                            const urlParams = new URLSearchParams(window.location.search);
                            const targetId = this.initialCustomerId || urlParams.get('id_customer');
                            if (targetId) {
                                this.activeTab = 'customer';
                                this.customerId = String(targetId);
                                const found = this.customers.find(c => String(c.id) === String(targetId));
                                if (found) {
                                    this.selectedCustomer = found;
                                    this.customerSearchText = found.entreprise ? `${found.entreprise} (ID ${found.id})` : `Client #${found.id}`;
                                } else {
                                    this.customerSearchText = `Client #${targetId}`;
                                }
                                this.setPeriod('30days');
                                setTimeout(() => {
                                    this.executeCustomerSearch();
                                }, 150);
                            }
                        } else if (this.searchQuery && !this.result) {
                            this.executeSearch();
                        }
                    },

                    setQuickQuery(id, type) {
                        this.searchQuery = id;
                        this.searchType = type;
                        this.executeSearch();
                    },

                    // Autocomplétion client
                    get filteredCustomers() {
                        const q = this.customerSearchText.toLowerCase().trim();
                        if (!q) {
                            return this.customers.slice(0, 30);
                        }
                        return this.customers.filter(c => {
                            const entreprise = String(c.entreprise ?? '').toLowerCase();
                            const id = String(c.id ?? '').toLowerCase();
                            const env = String(c.env ?? '').toLowerCase();
                            return entreprise.includes(q) || id.includes(q) || env.includes(q);
                        }).slice(0, 50);
                    },

                    selectCustomer(customer) {
                        this.selectedCustomer = customer;
                        this.customerId = String(customer.id);
                        this.customerSearchText = customer.entreprise ? `${customer.entreprise} (ID ${customer.id})` : `Client #${customer.id}`;
                        this.customerDropdownOpen = false;
                    },

                    clearCustomer() {
                        this.selectedCustomer = null;
                        this.customerId = '';
                        this.customerSearchText = '';
                        this.customerDropdownOpen = false;
                    },

                    // Raccourcis de sélection de dates
                    setPeriod(preset) {
                        const today = new Date();
                        const pad = (n) => String(n).padStart(2, '0');
                        const formatYMD = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

                        let start = new Date(today);
                        let end = new Date(today);

                        if (preset === 'today') {
                            start = today;
                            end = today;
                        } else if (preset === '7days') {
                            start.setDate(today.getDate() - 7);
                        } else if (preset === '30days') {
                            start.setDate(today.getDate() - 30);
                        } else if (preset === 'this_month') {
                            start = new Date(today.getFullYear(), today.getMonth(), 1);
                        }

                        const startStr = formatYMD(start);
                        const endStr = formatYMD(end);

                        this.customerStartDate = startStr;
                        this.customerEndDate = endStr;

                        const startInput = document.getElementById('customer_start_date');
                        const endInput = document.getElementById('customer_end_date');
                        if (startInput && startInput._flatpickr) {
                            startInput._flatpickr.setDate(startStr);
                        }
                        if (endInput && endInput._flatpickr) {
                            endInput._flatpickr.setDate(endStr);
                        }
                    },

                    // Exécution recherche par référence
                    async executeSearch() {
                        const query = this.searchQuery.trim();
                        if (!query) return;

                        this.isLoading = true;

                        try {
                            const response = await fetch(this.searchUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                },
                                body: JSON.stringify({
                                    id: query,
                                    type: this.searchType,
                                })
                            });

                            const data = await response.json();
                            this.result = data;
                        } catch (err) {
                            console.error('Erreur recherche transaction:', err);
                            this.result = {
                                success: false,
                                count: 0,
                                transactions: [],
                                message: 'Une erreur est survenue lors de la recherche de la transaction.'
                            };
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    // Exécution recherche par client & période
                    async executeCustomerSearch() {
                        if (
                            !this.customerId ||
                            !this.customerStartDate ||
                            !this.customerEndDate ||
                            this.customerStartDate > this.customerEndDate
                        ) {
                            this.customerSearchError = 'Veuillez sélectionner un client et une période valide (Date début ≤ Date fin).';
                            this.customerResult = null;
                            return;
                        }

                        this.isCustomerLoading = true;
                        this.customerSearchError = null;
                        this.customerResult = null;
                        this.customerPage = 1;

                        try {
                            const response = await fetch(this.customerSearchUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                },
                                body: JSON.stringify({
                                    id_customer: Number(this.customerId),
                                    debut: this.customerStartDate,
                                    fin: this.customerEndDate,
                                }),
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422) {
                                    throw new Error('Données invalides. Vérifiez le client et le format des dates.');
                                }
                                if (response.status === 419) {
                                    throw new Error('Session expirée. Veuillez recharger la page.');
                                }
                                throw new Error(data.message || 'Impossible de récupérer les transactions.');
                            }

                            this.customerResult = data;

                            if (!data.success) {
                                this.customerSearchError = data.message || 'Aucune transaction retournée.';
                            }
                        } catch (error) {
                            console.error('Erreur recherche client:', error);
                            this.customerSearchError = error.message || 'Une erreur est survenue lors de la recherche.';
                        } finally {
                            this.isCustomerLoading = false;
                        }
                    },

                    // Getters pour les statistiques du client
                    get customerSummaryStats() {
                        if (!this.customerResult || !this.customerResult.transactions) {
                            return { total: 0, amount: '0 FCFA', success: 0, failed: 0, pending: 0 };
                        }
                        const list = this.customerResult.transactions;
                        let amount = 0;
                        let success = 0;
                        let failed = 0;
                        let pending = 0;
                        list.forEach(t => {
                            amount += Number(t.montant || 0);
                            const st = String(t.etat || t.status || '').toUpperCase();
                            if (st === 'SUCCESS' || st === 'REUSSI') success++;
                            else if (st === 'FAILED' || st === 'ECHEC') failed++;
                            else pending++;
                        });
                        return {
                            total: list.length,
                            amount: new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA',
                            success,
                            failed,
                            pending
                        };
                    },

                    // Filtrage et pagination des résultats clients
                    get filteredCustomerTransactions() {
                        if (!this.customerResult || !this.customerResult.transactions) return [];
                        let list = this.customerResult.transactions;

                        if (this.customerStatusFilter !== 'ALL') {
                            list = list.filter(t => {
                                const st = String(t.etat || t.status || '').toUpperCase();
                                return st === this.customerStatusFilter;
                            });
                        }

                        const q = this.customerTextFilter.toLowerCase().trim();
                        if (q) {
                            list = list.filter(t =>
                                String(t.external_id || '').toLowerCase().includes(q) ||
                                String(t.internal_id || '').toLowerCase().includes(q) ||
                                String(t.info_pay?.nom || '').toLowerCase().includes(q) ||
                                String(t.info_pay?.telephone_complet || '').toLowerCase().includes(q) ||
                                String(t.montant || '').includes(q)
                            );
                        }

                        return list;
                    },

                    get customerTotalPages() {
                        return Math.max(1, Math.ceil(this.filteredCustomerTransactions.length / this.customerPerPage));
                    },

                    get paginatedCustomerTransactions() {
                        const start = (this.customerPage - 1) * this.customerPerPage;
                        return this.filteredCustomerTransactions.slice(start, start + this.customerPerPage);
                    },

                    get customerStartIndex() {
                        if (this.filteredCustomerTransactions.length === 0) return 0;
                        return (this.customerPage - 1) * this.customerPerPage + 1;
                    },

                    get customerEndIndex() {
                        return Math.min(this.customerPage * this.customerPerPage, this.filteredCustomerTransactions.length);
                    },

                    // Modal détail
                    openDetailModal(transaction) {
                        this.selectedDetailTransaction = transaction;
                        this.isDetailModalOpen = true;
                    },

                    closeDetailModal() {
                        this.selectedDetailTransaction = null;
                        this.isDetailModalOpen = false;
                    },

                    // Vérification directe chez le partenaire
                    async verifyPartnerTransaction(tr) {
                        if (!tr) return;

                        // Priorité absolue au service_code comme demandé (ex: snwave au lieu de wave)
                        const serviceCode = String(tr.service_code || tr.codeService || tr.code_service || '').trim();
                        let partner = serviceCode;

                        if (!partner) {
                            partner = String(tr.partner || tr.partenaire || '').trim();
                            if (partner.toLowerCase() === 'wave') {
                                const country = String(tr.country_code || tr.codePays || 'sn').toLowerCase().trim();
                                partner = (country || 'sn') + 'wave';
                            }
                        }

                        const idPartner = String(
                            tr.id_partner ||
                            tr.id_tr_partenaire ||
                            tr.partner_transaction_id ||
                            ''
                        ).trim();

                        this.verifyingPartnerName = partner || 'Partenaire inconnu';
                        this.verifyingPartnerId = idPartner || 'Identifiant manquant';
                        this.partnerVerificationResult = null;
                        this.partnerVerificationError = null;
                        this.isPartnerModalOpen = true;

                        if (!partner || !idPartner) {
                            this.partnerVerificationError = "Impossible de lancer la vérification : le nom du partenaire (ou service_code) ou l'identifiant partenaire (id_partner / id_tr_partenaire) n'est pas renseigné pour cette transaction.";
                            return;
                        }

                        this.isPartnerVerifying = true;

                        try {
                            const response = await fetch(this.verifyPartnerUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                },
                                body: JSON.stringify({
                                    partner: partner,
                                    service_code: partner,
                                    id_partner: idPartner,
                                }),
                            });

                            const data = await response.json();

                            this.partnerVerificationResult = data;

                            if (!response.ok || !data.success) {
                                this.partnerVerificationError = data.message || `Échec de vérification chez le partenaire (Code HTTP ${response.status})`;
                            }
                        } catch (err) {
                            console.error('Erreur vérification partenaire:', err);
                            this.partnerVerificationError = err.message || 'Une erreur est survenue lors de l\'interrogation du partenaire.';
                        } finally {
                            this.isPartnerVerifying = false;
                        }
                    },

                    closePartnerModal() {
                        this.isPartnerModalOpen = false;
                        this.partnerVerificationResult = null;
                        this.partnerVerificationError = null;
                        this.verifyingPartnerName = '';
                        this.verifyingPartnerId = '';
                    }
                };
            }
        </script>

        <script>
            document.addEventListener('alpine:initialized', () => {
                const app = document.querySelector('[x-data]');
                if (!app || typeof flatpickr !== 'function') return;

                const startInput = document.getElementById('customer_start_date');
                const endInput = document.getElementById('customer_end_date');

                if (!startInput || !endInput) return;

                const getDate = (value) => value || null;

                const startPicker = flatpickr(startInput, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: false,
                    disableMobile: true,
                    maxDate: getDate(endInput.value),
                    onChange(selectedDates, dateStr) {
                        const data = Alpine.$data(app);
                        data.customerStartDate = dateStr;
                        endPicker.set('minDate', dateStr || null);
                    },
                });

                const endPicker = flatpickr(endInput, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: false,
                    disableMobile: true,
                    minDate: getDate(startInput.value),
                    onChange(selectedDates, dateStr) {
                        const data = Alpine.$data(app);
                        data.customerEndDate = dateStr;
                        startPicker.set('maxDate', dateStr || null);
                    },
                });

                startInput.addEventListener('change', () => {
                    Alpine.$data(app).customerStartDate = startInput.value;
                });

                endInput.addEventListener('change', () => {
                    Alpine.$data(app).customerEndDate = endInput.value;
                });
            });
        </script>
    @endpush

@endsection
