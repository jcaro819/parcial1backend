@extends('layouts.app')

@section('title', 'Catálogo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Catálogo NerdVault</h1>
    <span class="text-muted">{{ $products->total() }} producto(s)</span>
</div>

{{-- Filtros: formulario GET → los filtros viajan en la URL (?category=1&franchise=...) --}}
<form method="GET" action="{{ route('catalog.index') }}" class="card card-body mb-4 shadow-sm">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="name">Nombre</label>
            <input type="text" id="name" name="name" value="{{ $filters['name'] ?? '' }}" class="form-control" placeholder="Buscar por nombre...">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="category">Categoría</label>
            <select id="category" name="category" class="form-select">
                <option value="">Todas</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="franchise">Franquicia</label>
            <select id="franchise" name="franchise" class="form-select">
                <option value="">Todas</option>
                @foreach ($franchises as $franchise)
                    <option value="{{ $franchise }}" @selected(($filters['franchise'] ?? '') === $franchise)>{{ $franchise }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
    @forelse ($products as $product)
        <div class="col">
            <div class="card h-100 shadow-sm product-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-secondary">{{ $product->category->name }}</span>
                        @if ($product->is_on_promotion)
                            <span class="badge bg-danger">Promoción</span>
                        @elseif (! $product->isAvailable())
                            <span class="badge bg-dark">Agotado</span>
                        @endif
                    </div>
                    <h2 class="h6 card-title">{{ $product->name }}</h2>
                    <p class="small text-muted mb-2"><i class="bi bi-stars"></i> {{ $product->franchise }}</p>
                    <div class="mt-auto">
                        @include('partials.price', ['product' => $product])
                        <a href="{{ route('catalog.show', $product) }}" class="btn btn-outline-primary w-100 mt-2">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">No hay productos que coincidan con los filtros.</div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
