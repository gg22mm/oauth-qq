<?php

namespace Wll\OauthQq;					//包里会自动省略src
use Illuminate\Support\ServiceProvider;	//调用laravel框架服务

class OauthProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/config/oauth-qq.php' => config_path('oauth-qq.php'),
        ]);
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('oauth', function ($app) {
            return new Oauth($app['config']);
        });
    }
}