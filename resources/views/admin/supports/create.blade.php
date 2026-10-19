@extends('layouts.app')

@section('title', 'Créer un agent Support')

@section('content')

    <div class="space-y-6">

        {{-- EN-TÊTE --}}
        <div>
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center gap-2 text-theme-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>

                Retour au tableau de bord
            </a>
        </div>


        {{-- CARTE --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

            {{-- EN-TÊTE DE LA CARTE --}}
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800 sm:px-8">

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/15">

                        <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 19a6 6 0 00-12 0m6-10a4 4 0 110-8 4 4 0 010 8zm6-3v6m3-3h-6" />
                        </svg>

                    </div>

                    <div>
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                            Créer un nouvel agent Support
                        </h1>

                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                            Créez un compte sécurisé pour un nouveau collaborateur Support.
                        </p>
                    </div>

                </div>

            </div>


            {{-- FORMULAIRE --}}
            <div class="mx-auto max-w-3xl">
                <form action="{{ route('admin.supports.store') }}" method="POST" class="space-y-6 p-6 sm:p-8">

                    @csrf


                    {{-- NOM COMPLET --}}
                    <div>

                        <label for="name" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            Nom complet <span class="text-error-500">*</span>
                        </label>

                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                            placeholder="Ex. Fatou Ndiaye"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800 {{ $errors->has('name') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : '' }}">

                        @error('name')
                            <p class="mt-1.5 text-theme-xs text-error-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- EMAIL --}}
                    <div>

                        <label for="email" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            Adresse email professionnelle <span class="text-error-500">*</span>
                        </label>

                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                            placeholder="Ex. support.fatou@exemple.com"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800 {{ $errors->has('email') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : '' }}">

                        @error('email')
                            <p class="mt-1.5 text-theme-xs text-error-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- MOTS DE PASSE --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- MOT DE PASSE --}}
                        <div>

                            <label for="password"
                                class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                Mot de passe <span class="text-error-500">*</span>
                            </label>

                            <input type="password" name="password" id="password" required minlength="8"
                                placeholder="Minimum 8 caractères"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800 {{ $errors->has('password') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10' : '' }}">

                            @error('password')
                                <p class="mt-1.5 text-theme-xs text-error-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- CONFIRMATION --}}
                        <div>

                            <label for="password_confirmation"
                                class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirmer le mot de passe <span class="text-error-500">*</span>
                            </label>

                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                minlength="8" placeholder="Confirmer le mot de passe"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500">

                        </div>

                    </div>


                    {{-- INFORMATION SUR LE ROLE --}}
                    <div
                        class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">

                        <div class="flex items-start gap-3">

                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 100-20 10 10 0 000 20z" />
                            </svg>

                            <div class="text-theme-sm text-brand-700 dark:text-brand-300">

                                <p class="font-medium">
                                    Attribution automatique du rôle
                                </p>

                                <p class="mt-1">
                                    Ce compte recevra automatiquement le rôle
                                    <span class="font-semibold">
                                        Support
                                    </span>
                                    et pourra accéder à son espace dédié après connexion.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- ACTIONS --}}
                    <div
                        class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">

                        <a href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                            Annuler
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>

                            Créer l'agent Support

                        </button>

                    </div>

                </form>
            </div>

        </div>

    </div>

@endsection
