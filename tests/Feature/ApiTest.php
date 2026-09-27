<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_lists_products_as_json(): void
    {
        Product::factory()->create(['name' => 'Funko Grogu', 'price' => 10000, 'stock' => 30]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Funko Grogu')
            ->assertJsonPath('data.0.is_on_promotion', true)
            ->assertJsonPath('data.0.final_price', 9000);
    }

    public function test_api_registers_a_sale(): void
    {
        $product = Product::factory()->create(['price' => 5000, 'stock' => 4]);

        $this->postJson('/api/sales', [
            'customer_name' => 'Luis',
            'customer_email' => 'luis@example.com',
            'card_holder' => 'LUIS',
            'card_number' => '5555555555554444',
            'card_expiration' => '10/'.now()->addYear()->format('y'),
            'card_cvv' => '321',
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertCreated()->assertJsonPath('data.total', 20000)->assertJsonPath('data.card', 'MASTERCARD **** **** **** 4444');

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_api_rejects_sale_without_stock(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $this->postJson('/api/sales', [
            'customer_name' => 'Luis',
            'customer_email' => 'luis@example.com',
            'card_holder' => 'LUIS',
            'card_number' => '4111111111111111',
            'card_expiration' => '10/'.now()->addYear()->format('y'),
            'card_cvv' => '321',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertStatus(422)->assertJsonPath('available_stock', 1);
    }
}
