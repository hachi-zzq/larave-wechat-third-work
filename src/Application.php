<?php namespace hachi\LaravelWechatThirdWork;

/**
 * Created by PhpStorm.
 * DateTime: 2018/3/6 17:34
 * Author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
 */

use EasyWeChat\Work\Application as EasyWechatWorkApplication;

/**
 * Class Application
 * @property \EasyWeChat\Work\OA\Client $oa
 * @property \hachi\LaravelWechatThirdWork\Auth\ServiceProvider $access_token
 * @property \EasyWeChat\Work\Agent\Client $agent
 * @property \EasyWeChat\Work\Department\Client $department
 * @property \EasyWeChat\Work\Media\Client $media
 * @property \EasyWeChat\Work\Menu\Client $menu
 * @property \EasyWeChat\Work\Message\Client $message
 * @property \EasyWeChat\Work\Message\Messenger $messenger
 * @property \EasyWeChat\Work\User\Client $user
 * @property \EasyWeChat\Work\User\TagClient $tag
 * @property \EasyWeChat\Work\Server\ServiceProvider $server
 * @property \EasyWeChat\BasicService\Jssdk\Client $jssdk
 * @property \Overtrue\Socialite\Providers\WeWorkProvider $oauth
 * @property \hachi\LaravelWechatThirdWork\OpenPlatform\Auth open_platform
 * @package hachi\LaravelWechatThirdWork
 */
class Application extends EasyWechatWorkApplication
{
    protected static $instance;

    /**
     * @var array
     */
    protected $providers = [
        \EasyWeChat\Work\OA\ServiceProvider::class,
        \hachi\LaravelWechatThirdWork\Auth\ServiceProvider::class,
        \hachi\LaravelWechatThirdWork\OpenPlatform\ServiceProvider::class,
        \EasyWeChat\Work\Base\ServiceProvider::class,
        \EasyWeChat\Work\Menu\ServiceProvider::class,
        \EasyWeChat\Work\OAuth\ServiceProvider::class,
        \EasyWeChat\Work\User\ServiceProvider::class,
        \EasyWeChat\Work\Agent\ServiceProvider::class,
        \EasyWeChat\Work\Media\ServiceProvider::class,
        \EasyWeChat\Work\Message\ServiceProvider::class,
        \EasyWeChat\Work\Department\ServiceProvider::class,
        \EasyWeChat\Work\Server\ServiceProvider::class,
        \EasyWeChat\Work\Jssdk\ServiceProvider::class,
    ];

    public static function make(array $config)
    {
        if(self::$instance){
            return self::$instance;
        }

        self::$instance = new self($config);
        return self::$instance;
    }
}