<?php

namespace RecursiveTree\Seat\InfoPlugin;

use RecursiveTree\Seat\InfoPlugin\Acl\ArticlePolicy;
use RecursiveTree\Seat\InfoPlugin\Acl\ResourcePolicy;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\Resource;
use RecursiveTree\Seat\InfoPlugin\Observers\RoleObserver;
use Seat\Services\AbstractSeatPlugin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Seat\Web\Models\Acl\Role;
use Illuminate\Support\Facades\Artisan;
use Seat\Web\Models\User;

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


        //artisan commands

        //claim articles
        Artisan::command('seatinfo:articles:claim {user} {--all}', function () {
            //get user
            $user = $this->argument('user');
            $user = User::find($user) ?? User::where("name",$user)->first();

            if($user===null){
                $this->error("The specified user was not found!");
                return;
            }

            if($this->option("all")){
                //ask for confirmation again
                if ($this->confirm('Do you really want to overwrite ALL owners? (including articles with a valid owner!)')) {
                    $articles = Article::all();
                } else {
                    $this->warn("Cancelling operation");
                    return;
                }
            } else {
                $articles = Article::where("owner",null)->get();
            }

            $count = $articles->count();
            if (!$this->confirm("Do you really want to update the owner of $count article(s)? This can't be undone!")) {
                return;
            }

            foreach ($articles as $article){
                $article->owner = $user->id;
                $article->save();
            }

            $this->info("Updated the owner of $count article(s).");
        });

        //claim resources
        Artisan::command('seatinfo:resources:claim {user} {--all}', function () {
            //get user
            $user = $this->argument('user');
            $user = User::find($user) ?? User::where("name",$user)->first();

            if($user===null){
                $this->error("The specified user was not found!");
                return;
            }

            if($this->option("all")){
                //ask for confirmation again
                if ($this->confirm('Do you really want to overwrite ALL owners? (including resources with a valid owner!)')) {
                    $resources = Resource::all();
                } else {
                    $this->warn("Cancelling operation");
                    return;
                }
            } else {
                $resources = Resource::where("owner",null)->get();
            }

            $count = $resources->count();
            if (!$this->confirm("Do you really want to update the owner of $count resources(s)? This can't be undone!")) {
                return;
            }

            foreach ($resources as $article){
                $article->owner = $user->id;
                $article->save();
            }

            $this->info("Updated the owner of $count resources(s).");
        });
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