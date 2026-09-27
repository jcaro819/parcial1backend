<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Ana Pérez',
            'customer_email' => 'ana@example.com',
            'card_holder' => 'ANA PEREZ',
            'card_number' => '4111 1111 1111 1111',
            'card_expiration' => '12/'.now()->addYears(2)->format('y'),
            'card_cvv' => '123',
        ];
    }

    public function test_full_purchase_flow_registers_sale_and_decreases_stock(): void
    {
        $promo = Product::factory()->create(['price' => 10000, 'stock' => 25]); // en promoción
        $normal = Product::factory()->create(['price' => 5000, 'stock' => 3]);

        $this->post(route('cart.store', $promo), ['quantity' => 2])->assertRedirect(route('cart.index'));
        $this->post(route('cart.store', $normal), ['quantity' => 1]);
        $this->put(route('cart.update', $normal), ['quantity' => 3]);

        $this->get(route('cart.index'))->assertOk()->assertSee($promo->name)->assertSee('$33.000');

        $response = $this->post(route('checkout.store'), $this->checkoutData());

        $sale = Sale::first();
        $response->assertRedirect(route('sales.show', $sale));

        // 2 × 9.000 (10% desc.) + 3 × 5.000 = 33.000
        $this->assertEquals(33000, $sale->total);
        $this->assertEquals(35000, $sale->subtotal);
        $this->assertEquals(2000, $sale->discount);
        $this->assertSame('VISA', $sale->card_brand);
        $this->assertSame('1111', $sale->card_last_four);
        $this->assertCount(2, $sale->items);

        $this->assertSame(23, $promo->fresh()->stock);
        $this->assertSame(0, $normal->fresh()->stock);

        // El carrito queda vacío
        $this->assertSame([], session('cart', []));
    }

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->post(route('cart.store', $product), ['quantity' => 1])->assertSessionHas('error');

        $this->assertSame([], session('cart', []));
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->post(route('cart.store', $product), ['quantity' => 3])->assertSessionHasErrors('quantity');
    }

    public function test_checkout_fails_without_saving_anything_if_stock_ran_out(): void
    {
        $a = Product::factory()->create(['stock' => 5]);
        $b = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.store', $a), ['quantity' => 2]);
        $this->post(route('cart.store', $b), ['quantity' => 4]);

        // Otro cliente compró mientras tanto
        $b->update(['stock' => 1]);

        $this->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $a->fresh()->stock); // rollback: no se descontó nada
    }

    public function test_checkout_validates_card_data(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.store', $product), ['quantity' => 1]);

        $this->post(route('checkout.store'), array_merge($this->checkoutData(), [
            'card_number' => '1234 5678 9012 3456',
            'card_expiration' => '01/20',
            'card_cvv' => '1',
        ]))->assertSessionHasErrors(['card_number', 'card_expiration', 'card_cvv']);

        $this->assertDatabaseCount('sales', 0);
    }
}
