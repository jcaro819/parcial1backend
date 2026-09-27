# NerdVault · Evaluación 1 Backend (Laravel 12)

Prototipo funcional de la tienda en línea **NerdVault** (ropa, accesorios y figuras de colección de cómics, películas y videojuegos), hecho con **Laravel 12**, **Eloquent**, **Blade** y **Bootstrap 5**.

> ¿Quieres entender **cómo funciona un backend**, cómo se conecta con un frontend y qué es una API?
> Lee la guía: **[GUIA_BACKEND.md](GUIA_BACKEND.md)**.

---

## Qué incluye (según el enunciado)

| Requisito | Dónde está |
|---|---|
| Categorías registradas por **migración** (Cómics, Ropa, Coleccionables, Accesorios) | `database/migrations/2026_01_01_000001_create_categories_table.php` |
| Productos con nombre, descripción, precio, stock y franquicia + **FK** a categoría | `database/migrations/2026_01_01_000002_create_products_table.php` |
| Ventas (comprador, tarjeta, total) y detalle de venta con cantidades | `..._create_sales_table.php`, `..._create_sale_items_table.php` |
| Relaciones Eloquent (`hasMany`, `belongsTo`, `belongsToMany`) y reglas de negocio en los modelos | `app/Models/` |
| **Descuento 10 %** si stock > 20 ("Promoción") | `Product::isOnPromotion()`, `finalPrice()` |
| **No se vende** si stock = 0 | `Product::isAvailable()`, `CartController@store`, `SaleService` |
| Registrar venta: verificar stock, **descontar stock**, guardar total, comprador y tarjeta | `app/Services/SaleService.php` (transacción + `lockForUpdate`) |
| CRUD completo de productos (gestor) | `app/Http/Controllers/Admin/ProductController.php` + `resources/views/admin/products/` |
| Catálogo (página principal) con filtros por **categoría, nombre y franquicia**, precio original y descuento | `CatalogController@index` + `resources/views/catalog/index.blade.php` |
| Página del producto con selector de cantidad | `CatalogController@show` + `catalog/show.blade.php` |
| Carrito: modificar cantidades, quitar productos, ir al checkout | `CartController` + `cart/index.blade.php` |
| Checkout con datos del cliente y la tarjeta | `CheckoutController` + `checkout/create.blade.php` |
| Formularios GET / POST / PUT / DELETE | filtros (GET), agregar al carrito y checkout (POST), editar producto y cantidad (PUT), eliminar (DELETE) |
| **Extra**: API REST JSON + frontend en JavaScript que la consume | `routes/api.php`, `app/Http/Controllers/Api/`, `public/api-demo.html` |
| **Extra**: 20 tests automáticos | `tests/` |

---

## Instalación en tu PC (Windows + Visual Studio Code)

### 1. Instalar PHP, Composer y Laravel (una sola vez)

Abre **PowerShell como administrador** y pega (es el instalador oficial de Laravel, <https://laravel.com/docs/12.x/installation>):

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))
```

Cierra y vuelve a abrir la terminal, y comprueba:

```bash
php -v        # PHP 8.2 o superior
composer -V
git --version # si no lo tienes: https://git-scm.com/download/win
```

> Alternativa: instalar [Laravel Herd](https://herd.laravel.com/windows) (trae PHP y Composer con un instalador gráfico).
> En macOS usa `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"` y en Linux `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"`.

### 2. Extensiones recomendadas de VS Code

- **PHP Intelephense** (autocompletado de PHP)
- **Laravel Blade Snippets** o **Laravel** (oficial) (resaltado de Blade)
- **SQLite Viewer** (para mirar la base de datos `database/database.sqlite`)

### 3. Clonar y levantar el proyecto

En la terminal de VS Code (`Ctrl + ñ`):

```bash
git clone https://github.com/jcaro819/parcial1backend.git
cd parcial1backend
composer run setup   # instala dependencias, crea .env, genera APP_KEY, crea la BD SQLite, migra y carga productos de ejemplo
php artisan serve    # levanta el servidor
```

Abre <http://127.0.0.1:8000> 🎉

`composer run setup` equivale a:

```bash
composer install
copy .env.example .env          # (cp en Mac/Linux)
php artisan key:generate
php artisan migrate --seed      # te preguntará si quieres crear database.sqlite → yes
```

### 4. Páginas

| URL | Qué es |
|---|---|
| <http://127.0.0.1:8000/> | Catálogo (página principal) con filtros |
| `/productos/{id}` | Página del producto |
| `/carrito` | Carrito de compras |
| `/checkout` | Checkout |
| `/ventas` | Ventas registradas y comprobantes |
| `/admin/productos` | Gestor de productos (CRUD) |
| `/api/products` | API JSON (ábrela en el navegador) |
| `/api-demo.html` | Frontend en JavaScript puro que usa la API |

Tarjetas de prueba (pasan la validación Luhn): `4111 1111 1111 1111` (VISA), `5555 5555 5555 4444` (Mastercard). Vencimiento: cualquier fecha futura, p. ej. `12/30`. CVV: `123`.

### 5. Comandos útiles

```bash
php artisan route:list              # ver todas las rutas
php artisan migrate:fresh --seed    # borrar la BD y recrearla con datos de ejemplo
php artisan test                    # correr los tests
php artisan tinker                  # consola para jugar con los modelos: App\Models\Product::first()
```

### ¿Quieres usar MySQL (XAMPP / Laragon) en vez de SQLite?

1. Crea la base de datos `nerdvault` en phpMyAdmin.
2. En `.env` cambia:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nerdvault
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. `php artisan migrate --seed`

---

## API REST (JSON)

| Método | URL | Descripción |
|---|---|---|
| GET | `/api/categories` | Categorías con cantidad de productos |
| GET | `/api/products?category=&franchise=&name=` | Productos (paginados y filtrables) |
| GET | `/api/products/{id}` | Un producto |
| POST | `/api/products` | Crear producto → `201` |
| PUT | `/api/products/{id}` | Actualizar producto |
| DELETE | `/api/products/{id}` | Eliminar producto → `204` |
| POST | `/api/sales` | Registrar venta → `201` (o `422` si no hay stock) |
| GET | `/api/sales/{id}` | Ver una venta |

Ejemplo:

```bash
curl -X POST http://127.0.0.1:8000/api/sales \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"customer_name":"Ana","customer_email":"ana@mail.com","card_holder":"ANA","card_number":"4111111111111111","card_expiration":"12/30","card_cvv":"123","items":[{"product_id":1,"quantity":2}]}'
```

---

## Estructura

```
app/
├── Exceptions/InsufficientStockException.php   # error de negocio: no hay stock
├── Http/
│   ├── Controllers/
│   │   ├── CatalogController.php               # catálogo + página de producto
│   │   ├── CartController.php                  # carrito (sesión)
│   │   ├── CheckoutController.php              # checkout → registra la venta
│   │   ├── SaleController.php                  # historial / comprobante
│   │   ├── Admin/ProductController.php         # CRUD de productos
│   │   └── Api/                                # controladores que responden JSON
│   ├── Requests/                               # validación (ProductRequest, CheckoutRequest)
│   ├── Resources/                              # formato del JSON de la API
│   └── Rules/LuhnCardNumber.php                # validación de número de tarjeta
├── Models/ (Category, Product, Sale, SaleItem) # tablas + relaciones + reglas de negocio
└── Services/
    ├── CartService.php                         # lógica del carrito
    └── SaleService.php                         # lógica de la venta (stock, precios, transacción)
database/migrations/                            # estructura de la BD
database/seeders/ProductSeeder.php              # productos de ejemplo
resources/views/                                # vistas Blade
routes/web.php  routes/api.php                  # rutas
tests/                                          # tests automáticos
```
