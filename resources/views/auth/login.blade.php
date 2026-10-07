@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')

    <div class="relative min-h-screen lg:flex">

        {{-- ================================
     PARTIE GAUCHE — CONNEXION
================================= --}}

        <div
            class="flex w-full flex-col justify-center bg-white px-6 py-12 dark:bg-gray-900 sm:px-12 lg:w-1/2 lg:px-16 xl:px-24">

            <div class="mx-auto w-full max-w-md">

                {{-- Logo --}}
                <div class="mb-8">
                    <a href="{{ url('/') }}" class="inline-flex flex-col items-start group">
                        <img src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}" alt="ManyPayCash"
                            class="h-20 w-auto transition-opacity group-hover:opacity-90">

                        <span
                            class="mt-2 pl-0.5 text-[13px] font-semibold uppercase tracking-[0.2em] text-gray-700 dark:text-gray-200">
                            Support
                        </span>
                    </a>
                </div>

                {{-- Introduction --}}
                <div class="mb-8">
                    <h1 class="mb-3 text-title-md font-semibold text-gray-800 dark:text-white/90">
                        Connexion
                    </h1>

                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                        Connexion à l'espace Support BBS.
                    </p>
                </div>

                {{-- ================================
             ERREURS DE CONNEXION
        ================================= --}}

                @if ($errors->any())
                    <div
                        class="mb-6 rounded-lg border border-error-200 bg-error-50 p-4 dark:border-error-500/30 dark:bg-error-500/10">
                        <div class="flex items-start gap-3">

                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-error-500" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>

                            <p class="text-sm font-medium text-error-600 dark:text-error-400">
                                {{ $errors->first() }}
                            </p>

                        </div>
                    </div>
                @endif

                {{-- ================================
             FORMULAIRE DE CONNEXION
        ================================= --}}

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Adresse email
                        </label>

                        <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email"
                            autofocus required placeholder="vous@exemple.com"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">

                        @error('email')
                            <p class="mt-2 text-sm text-error-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div x-data="{ showPassword: false }">

                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mot de passe
                        </label>

                        <div class="relative">

                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                                autocomplete="current-password" required placeholder="Entrez votre mot de passe"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 pr-12 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">

                            {{-- Afficher / masquer le mot de passe --}}
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                aria-label="Afficher ou masquer le mot de passe">

                                {{-- Œil --}}
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>

                                {{-- Œil barré --}}
                                <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                    <path
                                        d="M10.73 5.08A10.94 10.94 0 0 1 12 5c5 0 8.73 4.5 10 7a13.16 13.16 0 0 1-2.16 3.19" />
                                    <path
                                        d="M6.61 6.61A13.526 13.526 0 0 0 2 12c1.27 2.5 5 7 10 7a10.94 10.94 0 0 0 3.27-.5" />
                                    <line x1="2" y1="2" x2="22" y2="22" />
                                </svg>

                            </button>

                        </div>

                        @error('password')
                            <p class="mt-2 text-sm text-error-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Se souvenir de moi --}}
                    <div class="flex items-center">

                        <label class="flex cursor-pointer items-center gap-3">

                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800">

                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Se souvenir de moi
                            </span>

                        </label>

                    </div>

                    {{-- Bouton de connexion --}}
                    <button type="submit"
                        class="flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-3 focus:ring-brand-500/20">
                        Se connecter
                    </button>

                </form>

            </div>

        </div>


        {{-- ================================
     PARTIE DROITE — PRÉSENTATION
================================= --}}

        <div class="relative hidden min-h-screen w-1/2 items-center justify-center overflow-hidden bg-brand-950 lg:flex">

            {{-- Grille --}}
            <div class="absolute inset-0">
                <img src="{{ asset('tailadmin/src/images/shape/grid-01.svg') }}" alt=""
                    class="h-full w-full object-cover opacity-30">
            </div>

            {{-- Contenu --}}
            <div class="relative z-10 max-w-lg px-12 text-center">

                {{-- Logo --}}
                <div class="mb-8 flex justify-center">
                    <img src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}" alt="ManyPayCash"
                        class="h-16 w-auto">
                </div>

                {{-- Identification de l'espace --}}
                <div class="mb-4 flex items-center justify-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-400"></span>

                    <span class="text-xs font-semibold uppercase tracking-tight text-gray-400">
                        Espace Support
                    </span>

                    <span class="h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                </div>

                {{-- Titre --}}
                <h2 class="text-3xl font-semibold uppercase tracking-tight text-white">
                    Support ManyPayCash
                </h2>

                {{-- Description --}}
                <p class="mt-5 text-base leading-7 text-white/70">
                    Gérer les services et accompagner les clients
                    depuis un espace de support centralisé.
                </p>

            </div>

        </div>


        {{-- ================================
     BOUTON MODE SOMBRE
================================= --}}

        <button type="button"
            @click="
        darkMode = !darkMode;
        document.documentElement.classList.toggle('dark', darkMode)
    "
            class="fixed bottom-6 right-6 z-50 flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-theme-md transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            aria-label="Changer le thème">

            {{-- Soleil --}}
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2" />
                <path d="M12 20v2" />
                <path d="m4.93 4.93 1.41 1.41" />
                <path d="m17.66 17.66 1.41 1.41" />
                <path d="M2 12h2" />
                <path d="M20 12h2" />
                <path d="m6.34 17.66-1.41 1.41" />
                <path d="m19.07 4.93-1.41 1.41" />
            </svg>

            {{-- Lune --}}
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>

        </button>


    </div>

@endsection
