<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla intermedia (detalle de venta): una venta tiene muchos productos
     * y un producto aparece en muchas ventas (relación muchos a muchos).
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            // Si el producto se elimina del catálogo, el historial de la venta se conserva.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            // "Foto" del producto al momento de la compra (el precio puede cambiar después).
            $table->string('product_name', 150);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);     // precio base
            $table->decimal('discount_rate', 4, 2);   // 0.10 si estaba en promoción
            $table->decimal('final_unit_price', 10, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
