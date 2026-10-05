<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion des Rôles Admin & Support') - Laravel</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite / Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fallback / Base Styling for guaranteed high aesthetic rendering -->
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        h1, h2, h3, h4, .brand-font {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
        }
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .card-hover:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
            transform: translateY(-1px);
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="min-h-full flex flex-col antialiased text-slate-800">

    @auth
    <!-- Navigation Bar Principale -->
    <header class="glass-header sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 text-slate-900 font-bold text-lg brand-font group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-200 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="tracking-tight text-slate-900 leading-tight">Admin & Support</span>
                            <span class="text-[11px] font-medium text-slate-400">Portail de Gestion</span>
                        </div>
                    </a>

                    <!-- Navigation Links selon le rôle -->
                    <nav class="hidden md:flex items-center space-x-1">
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Tableau de bord
                            </a>
                            <a href="{{ route('admin.supports.create') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.supports.create') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                + Créer un Support
                            </a>
                        @elseif(Auth::user()->hasRole('support'))
                            <a href="{{ route('support.dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('support.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Espace Support
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Profil & Déconnexion -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3 bg-slate-50 border border-slate-200/80 px-3 py-1.5 rounded-full">
                        <!-- Avatar -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ Auth::user()->hasRole('admin') ? 'bg-indigo-600 text-white' : 'bg-emerald-600 text-white' }}">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</span>
                            <span class="text-[10px] text-slate-500">{{ Auth::user()->email }}</span>
                        </div>
                        <!-- Role Badge -->
                        @if(Auth::user()->hasRole('admin'))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                Admin
                            </span>
                        @elseif(Auth::user()->hasRole('support'))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Support
                            </span>
                        @endif
                    </div>

                    <!-- Bouton Déconnexion -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Déconnexion">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    @endauth

    <!-- Notifications Flash -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 shadow-sm flex items-start space-x-3">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('status'))
            <div class="mb-6 rounded-xl bg-blue-50 border border-blue-200 p-4 shadow-sm flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm font-medium text-blue-800">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if($errors->any() && !request()->routeIs('login'))
            <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 shadow-sm">
                <div class="flex items-center space-x-2 text-rose-800 font-semibold text-sm mb-1">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>Veuillez corriger les erreurs suivantes :</span>
                </div>
                <ul class="list-disc list-inside text-sm text-rose-700 pl-2 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <p>Application Laravel &bull; Gestion des Rôles (Admin & Support) &bull; Blade</p>
    </footer>

</body>
</html>
