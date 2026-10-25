@extends('layouts.app')

@section('title', 'Recherche de Transactions - Support')

@section('content')

    @php
        $initialQuery = $initialQuery ?? '';
        $initialType = $initialType ?? 'external_id';
        $initialResult = $initialResult ?? null;
        $customers = $customers ?? [];
        $customersError = $customersError ?? null;
    @endphp

    <div x-data="transactionSearchApp({
        initialQuery: @js($initialQuery),
        initialType: @js($initialType),
        initialResult: @js($initialResult),
        searchUrl: '{{ route('support.transactions.search') }}',
        customers: @js($customers),
        customersError: @js($customersError),
        customerSearchUrl: '{{ route('support.transactions.customer-search') }}'
    })" class="space-y-6">

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
                                Retrouvez instantanément le statut, le montant et les informations d'une transaction client
                                via son identifiant (<span
                                    class="font-mono text-xs text-gray-600 dark:text-gray-300">external_id</span> ou <span
                                    class="font-mono text-xs text-gray-600 dark:text-gray-300">internal_id</span>).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
         BARRE DE RECHERCHE ET SÉLECTEURS
    ============================================================= --}}
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
                            ?
                            'bg-brand-500 text-white shadow-theme-xs' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                        class="rounded-xl px-4 py-2 text-xs font-semibold transition">
                        ID Externe (Partenaire)
                    </button>

                    <button type="button" @click="searchType = 'internal_id'"
                        :class="searchType === 'internal_id'
                            ?
                            'bg-brand-500 text-white shadow-theme-xs' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                        class="rounded-xl px-4 py-2 text-xs font-semibold transition">
                        ID Interne (Système)
                    </button>

                    <button type="button" @click="searchType = 'auto'"
                        :class="searchType === 'auto'
                            ?
                            'bg-brand-500 text-white shadow-theme-xs' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
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
                            placeholder="Saisissez ou collez l'identifiant de transaction (ex: 916c0a0f-939c-4fc8-93c1-89f25c675718)..."
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


        {{-- ============================================================
     RECHERCHE PAR CLIENT ET PÉRIODE
============================================================= --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-7">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                    Recherche par client et période
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Consultez les transactions d'un client sur une période donnée.
                </p>
            </div>

            <form @submit.prevent="executeCustomerSearch()" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    {{-- Sélection du client --}}
                    <div>
                        <label for="customer_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Client
                        </label>

                        <select id="customer_id" x-model="customerId" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            :disabled="customers.length === 0 || isCustomerLoading">
                            <option value="">Sélectionner un client</option>
                            <template x-for="customer in customers" :key="customer.id">
                                <option :value="String(customer.id)"
                                    x-text="customer.entreprise ? customer.entreprise + ' (ID ' + customer.id + ')' : 'Client ' + customer.id">
                                </option>
                            </template>
                        </select>

                        <p x-show="customersError" x-text="customersError" class="mt-2 text-xs text-red-500"></p>

                        <p x-show="!customersError && customers.length === 0" class="mt-2 text-xs text-amber-600">
                            Aucun client disponible.
                        </p>
                    </div>

                    {{-- Date de début --}}
                    <div>
                        <label for="customer_start_date"
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Date de début
                        </label>

                        <div class="relative">
                            <input x-ref="customerStartDateInput" id="customer_start_date" type="text"
                                placeholder="JJ/MM/AAAA" autocomplete="off" x-model="customerStartDate" required
                                class="customer-date-picker dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />

                            <span @click="$refs.customerStartDateInput._flatpickr.open()"
                                class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Date de fin --}}
                    <div>
                        <label for="customer_end_date"
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Date de fin
                        </label>

                        <div class="relative">
                            <input x-ref="customerEndDateInput" id="customer_end_date" type="text"
                                placeholder="JJ/MM/AAAA" autocomplete="off" x-model="customerEndDate" required
                                class="customer-date-picker dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />

                            <span @click="$refs.customerEndDateInput._flatpickr.open()"
                                class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 2.80711 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Les dates sont transmises au format AAAA-MM-JJ.
                    </p>

                    <button type="submit"
                        :disabled="isCustomerLoading || !customerId || !customerStartDate || !customerEndDate ||
                            customerStartDate > customerEndDate"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg x-show="isCustomerLoading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>

                        <span x-text="isCustomerLoading ? 'Recherche en cours...' : 'Rechercher les transactions'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ============================================================
     RÉSULTATS DE LA RECHERCHE PAR CLIENT
============================================================= --}}
        <div x-show="customerSearchError || customerResult" x-cloak
            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            {{-- Message d'erreur --}}
            <div x-show="customerSearchError" x-cloak role="alert"
                class="rounded-xl bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300"
                x-text="customerSearchError"></div>

            {{-- Résultats --}}
            <template x-if="customerResult && !customerSearchError">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                        Résultats par client
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="customerResult.count ?? (customerResult.transactions || []).length"></span>
                        transaction(s) retournée(s).
                    </p>

                    <template x-if="customerResult.transactions && customerResult.transactions.length > 0">
                        <div class="mt-4 space-y-4">
                            <template x-for="(tr, index) in customerResult.transactions"
                                :key="tr.index ?? tr.external_id ?? index">
                                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800">
                                        <div>
                                            <span class="text-xs font-semibold uppercase text-gray-400">
                                                Statut
                                            </span>

                                            <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white"
                                                x-text="tr.badge?.label || tr.status || 'Non renseigné'"></p>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-xs font-semibold uppercase text-gray-400">
                                                Date
                                            </span>

                                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-200"
                                                x-text="tr.created_at_formate || tr.created_at || '—'"></p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Montant net</p>
                                            <p class="mt-1 font-bold text-gray-900 dark:text-white"
                                                x-text="tr.montant_formate || tr.amount || '—'"></p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Frais</p>
                                            <p class="mt-1 font-semibold text-gray-700 dark:text-gray-200"
                                                x-text="tr.frais_formate || tr.fees || '—'"></p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Client / Bénéficiaire</p>
                                            <p class="mt-1 text-sm text-gray-800 dark:text-white"
                                                x-text="tr.info_pay?.nom || tr.customer_name || '—'"></p>
                                            <p class="mt-1 break-all font-mono text-xs text-gray-500 dark:text-gray-400"
                                                x-text="tr.info_pay?.telephone_complet || '—'"></p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Référence externe</p>
                                            <p class="mt-1 break-all font-mono text-xs text-brand-600 dark:text-brand-400"
                                                x-text="tr.external_id || '—'"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <p x-show="!customerResult.transactions || customerResult.transactions.length === 0"
                        class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Aucune transaction retournée pour cette période.
                    </p>
                </div>
            </template>
        </div>


        {{-- ============================================================
         RÉSULTATS DE LA RECHERCHE
    ============================================================= --}}
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
                <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">Recherche de la transaction en
                    cours...</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Récupération des informations associées à cet
                    identifiant.</p>
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
                            x-text="result.query_type === 'external_id' ? 'ID Externe' : (result.query_type === 'internal_id' ? 'ID Interne' : 'Détecté')"></span>
                    </div>

                    {{-- Liste des cartes de transactions --}}
                    <template x-for="tr in result.transactions" :key="tr.index">
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
                                                :class="tr.badge.class">
                                                <span class="h-1.5 w-1.5 rounded-full" :class="tr.badge.dot"></span>
                                                <span x-text="tr.badge.label"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Horodatage --}}
                                <div class="text-right">
                                    <span class="text-3xs uppercase tracking-wider text-gray-400 font-semibold">Date de la
                                        transaction</span>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200"
                                        x-text="tr.created_at_formate"></p>
                                    <p class="text-3xs text-gray-400" x-text="tr.created_at_human"></p>
                                </div>
                            </div>

                            {{-- Corps principal de la carte --}}
                            <div class="p-6 sm:p-7">
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

                                    {{-- Montant net --}}
                                    <div
                                        class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                        <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Montant
                                            net</span>
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
                                        <span class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Client
                                            / Bénéficiaire</span>
                                        <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white"
                                            x-text="tr.info_pay.nom"></p>
                                        <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400"
                                            x-text="tr.info_pay.telephone_complet"></p>
                                    </div>

                                    {{-- Identifiant externe --}}
                                    <div
                                        class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                        <span
                                            class="text-3xs font-semibold uppercase tracking-wider text-gray-400">Référence</span>
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

    @push('scripts')
        <script defer src="bundle.js"></script>
        <script type="module"
            src="https://static.cloudflareinsights.com/beacon.min.js/v31edd6df95cf4e85bb4c19e7a9bdbcba1788362987495"
            integrity="sha512-iIg7k2xntmwu6/uSb5tpc/hySgZc4eoL31yB29W6tJFo2akwjPWcEqnCEdJvGexCL0KEQwVYv5BlowfhVz26hg=="
            data-cf-beacon='{"version":"2024.11.0","token":"79f5d289b5024f39982f1bcfdb90d63d","r":1,"spa":2}'
            crossorigin="anonymous"></script>
        <script>
            function transactionSearchApp(config) {
                return {
                    searchQuery: config.initialQuery || '',
                    searchType: config.initialType || 'external_id',
                    result: config.initialResult || null,
                    isLoading: false,
                    searchUrl: config.searchUrl,
                    // Recherche par client et période
                    customers: config.customers || [],
                    customersError: config.customersError || null,
                    customerSearchUrl: config.customerSearchUrl,

                    customerId: '',
                    customerStartDate: '',
                    customerEndDate: '',

                    isCustomerLoading: false,
                    customerResult: null,
                    customerSearchError: null,

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
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                        'content') || '',
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

                    async executeCustomerSearch() {
                        if (
                            !this.customerId ||
                            !this.customerStartDate ||
                            !this.customerEndDate ||
                            this.customerStartDate > this.customerEndDate
                        ) {
                            this.customerSearchError =
                                'Veuillez sélectionner un client et une période valide.';
                            this.customerResult = null;
                            return;
                        }

                        this.isCustomerLoading = true;
                        this.customerSearchError = null;
                        this.customerResult = null;

                        try {
                            const response = await fetch(this.customerSearchUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        ?.getAttribute('content') || '',
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
                                    throw new Error(
                                        'Les données saisies sont invalides. Vérifiez le client et les dates.'
                                    );
                                }

                                if (response.status === 419) {
                                    throw new Error(
                                        'Votre session a expiré. Rechargez la page puis réessayez.'
                                    );
                                }

                                throw new Error(
                                    data.message || 'Impossible de rechercher les transactions.'
                                );
                            }

                            this.customerResult = data;

                            if (!data.success) {
                                this.customerSearchError =
                                    data.message || 'La recherche n’a pas abouti.';
                            }
                        } catch (error) {
                            console.error('Erreur recherche par client :', error);

                            this.customerSearchError =
                                error.message ||
                                'Une erreur est survenue lors de la recherche.';
                        } finally {
                            this.isCustomerLoading = false;
                        }
                    },

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
