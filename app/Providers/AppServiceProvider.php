<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Una sola instancia del carrito por petición.
        $this->app->scoped(CartService::class, fn ($app) => new CartService($app['session.store']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // La paginación usa las clases de Bootstrap 5.
        Paginator::useBootstrapFive();

        // @money($valor) → $12.990 (sin decimales si el valor es entero)
        Blade::directive('money', fn ($expression) => "<?php echo \\App\\Providers\\AppServiceProvider::money($expression); ?>");

        // Todas las vistas que extienden el layout reciben el número de unidades en el carrito.
        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });
    }

    public static function money(float|int|string|null $value): string
    {
        $value = (float) $value;
        $decimals = floor($value) == $value ? 0 : 2;

        return '$'.number_format($value, $decimals, ',', '.');
    }
}
