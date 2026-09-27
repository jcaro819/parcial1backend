{{-- Formulario compartido por "crear" y "editar" --}}
@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
               class="form-control @error('name') is-invalid @enderror" required maxlength="150">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="category_id" class="form-label">Categoría</label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Selecciona...</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Descripción</label>
        <textarea id="description" name="description" rows="4"
                  class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $product->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="franchise" class="form-label">Franquicia</label>
        <input type="text" id="franchise" name="franchise" value="{{ old('franchise', $product->franchise) }}" list="franchise-list"
               class="form-control @error('franchise') is-invalid @enderror" required placeholder="Ej: Star Wars">
        <datalist id="franchise-list">
            @foreach (\App\Models\Product::franchises() as $franchise)
                <option value="{{ $franchise }}">
            @endforeach
        </datalist>
        @error('franchise') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="price" class="form-label">Precio base</label>
        <div class="input-group has-validation">
            <span class="input-group-text">$</span>
            <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price', $product->price) }}"
                   class="form-control @error('price') is-invalid @enderror" required>
            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <label for="stock" class="form-label">Stock disponible</label>
        <input type="number" id="stock" name="stock" min="0" step="1" value="{{ old('stock', $product->stock ?? 0) }}"
               class="form-control @error('stock') is-invalid @enderror" required>
        <div class="form-text">Con más de {{ \App\Models\Product::PROMOTION_STOCK_THRESHOLD }} unidades se aplica 10% de descuento.</div>
        @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
