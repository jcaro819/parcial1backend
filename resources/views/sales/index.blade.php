@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<h1 class="h3 mb-3"><i class="bi bi-receipt"></i> Ventas registradas</h1>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Tarjeta</th>
                <th class="text-center">Productos</th>
                <th class="text-end">Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer_name }}<div class="small text-muted">{{ $sale->customer_email }}</div></td>
                    <td class="small">{{ $sale->card_brand }} •••• {{ $sale->card_last_four }}</td>
                    <td class="text-center">{{ $sale->items_count }}</td>
                    <td class="text-end fw-semibold">@money($sale->total)</td>
                    <td class="text-end"><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay ventas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $sales->links() }}</div>
@endsection
