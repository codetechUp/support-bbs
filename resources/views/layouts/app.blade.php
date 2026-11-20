<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Support BBS')</title>

    <link rel="icon" href="{{ asset('tailadmin/src/images/logo/icon.png') }}">
    <link rel="stylesheet" href="{{ asset('tailadmin/style.css') }}">

    <style>
        .logout-menu-item:hover,
        .logout-menu-item:hover .menu-item-text,
        .logout-menu-item:hover .menu-item-icon-inactive {
            color: var(--color-error-500) !important;
        }

        .dark .logout-menu-item:hover,
        .dark .logout-menu-item:hover .menu-item-text,
        .dark .logout-menu-item:hover .menu-item-icon-inactive {
            color: var(--color-error-400) !important;
        }
    </style>

    @stack('styles')
</head>

<body x-data="{
    page: 'bbs',
    loaded: true,
    darkMode: false,
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false
}" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;

$watch('darkMode', value => {
    localStorage.setItem('darkMode', JSON.stringify(value));

    if (value) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-color-scheme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-color-scheme', 'light');
    }
});

if (darkMode) {
    document.documentElement.classList.add('dark');
    document.documentElement.setAttribute('data-color-scheme', 'dark');
} else {
    document.documentElement.classList.remove('dark');
    document.documentElement.setAttribute('data-color-scheme', 'light');
}"
    :class="{
        'dark bg-gray-900': darkMode === true,
        'bg-gray-50': darkMode === false
    }">

    <div class="flex h-screen overflow-hidden">

        {{-- =========================================================
             SIDEBAR
        ========================================================== --}}
        <aside
            :class="sidebarToggle ? 'translate-x-0 xl:w-22.5' : '-translate-x-full rtl:translate-x-full'"
            class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 rtl:right-0 rtl:left-auto rtl:border-r-0 rtl:border-l dark:border-gray-800 dark:bg-black"
            @click.outside="if (window.innerWidth < 1280) sidebarToggle = false">

            {{-- SIDEBAR HEADER --}}
            <div
                :class="sidebarToggle ? 'justify-center' : 'justify-between'"
                class="sidebar-header flex items-center gap-2 pt-8 pb-7">

                <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('support.dashboard') }}">

                    <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                        <img class="dark:hidden"
                            src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}"
                            alt="Support ManyPayCash">

                        <img class="hidden dark:block"
                            src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}"
                            alt="Support ManyPayCash">
                    </span>

                    <img class="logo-icon" :class="sidebarToggle ? 'xl:block' : 'hidden'"
                        src="{{ asset('tailadmin/src/images/logo/icon.png') }}"
                        alt="Support ManyPayCash">
                </a>
            </div>
            {{-- END SIDEBAR HEADER --}}


            {{-- SIDEBAR MENU --}}
            <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">

                <nav>

                    {{-- =================================================
                         MENU PRINCIPAL
                    ================================================== --}}
                    <div>

                        <h3 class="mb-4 text-xs leading-5 text-gray-400 uppercase">
                            <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">
                                MENU PRINCIPAL
                            </span>

                            <svg :class="sidebarToggle ? 'xl:block hidden' : 'hidden'"
                                class="menu-group-icon mx-auto fill-current"
                                width="24" height="24" viewBox="0 0 24 24">
                                <path
                                    d="M5.999 10.245a1.75 1.75 0 1 1 0 3.51 1.75 1.75 0 0 1 0-3.51Zm12 0a1.75 1.75 0 1 1 0 3.51 1.75 1.75 0 0 1 0-3.51Zm-6 0a1.75 1.75 0 1 1 0 3.51 1.75 1.75 0 0 1 0-3.51Z"
                                    fill="currentColor" />
                            </svg>
                        </h3>

                        <ul class="mb-6 flex flex-col gap-1">

                            {{-- TABLEAU DE BORD --}}
                            <li>
                                <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('support.dashboard') }}"
                                    class="menu-item group {{ request()->routeIs('admin.dashboard', 'support.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                    <svg class="{{ request()->routeIs('admin.dashboard', 'support.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 4H10V10H4V4ZM14 4H20V10H14V4ZM4 14H10V20H4V14ZM14 14H20V20H14V14Z"
                                            stroke="currentColor" stroke-width="1.6"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                        Tableau de bord
                                    </span>
                                </a>
                            </li>


                            {{-- ADMINISTRATION --}}
                            @if (auth()->user()->hasRole('admin'))

                                <li class="pt-3">
                                    <div class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-gray-400"
                                        :class="sidebarToggle ? 'xl:hidden' : ''">
                                        Administration
                                    </div>
                                </li>

                                {{-- CRÉER UN AGENT --}}
                                <li>
                                    <a href="{{ route('admin.supports.create') }}"
                                        class="menu-item group {{ request()->routeIs('admin.supports.create') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('admin.supports.create') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M15 19C15 16.79 13.21 15 11 15C8.79 15 7 16.79 7 19M11 12C13.21 12 15 10.21 15 8C15 5.79 13.21 4 11 4C8.79 4 7 5.79 7 8C7 10.21 8.79 12 11 12ZM19 8V14M22 11H16"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Créer un agent Support
                                        </span>
                                    </a>
                                </li>

                            @endif


                            {{-- =================================================
                                 SUPERVISION
                            ================================================== --}}
                            <li class="pt-3">
                                <div class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-gray-400"
                                    :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Supervision
                                </div>
                            </li>

                            <li>
                                <a href="{{ route('support.dashboard') }}#services-section"
                                    class="menu-item group {{ request()->routeIs('support.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                    <svg class="{{ request()->routeIs('support.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 12H7L9 5L13 19L15 12H20"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                        Statut des services
                                    </span>
                                </a>
                            </li>


                            {{-- =================================================
                                 GESTION CLASSIQUE
                            ================================================== --}}
                            <li class="pt-4">
                                <div class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-gray-400"
                                    :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Gestion classique
                                </div>
                            </li>

                            {{-- CLIENTS --}}
                            @if (Route::has('support.clients.index'))
                                <li>
                                    <a href="{{ route('support.clients.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.clients.*') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('support.clients.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M16 21V19C16 16.79 14.21 15 12 15H7C4.79 15 3 16.79 3 19V21M9.5 11C11.43 11 13 9.43 13 7.5C13 5.57 11.43 4 9.5 4C7.57 4 6 5.57 6 7.5C6 9.43 7.57 11 9.5 11ZM19 8V14M22 11H16"
                                                stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Clients
                                        </span>
                                    </a>
                                </li>
                            @endif

                            {{-- UTILISATEURS CLASSIQUES --}}
                            @if (Route::has('support.users.index'))
                                <li>
                                    <a href="{{ route('support.users.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('support.users.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="8" r="4"
                                                stroke="currentColor" stroke-width="1.6" />
                                            <path d="M4 21C4 16.58 7.58 13 12 13C16.42 13 20 16.58 20 21"
                                                stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Utilisateurs
                                        </span>
                                    </a>
                                </li>
                            @endif

                            {{-- TRANSACTIONS CLASSIQUES --}}
                            @if (Route::has('support.transactions.index'))
                                <li>
                                    <a href="{{ route('support.transactions.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.transactions.*') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('support.transactions.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M4 7H20M4 12H20M4 17H14M17 15L20 18L17 21"
                                                stroke="currentColor" stroke-width="1.7"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Recherche transactions
                                        </span>
                                    </a>
                                </li>
                            @endif


                            {{-- =================================================
                                 SANDBOX MANY PAY CASH
                            ================================================== --}}
                            <li class="pt-4">
                                <div class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-gray-400"
                                    :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Sandbox ManyPayCash
                                </div>
                            </li>

                            {{-- CLIENTS SANDBOX --}}
                            @if (Route::has('support.sandbox.customers.index'))
                                <li>
                                    <a href="{{ route('support.sandbox.customers.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.sandbox.customers.*') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('support.sandbox.customers.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M16 21V19C16 16.79 14.21 15 12 15H7C4.79 15 3 16.79 3 19V21M9.5 11C11.43 11 13 9.43 13 7.5C13 5.57 11.43 4 9.5 4C7.57 4 6 5.57 6 7.5C6 9.43 7.57 11 9.5 11ZM19 8V14M22 11H16"
                                                stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Clients Sandbox
                                        </span>
                                    </a>
                                </li>
                            @endif

                            {{-- CRÉATION CLIENT SANDBOX --}}
                            @if (Route::has('support.sandbox.customers.create'))
                                <li>
                                    <a href="{{ route('support.sandbox.customers.create') }}"
                                        class="menu-item group {{ request()->routeIs('support.sandbox.customers.create') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('support.sandbox.customers.create') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 5V19M5 12H19"
                                                stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Nouveau client Sandbox
                                        </span>
                                    </a>
                                </li>
                            @endif

                            {{-- UTILISATEURS SANDBOX --}}
                            @if (Route::has('support.sandbox.users.index'))
                                <li>
                                    <a href="{{ route('support.sandbox.users.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.sandbox.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('support.sandbox.users.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="8" r="4"
                                                stroke="currentColor" stroke-width="1.6" />
                                            <path d="M4 21C4 16.58 7.58 13 12 13C16.42 13 20 16.58 20 21"
                                                stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Utilisateurs Sandbox
                                        </span>
                                    </a>
                                </li>
                            @endif

                            {{-- FUTURES ROUTES SANDBOX : AFFICHAGE CONDITIONNEL --}}

                            {{-- UTILISATEURS PAR CLIENT --}}
                            @if (Route::has('support.sandbox.users.customer'))
                                <li>
                                    <a href="{{ route('support.sandbox.users.customer') }}"
                                        class="menu-item group {{ request()->routeIs('support.sandbox.users.customer') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="menu-item-icon-inactive" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none">
                                            <path d="M16 18C18.21 18 20 16.21 20 14C20 11.79 18.21 10 16 10M8 18C5.79 18 4 16.21 4 14C4 11.79 5.79 10 8 10M12 4V20M9 7L12 4L15 7"
                                                stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Utilisateurs par client
                                        </span>
                                    </a>
                                </li>
                            @endif

                            {{-- TRANSACTIONS SANDBOX --}}
                            @if (Route::has('support.sandbox.transactions.index'))
                                <li>
                                    <a href="{{ route('support.sandbox.transactions.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.sandbox.transactions.*') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="menu-item-icon-inactive" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none">
                                            <path d="M4 7H20M4 12H20M4 17H14M17 15L20 18L17 21"
                                                stroke="currentColor" stroke-width="1.7"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Transactions Sandbox
                                        </span>
                                    </a>
                                </li>
                            @endif


                        </ul>
                    </div>


                    {{-- =================================================
                         COMPTE
                    ================================================== --}}
                    <div>

                        <h3 class="mb-4 text-xs leading-5 text-gray-400 uppercase">
                            <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">
                                COMPTE
                            </span>

                            <svg :class="sidebarToggle ? 'xl:block hidden' : 'hidden'"
                                class="menu-group-icon mx-auto fill-current"
                                width="24" height="24" viewBox="0 0 24 24">
                                <path
                                    d="M5.999 10.245a1.75 1.75 0 1 1 0 3.51 1.75 1.75 0 0 1 0-3.51Zm12 0a1.75 1.75 0 1 1 0 3.51 1.75 1.75 0 0 1 0-3.51Zm-6 0a1.75 1.75 0 1 1 0 3.51 1.75 1.75 0 0 1 0-3.51Z"
                                    fill="currentColor" />
                            </svg>
                        </h3>

                        <ul class="flex flex-col gap-1">

                            {{-- DÉCONNEXION --}}
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button type="submit"
                                        class="logout-menu-item group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-700 transition hover:bg-error-50 dark:text-white/90 dark:hover:!bg-error-500/10">

                                        <svg class="menu-item-icon-inactive h-5 w-5"
                                            fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path d="M10 17L15 12L10 7M15 12H3M11 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H11"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text"
                                            :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Déconnexion
                                        </span>
                                    </button>
                                </form>
                            </li>

                        </ul>
                    </div>

                </nav>
            </div>
        </aside>


        {{-- =========================================================
             MAIN AREA
        ========================================================== --}}
        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">

            {{-- =====================================================
                 HEADER
            ====================================================== --}}
            <header
                class="sticky top-0 z-999 flex w-full border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-black">

                <div class="flex grow flex-col items-center justify-between xl:flex-row xl:px-6">

                    <div
                        class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 dark:border-gray-800 sm:gap-4 xl:border-b-0 xl:px-0 xl:py-4">

                        {{-- SIDEBAR TOGGLE --}}
                        <button
                            :class="sidebarToggle
                                ? 'xl:bg-transparent dark:xl:bg-transparent bg-gray-100 dark:bg-gray-800'
                                : ''"
                            class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 xl:h-11 xl:w-11 xl:border dark:border-gray-800 dark:text-gray-400"
                            @click.stop="sidebarToggle = !sidebarToggle"
                            type="button"
                            aria-label="Ouvrir ou fermer le menu">

                            <svg class="hidden xl:block" width="16" height="12"
                                viewBox="0 0 16 12" fill="none">
                                <path d="M1 2H15M1 10H15"
                                    stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                            </svg>

                            <svg class="xl:hidden" width="24" height="24"
                                viewBox="0 0 24 24" fill="none">
                                <path d="M4 6H20M4 12H20M4 18H20"
                                    stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                            </svg>
                        </button>


                        {{-- MOBILE LOGO --}}
                        <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('support.dashboard') }}"
                            class="xl:hidden">

                            <img class="dark:hidden w-48"
                                src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}"
                                alt="Support BBS">

                            <img class="hidden dark:block"
                                src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}"
                                alt="Support BBS">
                        </a>


                        <div class="flex flex-1 items-center justify-end gap-3">

                            {{-- DARK MODE --}}
                            <button type="button"
                                @click="darkMode = !darkMode"
                                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                aria-label="Changer le thème">

                                <svg x-show="!darkMode" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3V5M12 19V21M4.93 4.93L6.34 6.34M17.66 17.66L19.07 19.07M3 12H5M19 12H21M4.93 19.07L6.34 17.66M17.66 6.34L19.07 4.93M12 7C9.24 7 7 9.24 7 12C7 14.76 9.24 17 12 17C14.76 17 17 14.76 17 12C17 9.24 14.76 7 12 7Z" />
                                </svg>

                                <svg x-show="darkMode" x-cloak class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 12.79A9 9 0 1 1 11.21 3A7 7 0 0 0 21 12.79Z" />
                                </svg>
                            </button>


                            {{-- USER DROPDOWN --}}
                            <div class="relative"
                                x-data="{ dropdownOpen: false }"
                                @click.outside="dropdownOpen = false">

                                <button type="button"
                                    @click="dropdownOpen = !dropdownOpen"
                                    class="flex items-center gap-3">

                                    <span
                                        class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-sm font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>

                                    <span class="hidden text-left xl:block">

                                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ auth()->user()->name }}
                                        </span>

                                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                                            {{ auth()->user()->hasRole('admin') ? 'Administrateur' : 'Support' }}
                                        </span>

                                    </span>

                                    <svg class="hidden h-4 w-4 text-gray-500 xl:block"
                                        fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path d="M6 9L12 15L18 9"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                </button>


                                {{-- USER DROPDOWN --}}
                                <div x-show="dropdownOpen" x-cloak
                                    class="absolute right-0 mt-3 w-64 rounded-2xl border border-gray-200 bg-white p-3 shadow-lg dark:border-gray-800 dark:bg-gray-900">

                                    <div class="border-b border-gray-100 px-3 pb-3 dark:border-gray-800">

                                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ auth()->user()->name }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ auth()->user()->email }}
                                        </p>

                                        <p class="mt-2 text-xs font-medium text-brand-500">
                                            {{ auth()->user()->hasRole('admin') ? 'Administrateur' : 'Agent Support' }}
                                        </p>

                                    </div>

                                    <div class="pt-2">

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf

                                            <button type="submit"
                                                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800">

                                                <svg class="h-5 w-5 text-gray-700 transition-colors group-hover:text-error-500 dark:text-gray-300 dark:group-hover:text-error-400"
                                                    fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path d="M10 17L15 12L10 7M15 12H3M11 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H11"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>

                                                <span class="text-gray-700 transition-colors group-hover:text-error-500 dark:text-gray-300 dark:group-hover:text-error-400">
                                                    Déconnexion
                                                </span>
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </header>


            {{-- =====================================================
                 MAIN CONTENT
            ====================================================== --}}
            <main class="min-w-0 flex-1 bg-gray-50 dark:bg-gray-900">

                {{-- FLASH SUCCESS --}}
                @if (session('success'))
                    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-400">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif


                {{-- FLASH STATUS --}}
                @if (session('status'))
                    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-400">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif


                {{-- VALIDATION ERRORS --}}
                @if ($errors->any())
                    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                        <div
                            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400">

                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>

                        </div>
                    </div>
                @endif


                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </div>

            </main>

        </div>
    </div>


    <script defer src="{{ asset('tailadmin/bundle.js') }}"></script>

    @stack('scripts')

</body>

</html>
