<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Productos de ejemplo para probar el catálogo.
 * (Las categorías NO se crean aquí: vienen de la migración create_categories_table.)
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $products = [
            // [categoría, nombre, franquicia, precio, stock, descripción]
            ['comics', 'Star Wars: Darth Vader Vol. 1', 'Star Wars', 18990, 12, 'Cómic oficial de Marvel que narra los días de Vader tras la destrucción de la Estrella de la Muerte.'],
            ['comics', 'Dune: Casa Atreides (Novela gráfica)', 'Dune', 24990, 25, 'Adaptación a novela gráfica de la precuela de Dune escrita por Brian Herbert y Kevin J. Anderson.'],
            ['comics', 'The Legend of Zelda: Twilight Princess Vol. 1', 'The Legend of Zelda', 9990, 30, 'Manga de Akira Himekawa basado en el videojuego Twilight Princess.'],
            ['comics', 'Batman: Año Uno', 'DC Comics', 15990, 0, 'La clásica historia de Frank Miller sobre el primer año de Bruce Wayne como Batman.'],
            ['ropa', 'Polera Casa Atreides', 'Dune', 14990, 40, 'Polera 100% algodón con el emblema del halcón de la Casa Atreides.'],
            ['ropa', 'Polerón Imperio Galáctico', 'Star Wars', 32990, 8, 'Polerón con capucha y el logo del Imperio bordado en el pecho.'],
            ['ropa', 'Gorro Trifuerza', 'The Legend of Zelda', 11990, 22, 'Gorro tejido con la Trifuerza en dorado.'],
            ['coleccionables', 'Figura Link Breath of the Wild 1/7', 'The Legend of Zelda', 89990, 5, 'Figura de PVC de 25 cm de Link con la Espada Maestra.'],
            ['coleccionables', 'Sable de luz Luke Skywalker (réplica)', 'Star Wars', 129990, 3, 'Réplica de metal del sable de luz de Luke con luz y sonido.'],
            ['coleccionables', 'Figura Paul Atreides', 'Dune', 45990, 21, 'Figura articulada de Paul Atreides con traje destilador.'],
            ['coleccionables', 'Funko Pop! Grogu', 'Star Wars', 12990, 50, 'Funko Pop! de Grogu (The Mandalorian).'],
            ['accesorios', 'Taza Master Sword', 'The Legend of Zelda', 8990, 35, 'Taza de cerámica de 350 ml con diseño de la Espada Maestra.'],
            ['accesorios', 'Llavero Halcón Milenario', 'Star Wars', 5990, 18, 'Llavero metálico del Halcón Milenario.'],
            ['accesorios', 'Mochila Arrakis', 'Dune', 39990, 1, 'Mochila resistente con diseño del desierto de Arrakis.'],
        ];

        foreach ($products as [$category, $name, $franchise, $price, $stock, $description]) {
            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categories[$category],
                    'franchise' => $franchise,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $description,
                ],
            );
        }
    }
}
