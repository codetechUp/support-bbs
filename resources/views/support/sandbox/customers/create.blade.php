@extends('layouts.app')

@section('title', 'Créer un client Sandbox - Support')

@section('content')
    <div class="space-y-6">

        {{-- En-tête --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="flex items-center gap-3 text-2xl font-bold text-gray-800 dark:text-white">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01M14 12v.01M14 15v.01M14 18v.01" />
                            </svg>
                        </span>
                        Créer un client Sandbox
                    </h1>

                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Enregistrer une entreprise et créer son utilisateur associé sur l'environnement de test ManyPayCash.
                    </p>
                </div>

                <a href="{{ route('support.sandbox.customers.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour à la liste
                </a>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            <div class="mb-6">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white">
                    <svg class="h-5 w-5 text-brand-500 dark:text-brand-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                    Informations de l'entreprise et de son responsable
                </h2>

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Renseignez les informations de l'entreprise ainsi que celles de l'utilisateur qui lui sera associé. Tous
                    les champs sont obligatoires.
                </p>
            </div>

            {{-- Information --}}
            <div
                class="mb-5 rounded-xl border border-warning-500/30 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">
                <div class="flex items-start gap-3">

                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning-500 dark:text-warning-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 9v3m0 4h.01M10.3 3.86L2.7 17a2 2 0 001.74 3h15.12a2 2 0 001.74-3L13.7 3.86a2 2 0 00-3.4 0z" />
                    </svg>

                    <p class="leading-6">
                        La création enregistre le client et transmet également les informations de son utilisateur associé à
                        ManyPayCash.
                        Cet utilisateur pourra ensuite être retrouvé dans la rubrique
                        <strong class="font-semibold text-warning-800 dark:text-warning-300">
                            Utilisateurs Sandbox
                        </strong>.
                    </p>

                </div>
            </div>


            @if ($errors->has('api'))
                <div
                    class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">

                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                    </svg>

                    <p>{{ $errors->first('api') }}</p>
                </div>
            @endif

            <form action="{{ route('support.sandbox.customers.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Entreprise --}}
                    <div>
                        <label for="entreprise"
                            class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01M14 12v.01M14 15v.01M14 18v.01" />
                            </svg>
                            Entreprise <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="entreprise" name="entreprise" value="{{ old('entreprise') }}"
                            placeholder="Ex : Entreprise Test" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500">

                        @error('entreprise')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Prénom --}}
                    <div>
                        <label for="prenom"
                            class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M20 21a8 8 0 00-16 0m8-10a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                            Prénom du responsable <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}"
                            placeholder="Ex : Pape" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500">

                        @error('prenom')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Nom --}}
                    <div>
                        <label for="nom"
                            class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm7-1h5m-2.5-2.5v5" />
                            </svg>
                            Nom du responsable <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="nom" name="nom" value="{{ old('nom') }}"
                            placeholder="Ex : Yade" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500">

                        @error('nom')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email"
                            class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-brand-500 dark:text-brand-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Adresse e-mail du responsable <span class="text-red-500">*</span>
                        </label>

                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="exemple@entreprise.com" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500">

                        @error('email')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 dark:border-gray-800 sm:flex-row sm:justify-end">
                    <a href="{{ route('support.sandbox.customers.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Annuler
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                        </svg>
                        Créer l'entreprise et son utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
