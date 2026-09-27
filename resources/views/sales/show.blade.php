@extends('layouts.app')

@section('title', 'Venta #'.$sale->id)

@section('content')
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between flex-wrap">
            <h1 class="h4">Comprobante de venta #{{ $sale->id }}</h1>
            <span class="text-muted">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <h2 class="h6 text-uppercase text-muted">Comprador</h2>
                <p class="mb-0">{{ $sale->customer_name }}</p>
                <p>{{ $sale->customer_email }}</p>
            </div>
            <div class="col-md-6">
                <h2 class="h6 text-uppercase text-muted">Pago</h2>
                <p class="mb-0">{{ $sale->maskedCard() }}</p>
                <p>Titular: {{ $sale->card_holder }} · Vence {{ $sale->card_expiration }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio base</th>
                    <th class="text-center">Desc.</th>
                    <th class="text-end">Precio final</th>
                    <th class="text-end">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>
                            @if ($item->product)
                                <a href="{{ route('catalog.show', $item->product) }}">{{ $item->product_name }}</a>
                            @else
                                {{ $item->product_name }} <span class="small text-muted">(ya no está en el catálogo)</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">@money($item->unit_price)</td>
                        <td class="text-center">{{ $item->discount_rate > 0 ? (int) ($item->discount_rate * 100).'%' : '—' }}</td>
                        <td class="text-end">@money($item->final_unit_price)</td>
                        <td class="text-end">@money($item->line_total)</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                @include('partials.totals', ['totals' => ['subtotal' => $sale->subtotal, 'discount' => $sale->discount, 'total' => $sale->total]])
            </div>
        </div>

        <a href="{{ route('catalog.index') }}" class="btn btn-primary mt-3">Volver al catálogo</a>
    </div>
</div>
@endsection
