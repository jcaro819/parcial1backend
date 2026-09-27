# Guía: qué es un backend y cómo funciona este proyecto

Esta guía explica, con ejemplos **de este mismo proyecto**, qué es un backend, cómo viaja una petición desde el navegador hasta la base de datos y de vuelta, y cómo se conecta un frontend mediante una API.

---

## 1. Frontend vs. backend

```
 ┌──────────────────────────┐        HTTP (internet)        ┌──────────────────────────┐      SQL      ┌───────────────┐
 │        FRONTEND          │  ── petición (request) ──▶    │         BACKEND          │  ──────────▶  │ BASE DE DATOS │
 │  Lo que corre en el      │                               │  Lo que corre en el      │               │  (SQLite,     │
 │  NAVEGADOR del usuario:  │  ◀── respuesta (response) ──  │  SERVIDOR: PHP/Laravel   │  ◀──────────  │   MySQL...)   │
 │  HTML, CSS, JavaScript   │     (HTML o JSON)             │                          │               │               │
 └──────────────────────────┘                               └──────────────────────────┘               └───────────────┘
```

- **Frontend**: lo que el usuario ve y toca. Corre en *su* computador. Cualquiera puede ver y modificar su código (F12 en el navegador), por eso **nunca se confía en él**.
- **Backend**: corre en *tu* servidor. Es donde viven las **reglas de negocio** y los **datos**. El usuario no puede ver ni modificar su código.

**Ejemplo de por qué la lógica va en el backend:** el descuento del 10 %. Si el precio final lo calculara el JavaScript del navegador, un usuario podría abrir las DevTools, cambiar el precio a $1 y comprar. En este proyecto el carrito guarda **solo** `[id_producto => cantidad]` en la sesión, y el precio se recalcula **siempre** desde la base de datos (`CartService::items()` y `SaleService::register()`).

Responsabilidades típicas de un backend:

1. **Recibir** peticiones HTTP.
2. **Validar** lo que envía el usuario (¿el email es un email? ¿la cantidad es positiva? ¿la tarjeta es válida?).
3. **Aplicar reglas de negocio** (descuento si stock > 20, no vender sin stock, descontar stock).
4. **Leer y escribir** en la base de datos.
5. **Responder** (con HTML o con JSON).
6. Seguridad: autenticación, autorización, protección CSRF, no guardar datos sensibles (por eso aquí **no** se guarda el número completo de la tarjeta ni el CVV).

---

## 2. HTTP: el idioma entre frontend y backend

Cada interacción es una **petición** con:

- **Método (verbo)**: qué quieres hacer.
- **URL**: sobre qué recurso.
- **Headers**: metadatos (`Accept: application/json`, cookies, tipo de contenido...).
- **Body** (opcional): datos que envías (formulario o JSON).

Y una **respuesta** con un **código de estado**, headers y un body.

| Verbo | Significado | Ejemplo en NerdVault |
|---|---|---|
| `GET` | Leer (no cambia nada) | `GET /` ver catálogo, `GET /?franchise=Dune` filtrar |
| `POST` | Crear | `POST /admin/productos` crear producto, `POST /checkout` registrar venta |
| `PUT` / `PATCH` | Actualizar | `PUT /admin/productos/5` editar, `PUT /carrito/5` cambiar cantidad |
| `DELETE` | Eliminar | `DELETE /admin/productos/5`, `DELETE /carrito/5` |

| Código | Significado | Cuándo lo verás aquí |
|---|---|---|
| `200 OK` | Todo bien | Ver catálogo |
| `201 Created` | Se creó algo | `POST /api/sales` |
| `204 No Content` | OK, sin cuerpo | `DELETE /api/products/5` |
| `302 Found` | Redirección | Después de guardar un formulario |
| `404 Not Found` | No existe | `/productos/99999` |
| `419` | Token CSRF inválido/expirado | Formulario sin `@csrf` |
| `422 Unprocessable` | Datos inválidos | Validación fallida o stock insuficiente en la API |
| `500` | Error del servidor | Un bug en el código |

> **Truco**: abre las DevTools (F12) → pestaña **Network** y navega por la tienda. Verás cada petición, su método, su código y lo que respondió el servidor.

**¿Y el `@method('PUT')` en los formularios?** Los formularios HTML **solo** saben hacer `GET` y `POST`. Laravel lo resuelve así: envías un `POST` con un campo oculto `_method=PUT`, y Laravel lo trata como `PUT`. Míralo en `resources/views/cart/index.blade.php`.

---

## 3. El viaje de una petición en Laravel (MVC)

Laravel sigue el patrón **MVC**: **M**odelo (datos), **V**ista (presentación), **C**ontrolador (coordina).

Ejemplo real: el usuario presiona **“Pagar”** en el checkout.

```
Navegador
   │  POST /checkout   (body: customer_name, customer_email, card_number, ...  + _token CSRF)
   ▼
public/index.php  ← único punto de entrada de TODAS las peticiones
   │
   ▼
Middleware        ← filtros: inicia la sesión, verifica el token CSRF, etc.
   │
   ▼
routes/web.php    ← Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
   │
   ▼
CheckoutRequest   ← VALIDACIÓN: si falla, Laravel vuelve al formulario con los errores (nunca llega al controlador)
   │
   ▼
CheckoutController@store   ← CONTROLADOR: coordina, no hace cálculos pesados
   │
   ▼
SaleService::register()    ← LÓGICA DE NEGOCIO
   │   DB::transaction(...)
   │     1. Product::lockForUpdate()  → bloquea las filas de los productos
   │     2. verifica stock de cada uno → si falta: InsufficientStockException (rollback)
   │     3. calcula precios con descuento (modelo Product)
   │     4. Sale::create(...) + items()->createMany(...)
   │     5. $product->decrement('stock', $qty)
   ▼
Modelos Eloquent (Sale, SaleItem, Product)  ← traducen PHP ⇄ SQL
   │
   ▼
Base de datos  (INSERT INTO sales..., UPDATE products SET stock = stock - 2 ...)
   │
   ▼
redirect()->route('sales.show', $sale)   ← respuesta 302 → el navegador pide GET /ventas/15
   │
   ▼
SaleController@show → view('sales.show', compact('sale'))  ← VISTA Blade genera el HTML
   │
   ▼
Navegador muestra el comprobante
```

### 3.1 Rutas (`routes/web.php`)

Conectan **verbo + URL** con **un método de un controlador**, y les dan un **nombre**:

```php
Route::get('/productos/{product}', [CatalogController::class, 'show'])->name('catalog.show');
```

En las vistas nunca se escribe la URL a mano; se usa el nombre: `route('catalog.show', $product)` → `/productos/7`. Si mañana cambias la URL, no tienes que tocar las vistas.

`Route::resource('productos', ProductController::class)` crea de una vez las 7 rutas típicas de un CRUD (`index, create, store, show, edit, update, destroy`). Ejecuta `php artisan route:list` para verlas.

**Route Model Binding**: en `{product}`, Laravel busca automáticamente `Product::findOrFail(7)` y te lo entrega en el controlador como `Product $product`. Si no existe → 404.

### 3.2 Controladores (`app/Http/Controllers`)

Reciben la petición, piden datos a los modelos/servicios y devuelven una respuesta:

```php
public function index(Request $request): View
{
    $products = Product::with('category')->filter($request->only(['category', 'franchise', 'name']))->paginate(12);
    return view('catalog.index', ['products' => $products, ...]);   // ← entrega de datos a la vista
}
```

### 3.3 Modelos y Eloquent (`app/Models`)

Cada modelo representa una tabla. Eloquent es el **ORM**: te deja trabajar con objetos PHP en vez de escribir SQL.

```php
Product::where('franchise', 'Dune')->get();
// SQL generado: SELECT * FROM products WHERE franchise = 'Dune'

$product->category->name;   // relación belongsTo → SELECT * FROM categories WHERE id = ?
$category->products;        // relación hasMany
$sale->products;            // relación belongsToMany (a través de sale_items)
```

**POO en los modelos**: las reglas del negocio viven dentro del modelo `Product`, encapsuladas:

```php
public const PROMOTION_STOCK_THRESHOLD = 20;
public const PROMOTION_DISCOUNT_RATE = 0.10;

protected function isOnPromotion(): Attribute   // $product->is_on_promotion
{
    return Attribute::get(fn () => $this->stock > self::PROMOTION_STOCK_THRESHOLD);
}

protected function finalPrice(): Attribute      // $product->final_price
{
    return Attribute::get(fn () => round($this->price - $this->discount_amount, 2));
}

public function isAvailable(): bool { return $this->stock > 0; }
```

Así la vista, el carrito, el checkout y la API usan **la misma** regla: si cambia el porcentaje, se cambia en un solo lugar.

### 3.4 Migraciones (`database/migrations`)

Son el **control de versiones de la base de datos**: código PHP que crea/modifica tablas. Cualquiera que clone el repo ejecuta `php artisan migrate` y obtiene exactamente la misma estructura.

```php
$table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
```

Eso crea una **llave foránea**: la BD garantiza que un producto siempre apunte a una categoría que existe, y **no deja** borrar una categoría con productos.

Diagrama de la base de datos:

```
categories 1 ──── N products 1 ──── N sale_items N ──── 1 sales
(id, name, slug)    (id, category_id FK, name,     (id, sale_id FK, product_id FK,   (id, customer_name, customer_email,
                     description, price, stock,     product_name, quantity,           card_holder, card_brand, card_last_four,
                     franchise)                     unit_price, discount_rate,        card_expiration, subtotal, discount, total)
                                                    final_unit_price, line_total)
```

`sale_items` es la **tabla intermedia** de la relación muchos-a-muchos venta ⇄ producto, y además guarda una "foto" del precio al momento de comprar (si mañana el producto sube de precio, la venta antigua no cambia).

### 3.5 Vistas Blade (`resources/views`)

Plantillas HTML con “huecos” que el backend rellena **en el servidor** antes de enviar la página:

```blade
@foreach ($products as $product)
    <h2>{{ $product->name }}</h2>          {{-- {{ }} imprime y ESCAPA (protege contra XSS) --}}
    @if ($product->is_on_promotion) <span>Promoción</span> @endif
@endforeach

<form method="POST" action="{{ route('cart.store', $product) }}">
    @csrf                                   {{-- token anti-CSRF: obligatorio en POST/PUT/DELETE --}}
    <input type="number" name="quantity">
</form>
```

### 3.6 Validación (`app/Http/Requests`)

**Nunca** confíes en lo que llega del usuario. `ProductRequest` y `CheckoutRequest` definen reglas:

```php
'price' => ['required', 'numeric', 'min:0'],
'card_number' => ['required', 'string', new LuhnCardNumber],   // regla propia: algoritmo de Luhn
```

Si algo falla, Laravel devuelve automáticamente al formulario con `$errors` (web) o un `422` con JSON (API).

### 3.7 Sesión y carrito

HTTP **no tiene memoria**: cada petición es independiente. Para “recordar” el carrito, Laravel usa una **sesión**: guarda datos en el servidor (aquí, en la tabla `sessions`) y le da al navegador una **cookie** con un identificador. En cada petición el navegador envía esa cookie y Laravel recupera tu carrito (`CartService`).

### 3.8 Transacciones: todo o nada

Registrar una venta son varias escrituras (venta, líneas, descuento de stock de cada producto). Si falla a la mitad no puede quedar una venta sin stock descontado. `DB::transaction()` lo garantiza: si algo lanza una excepción, **se deshace todo** (rollback). Y `lockForUpdate()` evita que dos personas compren la última unidad al mismo tiempo.

---

## 4. Dos formas de conectar frontend y backend

### Forma A: el backend genera el HTML (lo que pide la evaluación)

```
Navegador ──GET /──▶ Laravel ──▶ Blade genera HTML completo ──▶ Navegador lo muestra
```

- Es lo que hacen `resources/views/*.blade.php`.
- El frontend y el backend están en el mismo proyecto.
- Los formularios envían datos con `GET`/`POST` (+ `@method`) y el servidor responde con otra página o una redirección.

### Forma B: API + frontend separado (SPA / app móvil)

```
Navegador (React, Vue, JS puro, app móvil)
   │  fetch('GET /api/products')
   ▼
Laravel (routes/api.php → ProductApiController) ──▶ responde JSON:
   { "data": [ { "id": 1, "name": "Funko Pop! Grogu", "price": 12990, "final_price": 11691, ... } ] }
   │
   ▼
El JavaScript del frontend toma ese JSON y dibuja la página.
```

Una **API** (Application Programming Interface) es un conjunto de URLs que devuelven **datos** (normalmente **JSON**) en vez de HTML. Una **API REST** organiza esas URLs por *recursos* (`/api/products`, `/api/sales`) y usa los verbos HTTP para las acciones.

Este proyecto tiene **ambas** formas usando **la misma lógica** (`SaleService`, modelos, validaciones):

| | Web (Blade) | API (JSON) |
|---|---|---|
| Rutas | `routes/web.php` | `routes/api.php` (prefijo `/api`) |
| Controlador | `CheckoutController` | `Api/SaleApiController` |
| Respuesta | `view(...)` / `redirect(...)` | `new SaleResource($sale)` → JSON |
| Estado | sesión + cookie + CSRF | sin estado (en apps reales: **token** en el header `Authorization: Bearer ...`, ver Laravel Sanctum) |

**Pruébalo:**

1. Abre <http://127.0.0.1:8000/api/products> → verás el JSON crudo.
2. Abre <http://127.0.0.1:8000/api-demo.html> → un frontend en **JavaScript puro** (sin Blade ni PHP) que usa `fetch()` para leer productos y registrar compras vía `POST /api/sales`. Abre F12 → Network para ver las peticiones. Su código está comentado en `public/api-demo.html`.

El núcleo de cómo un frontend habla con una API:

```js
// Leer datos
const res = await fetch('/api/products?franchise=Dune', { headers: { Accept: 'application/json' } });
const json = await res.json();          // → { data: [...], links: {...}, meta: {...} }

// Enviar datos
const res2 = await fetch('/api/sales', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ customer_name: 'Ana', ..., items: [{ product_id: 1, quantity: 2 }] }),
});
if (res2.status === 201) { /* venta creada */ }
if (res2.status === 422) { /* datos inválidos o sin stock: mostrar json.message */ }
```

**CORS**: si el frontend estuviera en *otro dominio* (p. ej. React en `localhost:5173` y Laravel en `localhost:8000`), el navegador bloquea las peticiones salvo que el backend lo permita. Laravel lo configura en `config/cors.php` (`php artisan config:publish cors`). Aquí no hace falta porque `api-demo.html` se sirve desde el mismo servidor.

También puedes probar la API con **Postman**, **Thunder Client** (extensión de VS Code) o `curl` (ejemplo en el README).

---

## 5. Recorrido por los requisitos de la evaluación

| Regla | Cómo se cumple |
|---|---|
| Producto con stock 0 no se vende | `Product::isAvailable()`. `CartController@store` rechaza agregarlo; la vista muestra "Agotado" y deshabilita el botón; `SaleService` vuelve a verificar al pagar (por si el stock cambió mientras el cliente tenía el producto en el carrito). |
| Stock > 20 ⇒ 10 % de descuento | Atributos `is_on_promotion`, `discount_rate`, `final_price` del modelo. Se muestran en el catálogo (precio tachado + badge) y se usan para cobrar. |
| Verificar stock suficiente | `Product::hasStockFor($qty)` dentro de la transacción con filas bloqueadas. |
| Descontar stock | `$product->decrement('stock', $qty)` dentro de la misma transacción. |
| Registrar total, comprador y tarjeta | Tabla `sales` (titular, marca, últimos 4 dígitos, vencimiento; **nunca** número completo ni CVV, como exige la norma PCI-DSS). |
| Venta con múltiples productos y cantidades | Tabla `sale_items` (una fila por producto con su cantidad). |
| Filtros por categoría / franquicia / nombre | `Product::scopeFilter()`, usado desde el catálogo, el gestor y la API. |

---

## 6. Tests automáticos

En `tests/` hay 20 pruebas que simulan peticiones reales y verifican la base de datos:

```bash
php artisan test
```

Ejemplo (`tests/Feature/CheckoutTest.php`): agrega 2 productos al carrito, paga, y comprueba que el total sea `2 × 9.000 + 3 × 5.000 = 33.000`, que se guardó la venta y que el stock bajó de 25 a 23 y de 3 a 0.

---

## 7. Para seguir aprendiendo

1. Documentación oficial: <https://laravel.com/docs/12.x> (empieza por *Routing*, *Controllers*, *Blade*, *Eloquent*, *Validation*).
2. Laracasts: *“30 Days to Learn Laravel”* (gratis).
3. Ejercicios sobre este proyecto:
   - Agrega login con `laravel/breeze` y protege `/admin` con el middleware `auth`.
   - Agrega imágenes a los productos (`$request->file('image')->store('products', 'public')`).
   - Crea un frontend en React o Vue que consuma `/api/products`.
   - Protege la API con tokens usando Laravel Sanctum (`php artisan install:api`).
