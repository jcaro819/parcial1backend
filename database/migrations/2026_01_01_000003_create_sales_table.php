<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            // Datos del comprador
            $table->string('customer_name', 150);
            $table->string('customer_email', 150)->index();
            // Datos de la tarjeta. Por seguridad (norma PCI-DSS) NUNCA se guarda
            // el número completo ni el CVV: sólo titular, marca, últimos 4 dígitos y vencimiento.
            $table->string('card_holder', 150);
            $table->string('card_brand', 30);
            $table->char('card_last_four', 4);
            $table->string('card_expiration', 5); // MM/AA
            // Totales
            $table->decimal('subtotal', 12, 2);   // suma a precio base
            $table->decimal('discount', 12, 2);   // total descontado por promociones
            $table->decimal('total', 12, 2);      // total a pagar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
