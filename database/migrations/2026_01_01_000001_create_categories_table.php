<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de categorías y registra las categorías base de NerdVault.
     * (El enunciado exige que las categorías se registren por medio de migraciones.)
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('categories')->insert([
            ['name' => 'Cómics',         'slug' => 'comics',         'description' => 'Cómics, mangas y novelas gráficas.',           'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ropa',           'slug' => 'ropa',           'description' => 'Poleras, polerones y gorros de tus sagas.',    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Coleccionables', 'slug' => 'coleccionables', 'description' => 'Figuras, estatuas y réplicas de colección.',    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Accesorios',     'slug' => 'accesorios',     'description' => 'Tazas, llaveros, mochilas y más.',              'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
