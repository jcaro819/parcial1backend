@extends('layouts.app')

@section('title', 'Gestor de productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="bi bi-box-seam"></i> Gestor de productos</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Buscar por nombre"></div>
    <div class="col-md-4">
        <select name="category" class="form-select">
            <option value="">Todas las categorías</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><button class="btn btn-outline-primary w-100">Buscar</button></div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Franquicia</th>
                <th class="text-end">Precio</th>
                <th class="text-center">Stock</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->franchise }}</td>
                    <td class="text-end">@money($product->price)</td>
                    <td class="text-center">
                        <span class="badge {{ $product->stock === 0 ? 'bg-dark' : ($product->is_on_promotion ? 'bg-danger' : 'bg-secondary') }}">{{ $product->stock }}</span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('catalog.show', $product) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ addslashes($product->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay productos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
<p class="small text-muted mt-2">
    <span class="badge bg-danger">&nbsp;</span> stock &gt; {{ \App\Models\Product::PROMOTION_STOCK_THRESHOLD }} (en promoción, -10%)
    <span class="badge bg-dark ms-3">&nbsp;</span> agotado (no se puede vender)
</p>
@endsection
