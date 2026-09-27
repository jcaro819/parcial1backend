@extends('layouts.app')

@section('title', $product->name)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('catalog.index') }}">Catálogo</a></li>
        <li class="breadcrumb-item"><a href="{{ route('catalog.index', ['category' => $product->category_id]) }}">{{ $product->category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol>
</nav>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-7">
                <span class="badge bg-secondary">{{ $product->category->name }}</span>
                <a href="{{ route('catalog.index', ['franchise' => $product->franchise]) }}" class="badge bg-info text-dark text-decoration-none">{{ $product->franchise }}</a>
                @if ($product->is_on_promotion)
                    <span class="badge bg-danger">Promoción -10%</span>
                @endif
                <h1 class="h3 mt-2">{{ $product->name }}</h1>
                <p class="mt-3" style="white-space: pre-line">{{ $product->description }}</p>
            </div>

            <div class="col-md-5">
                <div class="border rounded p-3 bg-white">
                    @include('partials.price', ['product' => $product])

                    <p class="mt-3 mb-2">
                        @if ($product->isAvailable())
                            <i class="bi bi-box-seam"></i> Stock disponible: <strong>{{ $product->stock }}</strong>
                        @else
                            <span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> Producto agotado</span>
                        @endif
                    </p>

                    @if ($inCart > 0)
                        <p class="small text-muted">Ya tienes {{ $inCart }} en tu carrito.</p>
                    @endif

                    @if ($product->isAvailable() && $inCart < $product->stock)
                        <form method="POST" action="{{ route('cart.store', $product) }}">
                            @csrf
                            <label for="quantity" class="form-label">Cantidad</label>
                            <div class="input-group">
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                       value="{{ old('quantity', 1) }}" min="1" max="{{ $product->stock - $inCart }}" required>
                                <button class="btn btn-success"><i class="bi bi-cart-plus"></i> Agregar al carrito</button>
                            </div>
                        </form>
                    @elseif ($product->isAvailable())
                        <div class="alert alert-warning mb-0">Ya agregaste todo el stock disponible.</div>
                    @else
                        <button class="btn btn-secondary w-100" disabled>No disponible</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
