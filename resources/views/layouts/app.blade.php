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
        <aside :class="sidebarToggle ? 'translate-x-0 xl:w-22.5' : '-translate-x-full rtl:translate-x-full'"
            class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 rtl:right-0 rtl:left-auto rtl:border-r-0 rtl:border-l dark:border-gray-800 dark:bg-black"
            @click.outside="if (window.innerWidth < 1280) sidebarToggle = false">

            {{-- SIDEBAR HEADER --}}
            <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
                class="sidebar-header flex items-center gap-2 pt-8 pb-7">
                <a
                    href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('support.dashboard') }}">

                    <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                        <img class="dark:hidden" src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}"
                            alt="Support Manypaycash" />

                        <img class="hidden dark:block" src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}"
                            alt="Support Manypaycash" />
                    </span>

                    <img class="logo-icon" :class="sidebarToggle ? 'xl:block' : 'hidden'"
                        src="{{ asset('tailadmin/src/images/logo/icon.png') }}" alt="Support Manypaycash" />
                </a>
            </div>
            {{-- SIDEBAR HEADER --}}


            {{-- SIDEBAR MENU --}}
            <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">

                <nav x-data="{ selected: 'Dashboard' }">

                    {{-- =================================================
 MENU
================================================== --}}
                    <div>

                        <h3 class="mb-4 text-xs leading-5 text-gray-400 uppercase">
                            <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">
                                MENU
                            </span>

                            <svg :class="sidebarToggle ? 'xl:block hidden' : 'hidden'"
                                class="menu-group-icon mx-auto fill-current" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                    fill="currentColor" />
                            </svg>
                        </h3>


                        <ul class="mb-6 flex flex-col gap-1">

                            {{-- =================================================
             DASHBOARD
        ================================================== --}}
                            <li>
                                <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('support.dashboard') }}"
                                    class="menu-item group {{ request()->routeIs('admin.dashboard', 'support.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                    <svg class="{{ request()->routeIs('admin.dashboard', 'support.dashboard')
                                        ? 'menu-item-icon-active'
                                        : 'menu-item-icon-inactive' }}"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.0858 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.0858 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                            fill="currentColor" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                        Tableau de bord
                                    </span>
                                </a>
                            </li>


                            {{-- =================================================
             ADMIN
        ================================================== --}}
                            @if (auth()->user()->hasRole('admin'))
                                {{-- CRÉER UN AGENT SUPPORT --}}
                                <li>
                                    <a href="{{ route('admin.supports.create') }}"
                                        class="menu-item group {{ request()->routeIs('admin.supports.create') ? 'menu-item-active' : 'menu-item-inactive' }}">

                                        <svg class="{{ request()->routeIs('admin.supports.create') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15 19C15 16.7909 13.2091 15 11 15C8.79086 15 7 16.7909 7 19"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />

                                            <path
                                                d="M11 12C13.2091 12 15 10.2091 15 8C15 5.79086 13.2091 4 11 4C8.79086 4 7 5.79086 7 8C7 10.2091 8.79086 12 11 12Z"
                                                stroke="currentColor" stroke-width="1.5" />

                                            <path d="M19 8V14M22 11H16" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Créer un agent Support
                                        </span>
                                    </a>
                                </li>

                                {{-- ACCÈS SUPERVISION SERVICES (POUR ADMIN) --}}
                                <li>
                                    <a href="{{ route('support.dashboard') }}"
                                        class="menu-item group {{ request()->routeIs('support.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                        <svg class="{{ request()->routeIs('support.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 12H7L9 5L13 19L15 12H20" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Statut des services
                                        </span>
                                    </a>
                                </li>

                                {{-- LISTE DES CLIENTS (ADMIN) --}}
                                <li>
                                    <a href="{{ route('support.clients.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.clients.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                        <svg class="{{ request()->routeIs('support.clients.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M16 21V19C16 16.7909 14.2091 15 12 15H7C4.79086 15 3 16.7909 3 19V21"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                            <circle cx="9.5" cy="7" r="3.5" stroke="currentColor"
                                                stroke-width="1.8" />
                                            <path d="M19 8V14M22 11H16" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Liste des clients
                                        </span>
                                    </a>
                                </li>

                                {{-- RECHERCHE TRANSACTIONS (ADMIN) --}}
                                <li>
                                    <a href="{{ route('support.transactions.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.transactions.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                        <svg class="{{ request()->routeIs('support.transactions.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Recherche transactions
                                        </span>
                                    </a>
                                </li>
                            @else
                                {{-- =================================================
                 SUPPORT — SUPERVISION
            ================================================== --}}

                                <li class="pt-2">
                                    <div class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-gray-400"
                                        :class="sidebarToggle ? 'xl:hidden' : ''">
                                        Supervision
                                    </div>
                                </li>

                                {{-- STATUT DES SERVICES --}}
                                <li>
                                    <a href="{{ route('support.dashboard') }}#services-section"
                                        class="menu-item group {{ request()->routeIs('support.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                        <svg class="{{ request()->routeIs('support.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 12H7L9 5L13 19L15 12H20" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Statut des services
                                        </span>
                                    </a>
                                </li>

                                {{-- LISTE DES CLIENTS (SUPPORT) --}}
                                <li>
                                    <a href="{{ route('support.clients.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.clients.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                        <svg class="{{ request()->routeIs('support.clients.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M16 21V19C16 16.7909 14.2091 15 12 15H7C4.79086 15 3 16.7909 3 19V21"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                            <circle cx="9.5" cy="7" r="3.5" stroke="currentColor"
                                                stroke-width="1.8" />
                                            <path d="M19 8V14M22 11H16" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Liste des clients
                                        </span>
                                    </a>
                                </li>

                                {{-- RECHERCHE TRANSACTIONS (SUPPORT) --}}
                                <li>
                                    <a href="{{ route('support.transactions.index') }}"
                                        class="menu-item group {{ request()->routeIs('support.transactions.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                        <svg class="{{ request()->routeIs('support.transactions.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Recherche transactions
                                        </span>
                                    </a>
                                </li>

                                {{-- DISPONIBILITÉ PAYOUT --}}
                                <li>
                                    <a href="#" class="menu-item group menu-item-inactive">
                                        <svg class="menu-item-icon-inactive" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 3V21M5 8H15C17.2091 8 19 9.79086 19 12C19 14.2091 17.209 16 15 16H7"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Disponibilité Payout
                                        </span>
                                    </a>
                                </li>


                                {{-- =================================================
                 SUPPORT — TRANSACTIONS
            ================================================== --}}

                                <li class="pt-4">
                                    <div class="px-3 py-2 text-xs font-medium uppercase tracking-wider text-gray-400"
                                        :class="sidebarToggle ? 'xl:hidden' : ''">
                                        Transactions
                                    </div>
                                </li>

                                {{-- RECHERCHER UNE TRANSACTION --}}
                                <li>
                                    <a href="#" class="menu-item group menu-item-inactive">
                                        <svg class="menu-item-icon-inactive" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="11" cy="11" r="6.5" stroke="currentColor"
                                                stroke-width="1.8" />
                                            <path d="M16 16L21 21" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Rechercher une transaction
                                        </span>
                                    </a>
                                </li>

                                {{-- TRANSACTIONS PAR CLIENT --}}
                                <li>
                                    <a href="#" class="menu-item group menu-item-inactive">
                                        <svg class="menu-item-icon-inactive" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M16 21V19C16 16.7909 14.2091 15 12 15H7C4.79086 15 3 16.7909 3 19V21"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                            <circle cx="9.5" cy="7" r="3.5" stroke="currentColor"
                                                stroke-width="1.8" />
                                            <path d="M19 8V14M22 11H16" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" />
                                        </svg>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                            Transactions par client
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
                                class="menu-group-icon mx-auto fill-current" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 10.2451 10.2491 11.9951C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                    fill="currentColor" />
                            </svg>
                        </h3>

                        <ul class="flex flex-col gap-1">

                            {{-- PROFIL --}}
                            {{-- <li>
                            <a
                                href="#"
                                class="menu-item group menu-item-inactive"
                            >
                                <svg
                                    class="menu-item-icon-inactive"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M20 21C20 17.134 16.866 14 13 14H11C7.13401 14 4 17.134 4 21"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />

                                    <path
                                        d="M12 11C14.7614 11 17 8.76142 17 6C17 3.23858 14.7614 1 12 1C9.23858 1 7 3.23858 7 6C7 8.76142 9.23858 11 12 11Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    />
                                </svg>

                                <span
                                    class="menu-item-text"
                                    :class="sidebarToggle ? 'xl:hidden' : ''"
                                >
                                    Profil
                                </span>
                            </a>
                        </li> --}}


                            {{-- DÉCONNEXION --}}
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button type="submit"
                                        class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-700 transition hover:bg-error-50 hover:text-error-500 dark:text-white/90 dark:hover:!bg-error-500/10 dark:hover:!text-error-400">
                                        <svg class="h-5 w-5 transition-colors" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path
                                                d="M10 17L15 12L10 7M15 12H3M11 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H11"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        <span>
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

                        {{-- Sidebar toggle --}}
                        <button
                            :class="sidebarToggle
                                ?
                                'xl:bg-transparent dark:xl:bg-transparent bg-gray-100 dark:bg-gray-800' :
                                ''"
                            class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 xl:h-11 xl:w-11 xl:border dark:border-gray-800 dark:text-gray-400"
                            @click.stop="sidebarToggle = !sidebarToggle" type="button"
                            aria-label="Ouvrir ou fermer le menu">
                            <svg class="hidden xl:block" width="16" height="12" viewBox="0 0 16 12"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 2H15M1 10H15" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                            </svg>

                            <svg class="xl:hidden" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                            </svg>
                        </button>


                        {{-- Mobile logo --}}
                        <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('support.dashboard') }}"
                            class="xl:hidden">
                            <img class="dark:hidden w-48"
                                src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}" alt="Support BBS" />

                            <img class="hidden dark:block"
                                src="{{ asset('tailadmin/src/images/logo/manypaycash.png') }}" alt="Support BBS" />
                        </a>


                        <div class="flex flex-1 items-center justify-end gap-3">

                            {{-- DARK MODE --}}
                            <button type="button" @click="darkMode = !darkMode"
                                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                aria-label="Changer le thème">
                                <svg x-show="!darkMode" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3V5M12 19V21M4.93 4.93L6.34 6.34M17.66 17.66L19.07 19.07M3 12H5M19 12H21M4.93 19.07L6.34 17.66M17.66 6.34L19.07 4.93M12 7C9.24 7 7 9.24 7 12C7 14.76 9.24 17 12 17C14.76 17 17 14.76 17 12C17 9.24 14.76 7 12 7Z" />
                                </svg>

                                <svg x-show="darkMode" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 12.79A9 9 0 1 1 11.21 3A7 7 0 0 0 21 12.79Z" />
                                </svg>
                            </button>


                            {{-- USER --}}
                            <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                                <button type="button" @click="dropdownOpen = !dropdownOpen"
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

                                    <svg class="hidden h-4 w-4 text-gray-500 xl:block" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path d="M6 9L12 15L18 9" stroke-linecap="round" stroke-linejoin="round" />
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
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="1.8">
                                                    <path
                                                        d="M10 17L15 12L10 7M15 12H3M11 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H11"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>

                                                <span
                                                    class="text-gray-700 transition-colors group-hover:text-error-500 dark:text-gray-300 dark:group-hover:text-error-400">
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
