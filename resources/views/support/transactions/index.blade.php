@extends('layouts.app')

@section('title', 'Recherche de Transactions - Support')

@section('content')

@php
    $initialQuery = $initialQuery ?? '';
    $initialType = $initialType ?? 'external_id';
    $initialResult = $initialResult ?? null;
@endphp

<div
    x-data="transactionSearchApp({
        initialQuery: @js($initialQuery),
        initialType: @js($initialType),
        initialResult: @js($initialResult),
        searchUrl: '{{ route('support.transactions.search') }}'
    })"
    class="space-y-6"
>

    {{-- ============================================================
         EN-TÊTE : RECHERCHE DE TRANSACTIONS
    ============================================================= --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">
                            Recherche de Transactions
                        </h1>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            Retrouvez instantanément le statut, le montant et les informations d'une transaction client via son identifiant (<span class="font-mono text-xs text-gray-600 dark:text-gray-300">external_id</span> ou <span class="font-mono text-xs text-gray-600 dark:text-gray-300">internal_id</span>).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         BARRE DE RECHERCHE ET SÉLECTEURS
    ============================================================= --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-7">
        <form @submit.prevent="executeSearch()" class="space-y-4">

            {{-- Type d'identifiant --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mr-2">Rechercher par :</span>

                <button
                    type="button"
                    @click="searchType = 'external_id'"
                    :class="searchType === 'external_id'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="rounded-xl px-4 py-2 text-xs font-semibold transition"
                >
                    ID Externe (Partenaire)
                </button>

                <button
                    type="button"
                    @click="searchType = 'internal_id'"
                    :class="searchType === 'internal_id'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="rounded-xl px-4 py-2 text-xs font-semibold transition"
                >
                    ID Interne (Système)
                </button>

                <button
                    type="button"
                    @click="searchType = 'auto'"
                    :class="searchType === 'auto'
                        ? 'bg-brand-500 text-white shadow-theme-xs'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="rounded-xl px-4 py-2 text-xs font-semibold transition"
                >
                    Recherche Automatique
                </button>
            </div>

            {{-- Champ de saisie & bouton d'action --}}
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Saisissez ou collez l'identifiant de transaction (ex: 916c0a0f-939c-4fc8-93c1-89f25c675718)..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-11 pr-10 font-mono text-sm text-gray-800 shadow-theme-xs transition placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                        required
                    />

                    {{-- Bouton effacer --}}
                    <button
                        type="button"
                        x-show="searchQuery.length > 0"
                        @click="searchQuery = ''; result = null;"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading || searchQuery.trim() === ''"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg
                        x-show="isLoading"
                        class="h-4 w-4 animate-spin text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="isLoading ? 'Recherche en cours...' : 'Rechercher la transaction'"></span>
                </button>
            </div>

            {{-- Raccourci exemple --}}
            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs text-gray-500 dark:text-gray-400">
                <span>Exemple d'identifiant :</span>
                <button
                    type="button"
                    @click="setQuickQuery('916c0a0f-939c-4fc8-93c1-89f25c675718', 'external_id')"
                    class="inline-flex items-center gap-1 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-2.5 py-1 font-mono text-2xs text-gray-700 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <span>916c0a0f-939c-4fc8-93c1-89f25c675718</span>
                </button>
            </div>

        </form>
    </div>

    {{-- ============================================================
         RÉSULTATS DE LA RECHERCHE
    ============================================================= --}}
    <div>

        {{-- Chargement en cours --}}
        <div x-show="isLoading" class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">Recherche de la transaction en cours...</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Récupération des informations associées à cet identifiant.</p>
        </div>

        {{-- Résultats trouvés --}}
        <template x-if="!isLoading && result && result.success && result.transactions && result.transactions.length > 0">
            <div class="space-y-4">

                {{-- En-tête du résultat --}}
                <div class="flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-3 w-3 rounded-full bg-success-500 ring-4 ring-success-500/20"></span>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white">
                            <span x-text="result.count"></span> transaction(s) trouvée(s) pour la référence :
                            <span class="font-mono text-brand-600 dark:text-brand-400" x-text="result.query_id"></span>
                        </p>
                    </div>

                    <span
                        class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        x-text="result.query_type === 'external_id' ? 'ID Externe' : (result.query_type === 'internal_id' ? 'ID Interne' : 'Détecté')"
                    ></span>
                </div>

                {{-- Liste des cartes de transactions --}}
                <template x-for="tr in result.transactions" :key="tr.index">
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:shadow-theme-md dark:border-gray-800 dark:bg-gray-900">

                        {{-- Bandeau supérieur de la transaction --}}
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-800/40">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-theme-xs dark:bg-gray-800">
                                    <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-3xs uppercase tracking-wider text-gray-400 font-semibold">Statut</span>
                                    <div class="mt-0.5 flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider"
                                            :class="tr.badge.class"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full" :class="tr.badge.dot"></span>
                                            <span x-text="tr.badge.label"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Horodatage --}}
                            <div class="text-right">
                                <span class="text-3xs uppercase tracking-wider text-gray-400 font-semibold">Date de la transaction</span>
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="tr.created_at_formate"></p>
                                <p class="text-3xs text-gray-400" x-text="tr.created_at_human"></p>
                            </div>
                        </div>

                        {{-- Corps principal de la carte --}}
                        <div class="p-6 sm:p-7">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

                                {{-- Montant net --}}
                                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Montant net</span>
                                    <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white" x-text="tr.montant_formate"></p>
                                </div>

                                {{-- Frais --}}
                                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Frais</span>
                                    <p class="mt-1 text-xl font-bold text-gray-700 dark:text-gray-200" x-text="tr.frais_formate"></p>
                                </div>

                                {{-- Bénéficiaire / Client --}}
                                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Client / Bénéficiaire</span>
                                    <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white" x-text="tr.info_pay.nom"></p>
                                    <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400" x-text="tr.info_pay.telephone_complet"></p>
                                </div>

                                {{-- Identifiant externe --}}
                                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Référence</span>
                                    <p class="mt-1 font-mono text-xs font-semibold text-brand-600 break-all dark:text-brand-400" x-text="tr.external_id || result.query_id"></p>
                                </div>

                            </div>
                        </div>

                    </div>
                </template>

            </div>
        </template>

        {{-- Aucun résultat trouvé --}}
        <template x-if="!isLoading && result && (!result.transactions || result.transactions.length === 0)">
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-gray-800 dark:text-white">Aucune transaction trouvée</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="result.message || 'Aucune transaction ne correspond à cet identifiant.'"></p>
                <div class="mt-4">
                    <p class="text-xs text-gray-400">
                        Veuillez vérifier l'identifiant saisi ou tenter avec un autre mode de recherche.
                    </p>
                </div>
            </div>
        </template>

        {{-- État initial avant toute recherche --}}
        <template x-if="!isLoading && !result">
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
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

@push('scripts')
<script>
    function transactionSearchApp(config) {
        return {
            searchQuery: config.initialQuery || '',
            searchType: config.initialType || 'external_id',
            result: config.initialResult || null,
            isLoading: false,
            searchUrl: config.searchUrl,

            init() {
                if (this.searchQuery && !this.result) {
                    this.executeSearch();
                }
            },

            setQuickQuery(id, type) {
                this.searchQuery = id;
                this.searchType = type;
                this.executeSearch();
            },

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
            }
        };
    }
</script>
@endpush

@endsection
