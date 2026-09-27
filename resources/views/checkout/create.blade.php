@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<h1 class="h3 mb-3"><i class="bi bi-credit-card"></i> Checkout</h1>

<div class="row g-4">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('checkout.store') }}" class="card card-body shadow-sm" novalidate>
            @csrf

            <h2 class="h5">Datos del cliente</h2>
            <div class="mb-3">
                <label for="customer_name" class="form-label">Nombre completo</label>
                <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}"
                       class="form-control @error('customer_name') is-invalid @enderror" required>
                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label for="customer_email" class="form-label">Email</label>
                <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}"
                       class="form-control @error('customer_email') is-invalid @enderror" required>
                @error('customer_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <h2 class="h5">Tarjeta de crédito</h2>
            <div class="mb-3">
                <label for="card_holder" class="form-label">Titular (como aparece en la tarjeta)</label>
                <input type="text" id="card_holder" name="card_holder" value="{{ old('card_holder') }}"
                       class="form-control @error('card_holder') is-invalid @enderror" required>
                @error('card_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="card_number" class="form-label">Número de tarjeta</label>
                <input type="text" id="card_number" name="card_number" inputmode="numeric" autocomplete="cc-number"
                       placeholder="4111 1111 1111 1111" class="form-control @error('card_number') is-invalid @enderror" required>
                @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label for="card_expiration" class="form-label">Vencimiento (MM/AA)</label>
                    <input type="text" id="card_expiration" name="card_expiration" value="{{ old('card_expiration') }}" placeholder="12/29"
                           class="form-control @error('card_expiration') is-invalid @enderror" required>
                    @error('card_expiration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-6 mb-3">
                    <label for="card_cvv" class="form-label">CVV</label>
                    <input type="password" id="card_cvv" name="card_cvv" maxlength="4" inputmode="numeric" autocomplete="cc-csc"
                           class="form-control @error('card_cvv') is-invalid @enderror" required>
                    @error('card_cvv') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <p class="small text-muted"><i class="bi bi-shield-lock"></i> Por seguridad sólo se guardan el titular, la marca, los últimos 4 dígitos y el vencimiento. El CVV nunca se almacena.</p>

            <button class="btn btn-success btn-lg"><i class="bi bi-bag-check"></i> Pagar @money($totals['total'])</button>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="card card-body shadow-sm">
            <h2 class="h5">Resumen del pedido</h2>
            <ul class="list-group list-group-flush mb-3">
                @foreach ($items as $item)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ $item['quantity'] }} × {{ $item['product']->name }}</span>
                        <span>@money($item['line_total'])</span>
                    </li>
                @endforeach
            </ul>
            @include('partials.totals', ['totals' => $totals])
            <a href="{{ route('cart.index') }}" class="btn btn-link px-0 mt-2">← Volver al carrito</a>
        </div>
    </div>
</div>
@endsection
