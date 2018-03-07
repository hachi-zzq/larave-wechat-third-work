<?php namespace hachi\LaravelWechatThirdWork\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
/**
 * Created by PhpStorm.
 * DateTime: 2018/3/6 17:38
 * Author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register(Container $app)
    {
        $app['access_token'] = function ($app) {
            return new AccessToken($app);
        };
    }
}