@extends('layouts.app')

@section('title', 'Utilisateurs du client Sandbox - Support')

@section('content')

    <div class="space-y-6" x-data="{
        showCredentialsModal: false,
        credentialsEmail: '',
        credentialsForm: null,

        openCredentialsModal(email, form) {
            this.credentialsEmail = email;
            this.credentialsForm = form;
            this.showCredentialsModal = true;
        },

        closeCredentialsModal() {
            this.showCredentialsModal = false;
            this.credentialsEmail = '';
            this.credentialsForm = null;
        },

        submitCredentialsForm() {
            if (this.credentialsForm) {
                this.credentialsForm.submit();
            }
        }
    }" x-on:keydown.escape.window="closeCredentialsModal()">

        {{-- En-tête --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-3">
                        <a href="{{ route('support.sandbox.customers.index') }}"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Retour aux clients Sandbox
                        </a>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Utilisateurs du client
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Consultation des utilisateurs associés au client
                        <span class="font-semibold text-brand-600 dark:text-brand-400">
                            #{{ $idCustomer }}
                        </span>
                        dans ManyPayCash Sandbox.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span
                        class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Client #{{ $idCustomer }}
                    </span>
                </div>
            </div>
        </div>


        {{-- Erreur API --}}
        @if ($error)
            <div class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-900/50 dark:bg-red-900/20">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                    </svg>

                    <div>
                        <p class="text-sm font-semibold text-red-700 dark:text-red-400">
                            Impossible de récupérer les utilisateurs.
                        </p>
                        <p class="mt-1 text-xs text-red-600 dark:text-red-300">
                            {{ $error }}
                        </p>

                        <a href="{{ url()->current() }}"
                            class="mt-3 inline-flex rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                            Réessayer
                        </a>
                    </div>
                </div>
            </div>
        @else
            {{-- Compteurs --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Identifiant du client
                    </p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                        #{{ $idCustomer }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Nombre d'utilisateurs associés
                    </p>
                    <p class="mt-2 text-2xl font-bold text-brand-600 dark:text-brand-400">
                        {{ count($users) }}
                    </p>
                </div>
            </div>

            {{-- Tableau des utilisateurs --}}
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

                <div
                    class="flex flex-col gap-2 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                            Liste des utilisateurs
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Comptes rattachés au client #{{ $idCustomer }}.
                        </p>
                    </div>

                    <span
                        class="w-fit rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                        {{ count($users) }} utilisateur(s)
                    </span>
                </div>

                @if (count($users) > 0)

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                            <thead>
                                <tr
                                    class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800/40 dark:text-gray-400">
                                    <th class="px-6 py-4">Utilisateur</th>
                                    <th class="px-6 py-4">ID utilisateur</th>
                                    <th class="px-6 py-4">ID client</th>
                                    <th class="px-6 py-4">Adresse email</th>
                                    <th class="px-6 py-4">Date de création</th>
                                    <th class="px-6 py-4">Dernière mise à jour</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($users as $user)
                                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/50">

                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold uppercase text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                                    {{ strtoupper(substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? '', 0, 1)) }}
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                        {{ trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: 'Nom non renseigné' }}
                                                    </p>
                                                    <p class="mt-0.5 text-xs text-gray-400">
                                                        Utilisateur Sandbox
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="rounded-lg bg-gray-100 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                #{{ $user['id'] ?? '—' }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="rounded-lg bg-gray-100 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                #{{ $user['idCustomer'] ?? $idCustomer }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $user['email'] ?? 'Non renseignée' }}
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300">
                                            {{ !empty($user['createdAt']) ? \Carbon\Carbon::parse($user['createdAt'])->format('d/m/Y H:i') : '—' }}
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-600 dark:text-gray-300">
                                            {{ !empty($user['updatedAt']) ? \Carbon\Carbon::parse($user['updatedAt'])->format('d/m/Y H:i') : '—' }}
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            @if (!empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL))
                                                <form method="POST"
                                                    action="{{ route('support.sandbox.users.send-credentials', ['email' => $user['email']]) }}">
                                                    @csrf

                                                    <button type="button"
                                                        x-on:click="openCredentialsModal(
            $event.currentTarget.dataset.email,
            $event.currentTarget.closest('form')
        )"
                                                        data-email="{{ $user['email'] }}"
                                                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                        </svg>

                                                        Envoyer les identifiants
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    E-mail indisponible
                                                </span>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- Aucun utilisateur --}}
                    <div class="px-6 py-16 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-semibold text-gray-800 dark:text-white">
                            Aucun utilisateur associé
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Ce client ne possède actuellement aucun utilisateur enregistré dans ManyPayCash Sandbox.
                        </p>
                    </div>

                @endif
            </div>

        @endif

        {{-- Modal de confirmation du renvoi des identifiants --}}
        <div x-show="showCredentialsModal" x-transition.opacity x-cloak
            class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm"
            x-on:click.self="closeCredentialsModal()" style="display: none;">
            <div x-show="showCredentialsModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
                x-on:click.stop>
                {{-- En-tête --}}
                <div class="flex items-start justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                Confirmer le renvoi
                            </h3>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Identifiants Sandbox
                            </p>
                        </div>
                    </div>

                    <button type="button" x-on:click="closeCredentialsModal()"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Fermer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Contenu --}}
                <div class="px-6 py-6">
                    <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Souhaitez-vous demander à ManyPayCash le renvoi des identifiants de cet utilisateur ?
                    </p>

                    <div
                        class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Destinataire
                        </p>

                        <p class="mt-1 break-all text-sm font-semibold text-gray-800 dark:text-white"
                            x-text="credentialsEmail"></p>
                    </div>

                    <p class="mt-4 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        La demande sera transmise à l'API ManyPayCash. La réception effective de l'e-mail reste à confirmer.
                    </p>
                </div>

                {{-- Actions --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end dark:border-gray-800 dark:bg-gray-900">
                    <button type="button" x-on:click="closeCredentialsModal()"
                        class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Annuler
                    </button>

                    <button type="button" x-on:click="submitCredentialsForm()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>

                        Confirmer le renvoi
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
