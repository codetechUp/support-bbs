@extends('layouts.app')

@section('title', 'Dashboard Support')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DONNÉES MOCKÉES — TEMPORAIRES
    |--------------------------------------------------------------------------
    | Ces données seront remplacées plus tard par les réponses des endpoints
    | de monitoring (/health, /health/payout, etc.).
    |
    | La structure est volontairement proche de ce que nous utiliserons
    | lorsque le backend sera disponible.
    */

    $services = [
        [
            'name' => 'API principale',
            'description' => 'API Core',
            'status' => 'UP',
            'latency' => '124 ms',
            'checked' => '12 sec',
        ],
        [
            'name' => 'Collection',
            'description' => 'Service Collection',
            'status' => 'UP',
            'latency' => '98 ms',
            'checked' => '10 sec',
        ],
        [
            'name' => 'Payout',
            'description' => 'Service Payout',
            'status' => 'DEGRADED',
            'latency' => '842 ms',
            'checked' => '8 sec',
        ],
        [
            'name' => 'Notifications',
            'description' => 'Service Notifications',
            'status' => 'UP',
            'latency' => '96 ms',
            'checked' => '15 sec',
        ],
    ];

    $recentTransactions = [
        [
            'id' => 'TXN-20261007-00142',
            'partner_id' => 'PARTNER-001',
            'type' => 'Collection',
            'status' => 'SUCCESS',
            'created_at' => '14:32',
        ],
        [
            'id' => 'TXN-20261007-00141',
            'partner_id' => 'PARTNER-004',
            'type' => 'Payout',
            'status' => 'PENDING',
            'created_at' => '14:29',
        ],
        [
            'id' => 'TXN-20261007-00140',
            'partner_id' => 'PARTNER-002',
            'type' => 'Transfer',
            'status' => 'FAILED',
            'created_at' => '14:24',
        ],
        [
            'id' => 'TXN-20261007-00139',
            'partner_id' => 'PARTNER-003',
            'type' => 'Payout',
            'status' => 'TIMEOUT',
            'created_at' => '14:18',
        ],
    ];

    $upCount = collect($services)->where('status', 'UP')->count();
    $degradedCount = collect($services)->where('status', 'DEGRADED')->count();
    $downCount = collect($services)->where('status', 'DOWN')->count();
    $pendingCount = collect($recentTransactions)->where('status', 'PENDING')->count();

    $serviceStatusClasses = [
        'UP' => [
            'badge' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            'icon' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
            'dot' => 'bg-success-500',
        ],
        'DEGRADED' => [
            'badge' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'icon' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
            'dot' => 'bg-warning-500',
        ],
        'DOWN' => [
            'badge' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
            'icon' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400',
            'dot' => 'bg-error-500',
        ],
    ];

    $transactionStatusClasses = [
        'SUCCESS' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
        'FAILED' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
        'TIMEOUT' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        'PENDING' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400',
    ];
@endphp

<div class="space-y-6">

    {{-- ============================================================
         HEADER
    ============================================================= --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
        <div class="px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-success-500"></span>

                        <span class="text-theme-sm font-medium text-success-600 dark:text-success-400">
                            Espace Support
                        </span>
                    </div>

                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                        Bonjour, {{ $user->name }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-theme-sm text-gray-500 dark:text-gray-400">
                        Vue opérationnelle de la plateforme et état des services.
                    </p>
                </div>

                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-lg font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>

            </div>
        </div>
    </div>


    {{-- ============================================================
         RÉSUMÉ OPÉRATIONNEL
    ============================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Services --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm font-medium text-gray-500 dark:text-gray-400">
                        Services opérationnels
                    </p>

                    <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $upCount }} / {{ count($services) }}
                    </h3>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/>
                    </svg>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                <span class="text-gray-500 dark:text-gray-400">
                    {{ $upCount }} disponible(s)
                </span>
            </div>
        </div>


        {{-- Payout --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm font-medium text-gray-500 dark:text-gray-400">
                        Disponibilité Payout
                    </p>

                    <h3 class="mt-2 text-xl font-semibold text-success-600 dark:text-success-400">
                        Disponible
                    </h3>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Statut opérationnel uniquement
            </p>
        </div>


        {{-- Transactions pending --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm font-medium text-gray-500 dark:text-gray-400">
                        Transactions en attente
                    </p>

                    <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $pendingCount }}
                    </h3>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Données opérationnelles
            </p>
        </div>


        {{-- Incidents --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm font-medium text-gray-500 dark:text-gray-400">
                        Services à surveiller
                    </p>

                    <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $degradedCount + $downCount }}
                    </h3>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl
                    {{ ($degradedCount + $downCount) > 0
                        ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400'
                        : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.8L2.7 18a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 4.8a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                {{ $degradedCount }} dégradé(s) · {{ $downCount }} indisponible(s)
            </p>
        </div>

    </div>


    {{-- ============================================================
         ÉTAT DES SERVICES
    ============================================================= --}}
    <div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                État des services
            </h2>

            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Surveillance de la disponibilité des services de la plateforme.
            </p>
        </div>


        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            @foreach($services as $service)

                @php
                    $status = $serviceStatusClasses[$service['status']] ?? $serviceStatusClasses['DOWN'];
                @endphp

                <div class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:shadow-theme-md dark:border-gray-800 dark:bg-white/3">

                    <div class="flex items-start justify-between gap-3">

                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $status['icon'] }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 17.25v1.5A2.25 2.25 0 0011.25 21h1.5A2.25 2.25 0 0015 18.75v-1.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7.5 10.5a4.5 4.5 0 019 0c0 2.25-.75 3-1.5 4.5h-6c-.75-1.5-1.5-2.25-1.5-4.5z"/>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <h3 class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ $service['name'] }}
                                </h3>

                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $service['description'] }}
                                </p>
                            </div>

                        </div>

                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['badge'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                            {{ $service['status'] }}
                        </span>

                    </div>


                    <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Latence
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ $service['latency'] }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Vérifié
                                </p>

                                <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    il y a {{ $service['checked'] }}
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>


    {{-- ============================================================
         PAYOUT + ÉTAT GLOBAL
    ============================================================= --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Payout --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">

                <div class="flex items-center justify-between">

                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Disponibilité Payout
                        </h2>

                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                            État opérationnel du service Payout.
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-2 rounded-full bg-success-50 px-3 py-1.5 text-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                        <span class="h-2 w-2 rounded-full bg-success-500"></span>
                        Disponible
                    </span>

                </div>

            </div>

            <div class="p-6">

                <div class="flex items-start gap-4 rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/20 dark:bg-success-500/10">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-success-800 dark:text-gray-300">
                            Les opérations Payout peuvent être traitées
                        </p>

                        <p class="mt-1 text-xs leading-5 text-success-700 dark:text-success-400">
                            Les conditions techniques nécessaires sont actuellement disponibles.
                            Aucune valeur de balance n'est exposée à l'agent Support.
                        </p>
                    </div>

                </div>

            </div>

        </div>


        {{-- État global --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">

                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    État global
                </h2>

                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                    Vue synthétique de l'environnement opérationnel.
                </p>

            </div>

            <div class="grid grid-cols-2 gap-4 p-6">

                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Services UP
                    </p>

                    <p class="mt-2 text-xl font-semibold text-success-600 dark:text-success-400">
                        {{ $upCount }}
                    </p>
                </div>

                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Dégradés
                    </p>

                    <p class="mt-2 text-xl font-semibold text-warning-600 dark:text-warning-400">
                        {{ $degradedCount }}
                    </p>
                </div>

                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Indisponibles
                    </p>

                    <p class="mt-2 text-xl font-semibold text-error-600 dark:text-error-400">
                        {{ $downCount }}
                    </p>
                </div>

                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Payout
                    </p>

                    <p class="mt-2 text-xl font-semibold text-success-600 dark:text-success-400">
                        Disponible
                    </p>
                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         TRANSACTIONS RÉCENTES
    ============================================================= --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">

            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Transactions récentes
                </h2>

                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                    Aperçu des dernières opérations nécessitant éventuellement une vérification.
                </p>
            </div>

            <a
                href="#"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
            >
                Rechercher une transaction

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 6l6 6-6 6"/>
                </svg>
            </a>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">

                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Transaction ID
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Partner ID
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Type
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Statut
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Heure
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @foreach($recentTransactions as $transaction)

                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $transaction['id'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $transaction['partner_id'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $transaction['type'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $transactionStatusClasses[$transaction['status']] }}">
                                    {{ $transaction['status'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transaction['created_at'] }}
                                </span>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>


    {{-- ============================================================
         NOTE DE SÉCURITÉ
    ============================================================= --}}
    <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/3">

        <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="9"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01"/>
        </svg>

        <div>
            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                Vue Support limitée
            </p>

            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                Cette interface expose uniquement les informations nécessaires au diagnostic
                opérationnel. Les balances clients, leurs montants ainsi que les KPI financiers
                globaux ne sont pas accessibles depuis l'espace Support.
            </p>
        </div>

    </div>

</div>

@endsection
