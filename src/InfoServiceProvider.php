<?php

namespace RecursiveTree\Seat\InfoPlugin;

use RecursiveTree\Seat\InfoPlugin\Acl\ArticlePolicy;
use RecursiveTree\Seat\InfoPlugin\Acl\ResourcePolicy;
use RecursiveTree\Seat\InfoPlugin\Observers\RoleObserver;
use Seat\Services\AbstractSeatPlugin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Seat\Web\Models\Acl\Role;


class InfoServiceProvider extends AbstractSeatPlugin
{
    public function boot(){

        $this->publishes([
            __DIR__ . '/resources/js' => public_path('info/js')
        ]);

        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'info');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'info');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        $version = $this->getVersion();

        Blade::directive('infoVersionedAsset', function($path) use ($version) {
            return "<?php echo asset({$path}) . '?v=$version'; ?>";
        });

        Gate::define('info.article.view',[ArticlePolicy::class,"view"]);
        Gate::define('info.article.edit',[ArticlePolicy::class,"edit"]);
        Gate::define('info.resource.view',[ResourcePolicy::class,"view"]);
        Gate::define('info.resource.edit',[ResourcePolicy::class,"edit"]);

        Role::observe(RoleObserver::class);
    }

    public function register(){
        $this->mergeConfigFrom(__DIR__ . '/Config/info.sidebar.php','package.sidebar');
        $this->registerPermissions(__DIR__ . '/Config/Permissions/info.permissions.php', 'info');
    }

    public function getName(): string
    {
        return 'SeAT Info';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/recursivetree/seat-info';
    }

    public function getPackagistPackageName(): string
    {
        return 'seat-info';
    }

    public function getPackagistVendorName(): string
    {
        return 'recursivetree';
    }
}