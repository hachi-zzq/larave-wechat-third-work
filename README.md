## 企业号第三方授权 SDK for Laravel

> author:zhuzhengqian<hachi.zzq@gmail.com>

## 使用示例

```php
<?php
    use hachi\LaravelWechatThirdWork\Application;

//配置
   $config = [
        'corp_id' => 'corp_id',
        'agent_id' => 'agent_id', // 如果有 agend_id 则填写
        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
        'response_type' => 'collection',
        'permanent_code'=>'permanent_code',
        'suite_id'=>'suite_id',//套件ID
        'suite_secret'=>'suite_secret',//套件的secret
        'suite_ticket'=>'suite_ticket',//套件的ticket
        'provider_secret'=>'provider_secret',//单点登录、注册定制化 等需要用的 provider_secret
        'log' => [
            'level' => 'debug',
            'file'  => storage_path('logs/wechat.log'),
        ],
    ];

    $wechatThirdWork = Application::make($config);
    
    //使用
    
    //getSuiteAccessToken
    $wechatThirdWork->open_platform->getSuiteAccessToken();
    
    //getToken by permanenttCode
    $wechatThirdWork->open_platform->getCorpAccessTokenByPermanentCode();
```