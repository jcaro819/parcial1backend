<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_created_by_migrations(): void
    {
        $this->assertEqualsCanonicalizing(
            ['Cómics', 'Ropa', 'Coleccionables', 'Accesorios'],
            Category::pluck('name')->all(),
        );
    }

    public function test_catalog_shows_original_and_discounted_prices(): void
    {
        Product::factory()->create(['name' => 'Figura Promo', 'price' => 10000, 'stock' => 30]);
        Product::factory()->create(['name' => 'Figura Normal', 'price' => 7000, 'stock' => 5]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Figura Promo')
            ->assertSee('$10.000')
            ->assertSee('$9.000')
            ->assertSee('Figura Normal')
            ->assertSee('$7.000');
    }

    public function test_catalog_filters_by_category_franchise_and_name(): void
    {
        $comics = Category::where('slug', 'comics')->first();
        $ropa = Category::where('slug', 'ropa')->first();

        Product::factory()->create(['name' => 'Comic Vader', 'category_id' => $comics->id, 'franchise' => 'Star Wars']);
        Product::factory()->create(['name' => 'Polera Atreides', 'category_id' => $ropa->id, 'franchise' => 'Dune']);

        $this->get(route('catalog.index', ['category' => $comics->id]))
            ->assertSee('Comic Vader')->assertDontSee('Polera Atreides');

        $this->get(route('catalog.index', ['franchise' => 'Dune']))
            ->assertSee('Polera Atreides')->assertDontSee('Comic Vader');

        $this->get(route('catalog.index', ['name' => 'vader']))
            ->assertSee('Comic Vader')->assertDontSee('Polera Atreides');
    }

    public function test_product_page_is_displayed(): void
    {
        $product = Product::factory()->create();

        $this->get(route('catalog.show', $product))->assertOk()->assertSee($product->name);
    }
}
