@extends('layouts.app')

@section('title', 'Carrito')

@section('content')
<h1 class="h3 mb-3"><i class="bi bi-cart3"></i> Carrito de compras</h1>

@if ($items->isEmpty())
    <div class="alert alert-info">
        Tu carrito está vacío. <a href="{{ route('catalog.index') }}">Ir al catálogo</a>
    </div>
@else
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Precio unitario</th>
                    <th style="width: 220px">Cantidad</th>
                    <th class="text-end">Subtotal</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($items as $item)
                    @php($product = $item['product'])
                    <tr>
                        <td>
                            <a href="{{ route('catalog.show', $product) }}">{{ $product->name }}</a>
                            <div class="small text-muted">{{ $product->franchise }} · {{ $product->category->name }}</div>
                            @if ($item['quantity'] > $product->stock)
                                <div class="small text-danger">Sólo quedan {{ $product->stock }} unidades.</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($product->is_on_promotion)
                                <span class="price-old small">@money($item['unit_price'])</span><br>
                            @endif
                            @money($item['final_unit_price'])
                        </td>
                        <td>
                            {{-- Formulario PUT: HTML sólo soporta GET/POST, @method('PUT') agrega un campo oculto _method --}}
                            <form method="POST" action="{{ route('cart.update', $product) }}" class="input-group input-group-sm">
                                @csrf
                                @method('PUT')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ max(1, $product->stock) }}" class="form-control">
                                <button class="btn btn-outline-primary" title="Actualizar cantidad"><i class="bi bi-arrow-repeat"></i></button>
                            </form>
                        </td>
                        <td class="text-end fw-semibold">@money($item['line_total'])</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('cart.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Quitar"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('¿Vaciar el carrito?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Vaciar carrito</button>
                <a href="{{ route('catalog.index') }}" class="btn btn-link">Seguir comprando</a>
            </form>
        </div>
        <div class="col-md-6">
            <div class="card card-body shadow-sm">
                @include('partials.totals', ['totals' => $totals])
                <a href="{{ route('checkout.create') }}" class="btn btn-success btn-lg w-100 mt-3">
                    <i class="bi bi-credit-card"></i> Ir al checkout
                </a>
            </div>
        </div>
    </div>
@endif
@endsection
