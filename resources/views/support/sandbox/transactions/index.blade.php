@extends('layouts.app')

@section('title', 'Transactions Sandbox - Support')

@section('content')

    <div x-data="sandboxTransactionListApp()" x-init="init()" class="space-y-6">

        {{-- EN-TÊTE --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
                    Transactions Sandbox
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Consultation et suivi des transactions de l'environnement Sandbox ManyPayCash.
                </p>
            </div>

            <button type="button" @click="loadTransactions()" :disabled="loading"
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            {{-- TOTAL API --}}
            <div class="rounded-xl border border-gray-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Total transactions API
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white"
                            x-text="formatNumber(apiTotal)"></p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nombre annoncé par ManyPayCash
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 7h16M4 12h16M4 17h16M7 4v16" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- TRANSACTIONS CHARGÉES --}}
            <div class="rounded-xl border border-gray-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Transactions affichées
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white"
                            x-text="transactions.length"></p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Résultats de la page actuelle
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-success-50 text-success-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12l4 4L19 6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- PAGE COURANTE --}}
            <div class="rounded-xl border border-gray-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Pagination API
                        </p>

                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            <span x-text="currentPage"></span>
                            <span class="text-lg text-gray-400">/</span>
                            <span class="text-lg" x-text="lastPage"></span>
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Page actuelle / dernière page
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 6h16M4 12h16M4 18h16M9 3l-5 3 5 3M15 15l5 3-5 3" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" />
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

        {{-- FILTRES --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="border-b border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                <h2 class="font-semibold text-gray-800 dark:text-white">
                    Recherche de transactions
                </h2>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Filtre les transactions par client, référence externe ou période.
                </p>
            </div>

            <form @submit.prevent="applyFilters()" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">

                {{-- ID CLIENT --}}
                <div>
                    <label for="transaction-customer"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Identifiant client
                    </label>

                    <input id="transaction-customer" type="number" min="1" x-model="filters.id_customer"
                        placeholder="Ex. : 19"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                {{-- RÉFÉRENCE EXTERNE --}}
                <div>
                    <label for="transaction-external"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Référence externe
                    </label>

                    <input id="transaction-external" type="text" x-model="filters.external_id"
                        placeholder="Saisir une référence..."
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                {{-- DATE DÉBUT --}}
                <div>
                    <label for="transaction-start" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date de début
                    </label>

                    <input id="transaction-start" type="date" x-model="filters.debut"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                {{-- DATE FIN --}}
                <div>
                    <label for="transaction-end" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date de fin
                    </label>

                    <input id="transaction-end" type="date" x-model="filters.fin"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                {{-- ACTIONS --}}
                <div class="flex flex-col gap-2 sm:col-span-2 lg:col-span-4 sm:flex-row sm:justify-end">
                    <button type="button" @click="resetFilters()" :disabled="loading"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Réinitialiser
                    </button>

                    <button type="submit" :disabled="loading"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="7" stroke-width="2" />
                            <path d="M16 16l4 4" stroke-width="2" stroke-linecap="round" />
                        </svg>

                        Rechercher
                    </button>
                </div>
            </form>
        </div>

        {{-- TABLEAU DES TRANSACTIONS --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            {{-- EN-TÊTE TABLEAU --}}
            <div
                class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-gray-900/70 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800 dark:text-white">
                        Liste des transactions
                    </h2>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Données récupérées directement depuis l'API ManyPayCash.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <label for="transaction-limit" class="text-xs font-medium text-gray-600 dark:text-gray-400">
                        Par page
                    </label>

                    <select id="transaction-limit" x-model.number="perPage" @change="changeLimit()"
                        class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            {{-- CHARGEMENT --}}
            <template x-if="loading">
                <div class="flex flex-col items-center justify-center gap-3 py-16">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-brand-500"></div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Récupération des transactions Sandbox...
                    </p>
                </div>
            </template>

            {{-- CONTENU --}}
            <div x-show="!loading" x-cloak>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px]">

                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Transaction
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Client
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Bénéficiaire
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Service
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Montant
                                </th>

                                <th
                                    class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Statut
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Date
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Détails
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                            <template x-for="transaction in transactions" :key="transaction.id">
                                <tr class="transition-colors hover:bg-brand-50/40 dark:hover:bg-white/5">

                                    {{-- ID TRANSACTION --}}
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="font-semibold text-gray-800 dark:text-white"
                                            x-text="'#' + (transaction.id ?? '—')"></span>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                            x-text="transaction.typeTransaction || 'Type non renseigné'"></p>
                                    </td>

                                    {{-- CLIENT --}}
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"
                                            x-text="'#' + (transaction.idCustomer ?? '—')"></span>
                                    </td>

                                    {{-- BÉNÉFICIAIRE --}}
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-300"
                                            x-text="beneficiaryName(transaction)"></p>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                            x-text="beneficiaryPhone(transaction)"></p>
                                    </td>

                                    {{-- SERVICE --}}
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                            x-text="transaction.codeService || '—'"></span>

                                        <p class="mt-1 text-xs uppercase text-gray-500 dark:text-gray-400"
                                            x-text="transaction.codePays || ''"></p>
                                    </td>

                                    {{-- MONTANT --}}
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white"
                                            x-text="formatAmount(transaction.montant, transaction.currencyCode)"></span>
                                    </td>

                                    {{-- STATUT --}}
                                    <td class="whitespace-nowrap px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="statusClass(transaction.etat)"
                                            x-text="transaction.etat || 'INCONNU'"></span>
                                    </td>

                                    {{-- DATE --}}
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="text-sm text-gray-700 dark:text-gray-300"
                                            x-text="formatDate(transaction.createdAt)"></span>
                                    </td>

                                    {{-- ACTION --}}
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <button type="button" @click="showDetails(transaction)"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                            Voir
                                        </button>
                                    </td>

                                </tr>
                            </template>

                            {{-- AUCUN RÉSULTAT --}}
                            <template x-if="!error && transactions.length === 0">
                                <tr>
                                    <td colspan="8" class="px-5 py-14 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor">
                                                <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                                                <path d="M16 16l4 4" stroke-width="1.5" stroke-linecap="round" />
                                            </svg>

                                            <p class="font-medium text-gray-700 dark:text-gray-300">
                                                Aucune transaction trouvée
                                            </p>

                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Modifiez vos filtres ou actualisez la liste.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION SERVEUR --}}
                <div
                    class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50/50 px-5 py-4 dark:border-gray-800 dark:bg-gray-900/40 sm:flex-row sm:items-center sm:justify-between">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Affichage de
                        <strong x-text="firstItem"></strong>
                        à
                        <strong x-text="lastItem"></strong>
                        sur
                        <strong x-text="formatNumber(apiTotal)"></strong>
                        transaction(s)
                    </p>

                    <div class="flex items-center gap-2">

                        <button type="button" @click="goToPage(currentPage - 1)" :disabled="loading || currentPage <= 1"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Précédent
                        </button>

                        <span class="whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            Page <strong x-text="currentPage"></strong>
                            /
                            <strong x-text="lastPage"></strong>
                        </span>

                        <button type="button" @click="goToPage(currentPage + 1)"
                            :disabled="loading || currentPage >= lastPage"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Suivant
                        </button>

                    </div>
                </div>

            </div>
        </div>


        {{-- MODALE DÉTAILS TRANSACTION --}}
        <div x-show="selectedTransaction !== null" x-cloak x-transition.opacity @keydown.escape.window="closeDetails()"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-950/70 p-2 backdrop-blur-sm sm:p-4">

            <div x-show="selectedTransaction !== null" x-transition @click.outside="closeDetails()"
                class="flex max-h-[calc(100dvh-1rem)] w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl shadow-gray-950/30 dark:border-gray-700 dark:bg-gray-900 sm:max-h-[calc(100dvh-2rem)]">

                {{-- EN-TÊTE FIXE --}}
                <div
                    class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-900 sm:px-7">

                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white sm:text-xl">
                            Détails de la transaction
                        </h3>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Consultation des informations retournées par ManyPayCash
                        </p>
                    </div>

                    <button type="button" @click="closeDetails()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-200 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Fermer la fenêtre">

                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                {{-- CORPS DÉFILANT --}}
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6">

                    <template x-if="selectedTransaction">
                        <div class="space-y-6">

                            {{-- RÉSUMÉ DE LA TRANSACTION --}}
                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white">
                                        Résumé de la transaction
                                    </h4>

                                    <span class="text-xs text-gray-500 dark:text-gray-400"
                                        x-text="'Transaction #' + selectedTransaction.id"></span>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

                                    <div class="detail-item">
                                        <p class="detail-label">Identifiant transaction</p>
                                        <p class="detail-value text-base" x-text="'#' + (selectedTransaction.id ?? '—')">
                                        </p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Statut</p>
                                        <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="statusClass(selectedTransaction.etat)"
                                            x-text="selectedTransaction.etat || 'INCONNU'"></span>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Montant</p>
                                        <p class="detail-value text-base"
                                            x-text="formatAmount(selectedTransaction.montant, selectedTransaction.currencyCode)">
                                        </p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Type de transaction</p>
                                        <p class="detail-value"
                                            x-text="selectedTransaction.typeTransaction || 'Non renseigné'"></p>
                                    </div>

                                </div>
                            </section>

                            {{-- INFORMATIONS GÉNÉRALES --}}
                            <section>
                                <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                                    Informations générales
                                </h4>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                                    <div class="detail-item">
                                        <p class="detail-label">Identifiant client</p>
                                        <p class="detail-value" x-text="selectedTransaction.idCustomer ?? '—'"></p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Identifiant compte</p>
                                        <p class="detail-value" x-text="selectedTransaction.idCompte ?? '—'"></p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Service</p>
                                        <p class="detail-value" x-text="selectedTransaction.codeService || '—'"></p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Code pays</p>
                                        <p class="detail-value" x-text="selectedTransaction.codePays || '—'"></p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Devise</p>
                                        <p class="detail-value" x-text="selectedTransaction.currencyCode || '—'"></p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Date de création</p>
                                        <p class="detail-value" x-text="formatDate(selectedTransaction.createdAt)"></p>
                                    </div>

                                    <div class="detail-item">
                                        <p class="detail-label">Dernière modification</p>
                                        <p class="detail-value" x-text="formatDate(selectedTransaction.updatedAt)"></p>
                                    </div>

                                </div>
                            </section>

                            {{-- BÉNÉFICIAIRE ET RÉFÉRENCES --}}
                            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                                {{-- BÉNÉFICIAIRE --}}
                                <section>
                                    <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                                        Informations du bénéficiaire
                                    </h4>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                                        <div class="detail-item">
                                            <p class="detail-label">Nom</p>
                                            <p class="detail-value" x-text="beneficiaryName(selectedTransaction)"></p>
                                        </div>

                                        <div class="detail-item">
                                            <p class="detail-label">Numéro de téléphone</p>
                                            <p class="detail-value" x-text="beneficiaryPhone(selectedTransaction)"></p>
                                        </div>

                                    </div>
                                </section>

                                {{-- RÉFÉRENCES TECHNIQUES --}}
                                <section>
                                    <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                                        Références techniques
                                    </h4>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                                        <div class="detail-item">
                                            <p class="detail-label">External ID</p>
                                            <p class="detail-value break-all"
                                                x-text="selectedTransaction.externalId || '—'"></p>
                                        </div>

                                        <div class="detail-item">
                                            <p class="detail-label">Internal ID</p>
                                            <p class="detail-value break-all"
                                                x-text="selectedTransaction.internalId || '—'"></p>
                                        </div>

                                    </div>
                                </section>

                            </div>

                            {{-- INFORMATIONS BRUTES INFOPAY --}}
                            <section>
                                <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                                    Données infoPay
                                </h4>

                                <pre class="max-h-48 overflow-auto rounded-xl border border-gray-200 bg-gray-50 p-4 text-xs leading-relaxed text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    x-text="formatInfoPay(selectedTransaction.infoPay)"></pre>
                            </section>

                        </div>
                    </template>

                </div>

                {{-- PIED FIXE --}}
                <div
                    class="flex shrink-0 items-center justify-between border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-900 sm:px-7">

                    <p class="hidden text-xs text-gray-500 dark:text-gray-400 sm:block">
                        Informations issues de l'environnement Sandbox ManyPayCash.
                    </p>

                    <button type="button" @click="closeDetails()"
                        class="w-full rounded-xl bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-500/20 sm:w-auto">
                        Fermer
                    </button>
                </div>

            </div>
        </div>


    </div>

    {{-- STYLES LOCAUX DE LA MODALE --}}
    <style>
        .detail-item {
            min-width: 0;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            background: rgb(249 250 251);
            padding: 1rem;
        }

        .dark .detail-item {
            border-color: rgb(55 65 81);
            background: rgb(31 41 55);
        }

        .detail-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgb(107 114 128);
        }

        .detail-value {
            margin-top: 0.4rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(31 41 55);
            overflow-wrap: anywhere;
        }

        .dark .detail-value {
            color: rgb(229 231 235);
        }
    </style>

    <script>
        function sandboxTransactionListApp() {
            return {
                transactions: [],

                apiTotal: 0,
                currentPage: 1,
                lastPage: 1,
                perPage: 10,

                loading: false,
                error: null,

                selectedTransaction: null,

                requestId: 0,

                filters: {
                    id_customer: '',
                    external_id: '',
                    debut: '',
                    fin: ''
                },

                dataUrl: @json(route('support.sandbox.transactions.data')),

                get firstItem() {
                    if (this.transactions.length === 0) {
                        return 0;
                    }

                    return ((this.currentPage - 1) * this.perPage) + 1;
                },

                get lastItem() {
                    if (this.transactions.length === 0) {
                        return 0;
                    }

                    return this.firstItem + this.transactions.length - 1;
                },

                init() {
                    this.loadTransactions();
                },

                formatNumber(value) {
                    return new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));
                },

                formatAmount(amount, currency) {
                    const value = Number(amount ?? 0);

                    return `${new Intl.NumberFormat('fr-FR', {
                        maximumFractionDigits: 2
                    }).format(value)} ${currency || ''}`.trim();
                },

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

                parseInfoPay(value) {
                    if (!value) {
                        return {};
                    }

                    if (typeof value === 'object') {
                        return value;
                    }

                    try {
                        return JSON.parse(value);
                    } catch (exception) {
                        return {};
                    }
                },

                beneficiaryName(transaction) {
                    const info = this.parseInfoPay(transaction?.infoPay);

                    return info.nom || 'Non renseigné';
                },

                beneficiaryPhone(transaction) {
                    const info = this.parseInfoPay(transaction?.infoPay);

                    const phone = [
                        info.indicatif,
                        info.numero
                    ].filter(Boolean).join(' ');

                    return phone || 'Numéro non renseigné';
                },

                formatInfoPay(value) {
                    if (!value) {
                        return 'Aucune donnée disponible.';
                    }

                    const parsed = this.parseInfoPay(value);

                    if (Object.keys(parsed).length > 0) {
                        return JSON.stringify(parsed, null, 2);
                    }

                    return typeof value === 'string' ? value : JSON.stringify(value, null, 2);
                },

                statusClass(status) {
                    switch (String(status ?? '').toUpperCase()) {
                        case 'SUCCESS':
                            return 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400';

                        case 'FAILED':
                            return 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400';

                        case 'PENDING':
                            return 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400';

                        default:
                            return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                    }
                },

                async loadTransactions(page = this.currentPage) {
                    const requestId = ++this.requestId;

                    this.loading = true;
                    this.error = null;

                    try {
                        const params = new URLSearchParams();

                        params.set('page', String(page));
                        params.set('limit', String(this.perPage));

                        Object.entries(this.filters).forEach(([key, value]) => {
                            const normalized = String(value ?? '').trim();

                            if (normalized !== '') {
                                params.set(key, normalized);
                            }
                        });

                        const response = await fetch(
                            `${this.dataUrl}?${params.toString()}`, {
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
                                result.message || 'Erreur lors du chargement des transactions.'
                            );
                        }

                        if (!Array.isArray(result.data)) {
                            throw new Error('La structure de la réponse API est invalide.');
                        }

                        if (requestId !== this.requestId) {
                            return;
                        }

                        const meta = result.meta || {};

                        this.transactions = result.data;
                        this.apiTotal = Number(meta.total ?? result.data.length);
                        this.currentPage = Number(meta.currentPage ?? page);
                        this.lastPage = Math.max(1, Number(meta.lastPage ?? 1));

                    } catch (exception) {
                        console.error('Sandbox transactions:', exception);

                        if (requestId === this.requestId) {
                            this.error = exception.message ||
                                'Une erreur est survenue pendant le chargement des transactions.';

                            this.transactions = [];
                            this.apiTotal = 0;
                            this.currentPage = 1;
                            this.lastPage = 1;
                        }

                    } finally {
                        if (requestId === this.requestId) {
                            this.loading = false;
                        }
                    }
                },

                applyFilters() {
                    this.loadTransactions(1);
                },

                resetFilters() {
                    this.filters = {
                        id_customer: '',
                        external_id: '',
                        debut: '',
                        fin: ''
                    };

                    this.loadTransactions(1);
                },

                changeLimit() {
                    this.loadTransactions(1);
                },

                goToPage(page) {
                    const target = Number(page);

                    if (
                        this.loading ||
                        target < 1 ||
                        target > this.lastPage ||
                        target === this.currentPage
                    ) {
                        return;
                    }

                    this.loadTransactions(target);
                },

                showDetails(transaction) {
                    this.selectedTransaction = transaction;
                },

                closeDetails() {
                    this.selectedTransaction = null;
                }
            };
        }
    </script>

@endsection
