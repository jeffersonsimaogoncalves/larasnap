<?php

namespace LaraSnap\LaravelAdmin;

use Illuminate\Support\ServiceProvider;
use LaraSnap\LaravelAdmin\Commands\InstallCommand;
use Illuminate\Support\Facades\Blade;

class LaravelAdminServiceProvider extends ServiceProvider{
	
	public function register(){
		$this->mergeConfigFrom(__DIR__.'/../config/larasnap.php', 'larasnap');	
	}
	
	public function boot(){
		//$this->loadRoutesFrom(__DIR__.'/../routes/web.php'); //uncomment if the routes needs to be loaded from package
		$this->loadViewsFrom(__DIR__.'/../resources/views', 'larasnap');	
		$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
		
		$router = $this->app['router'];
        $router->aliasMiddleware('check-userstatus', \LaraSnap\LaravelAdmin\Middleware\CheckUserStatus::class);
        $router->aliasMiddleware('check-roles', \LaraSnap\LaravelAdmin\Middleware\CheckRole::class);
		
		if ($this->app->runningInConsole()) {
			$this->registerPublishableResources();
            $this->registerConsoleCommands();
        }

        $this->bladeCanAccess();
	}
	
	private function registerPublishableResources(){
        $publishablePath = __DIR__.'/../';

        $publishable = [
            'larasnap-config' => [
                "{$publishablePath}/config/larasnap.php" => config_path('larasnap.php'),
            ],
            'larasnap-assets' => [
                "{$publishablePath}/assets"              => public_path('vendor/larasnap'),
            ],
            'larasnap-seeds' => [
                "{$publishablePath}/database/seeds"      => database_path('seeds'),
            ],
            'larasnap-migrations' => [
                "{$publishablePath}/database/migrations" => database_path('migrations'),
            ],
            'larasnap-views' => [
                "{$publishablePath}/resources/views"     => resource_path('views/vendor/larasnap'),
            ],
            'larasnap-auth-login-controller' => [
                __DIR__."/Controllers/Publishable-7.x/7.10.3/Auth/LoginController.php"     => app_path('Http/Controllers/Auth/LoginController.php'),
            ],
            'larasnap-auth-reg-controller' => [
                __DIR__."/Controllers/Publishable-7.x/7.10.3/Auth/RegisterController.php"  => app_path('Http/Controllers/Auth/RegisterController.php'),
            ],
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
	
	private function registerConsoleCommands(){
		$this->commands(InstallCommand::class);
	}

	private function bladeCanAccess(){
        Blade::if('canAccess', function ($screen_name) {
            // Get the required roles for the route(screen)
            $screenRoles = auth()->user()->getRequiredRoleForRoute($screen_name);
            // Check if a role is required for the route, and if so, ensure that the user has that role.
            if (auth()->user()->hasRole($screenRoles)) {
                return TRUE;
            }else{
                return FALSE;
            }
            return TRUE;
        });
    }
}