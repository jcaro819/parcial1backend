<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'category_id' => Category::first()->id,
            'name' => 'Figura Grogu',
            'description' => 'Figura de colección.',
            'price' => 25990,
            'stock' => 10,
            'franchise' => 'Star Wars',
        ], $overrides);
    }

    public function test_it_lists_products(): void
    {
        Product::factory()->create(['name' => 'Taza Zelda']);

        $this->get(route('admin.products.index'))->assertOk()->assertSee('Taza Zelda');
    }

    public function test_it_creates_a_product(): void
    {
        $this->post(route('admin.products.store'), $this->validData())
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Figura Grogu', 'stock' => 10]);
    }

    public function test_it_validates_the_product(): void
    {
        $this->post(route('admin.products.store'), $this->validData(['price' => -5, 'category_id' => 999]))
            ->assertSessionHasErrors(['price', 'category_id']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_it_updates_a_product(): void
    {
        $product = Product::factory()->create();

        $this->put(route('admin.products.update', $product), $this->validData(['name' => 'Nuevo nombre', 'stock' => 50]))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nuevo nombre', 'stock' => 50]);
    }

    public function test_it_deletes_a_product(): void
    {
        $product = Product::factory()->create();

        $this->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
