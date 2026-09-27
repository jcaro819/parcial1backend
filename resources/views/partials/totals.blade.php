<dl class="row mb-0">
    <dt class="col-7">Subtotal (precio base)</dt>
    <dd class="col-5 text-end">@money($totals['subtotal'])</dd>
    <dt class="col-7 text-success">Descuentos por promoción</dt>
    <dd class="col-5 text-end text-success">-@money($totals['discount'])</dd>
    <dt class="col-7 fs-5">Total a pagar</dt>
    <dd class="col-5 text-end fs-5 fw-bold">@money($totals['total'])</dd>
</dl>
