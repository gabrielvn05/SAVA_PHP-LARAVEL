<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SAVA')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="app-public">
    @yield('content')
</body>
</html>
