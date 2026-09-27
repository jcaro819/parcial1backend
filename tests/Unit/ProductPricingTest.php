<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductPricingTest extends TestCase
{
    public function test_product_with_more_than_20_units_gets_10_percent_discount(): void
    {
        $product = new Product(['price' => 10000, 'stock' => 21]);

        $this->assertTrue($product->is_on_promotion);
        $this->assertSame(0.10, $product->discount_rate);
        $this->assertSame(9000.0, $product->final_price);
    }

    public function test_product_with_exactly_20_units_has_no_discount(): void
    {
        $product = new Product(['price' => 10000, 'stock' => 20]);

        $this->assertFalse($product->is_on_promotion);
        $this->assertSame(10000.0, $product->final_price);
    }

    public function test_product_without_stock_is_not_available(): void
    {
        $product = new Product(['price' => 5000, 'stock' => 0]);

        $this->assertFalse($product->isAvailable());
        $this->assertFalse($product->hasStockFor(1));
    }
}
