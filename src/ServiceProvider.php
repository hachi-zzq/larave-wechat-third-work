<?php
/**
 * Created by PhpStorm.
 * User: keal
 * Date: 2018/2/28
 * Time: 下午11:49
 */
namespace hachi\LaravelWechatThirdWork;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the provider.
     */
    public function boot()
    {
    }


    public function register()
    {
        $this->app->singleton('wechat-third-work', function ($laravelApp) {
            return new Application();
        });

    }
}