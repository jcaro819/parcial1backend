<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Catálogo') · NerdVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .product-card { transition: transform .15s; }
        .product-card:hover { transform: translateY(-3px); }
        .price-old { text-decoration: line-through; color: #6c757d; }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('catalog.index') }}"><i class="bi bi-safe2"></i> NerdVault</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('catalog.*')) active @endif" href="{{ route('catalog.index') }}">Catálogo</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('admin.products.*')) active @endif" href="{{ route('admin.products.index') }}">Gestor de productos</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('sales.*')) active @endif" href="{{ route('sales.index') }}">Ventas</a></li>
            </ul>
            <a href="{{ route('cart.index') }}" class="btn btn-outline-light">
                <i class="bi bi-cart3"></i> Carrito
                <span class="badge bg-warning text-dark">{{ $cartCount }}</span>
            </a>
        </div>
    </div>
</nav>

<main class="container py-4 flex-grow-1">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="bg-dark text-white-50 text-center py-3 small">
    NerdVault · Prototipo Laravel {{ app()->version() }} · Evaluación 1 Backend
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
