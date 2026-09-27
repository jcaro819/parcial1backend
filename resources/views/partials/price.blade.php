{{-- Muestra precio original, descuento (si aplica) y precio final de un producto --}}
@if ($product->is_on_promotion)
    <div>
        <span class="price-old">@money($product->price)</span>
        <span class="badge bg-danger">-{{ (int) ($product->discount_rate * 100) }}%</span>
    </div>
    <div class="fs-5 fw-bold text-success">@money($product->final_price)</div>
    <small class="text-muted">Ahorras @money($product->discount_amount)</small>
@else
    <div class="fs-5 fw-bold">@money($product->price)</div>
@endif
