<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Connexion') - Support Manypaycash</title>

    <link rel="icon" href="{{ asset('tailadmin/src/images/logo/auth-logo.svg') }}">

    <link rel="stylesheet" href="{{ asset('tailadmin/style.css') }}">
</head>

<body
    x-data="{
        page: 'comingSoon',
        loaded: true,
        darkMode: false,
        stickyMenu: false,
        sidebarToggle: false,
        scrollTop: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
    "
    :class="{ 'dark bg-gray-900': darkMode === true }"
>
    @yield('content')

    <script defer src="{{ asset('tailadmin/bundle.js') }}"></script>
</body>
</html>
