<?php
/**
 * Created by PhpStorm.
 * User: hachi
 * Date: 2018/1/5
 * Time: 14:24
 */

namespace hachi\LaravelWechatThirdWork\OpenPlatform;

use Carbon\Carbon;
use EasyWeChat\Kernel\Traits\HasHttpRequests;
use hachi\LaravelWechatThirdWork\Exceptions\RequestWeChatException;
use Illuminate\Support\Facades\Cache;
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;

class Auth
{
    use HasHttpRequests;

    protected $app;

    protected $cachePrefix = 'wechat-third-work.';

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Send http request.
     * @param $url
     * @param $options
     * @param string $method
     * @return ResponseInterface
     * @internal param array $credentials
     *
     */
    protected function sendRequest($url, $options, $method = 'post'): ResponseInterface
    {
        return $this->setHttpClient($this->app['http_client'])->request($url, $method, $options);
    }

    /**
     * 获取 suite_access_token【带缓存】
     * @return mixed
     * @author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
     */
    public function getSuiteAccessToken()
    {
        $suiteId = $this->app['config']['suite_id'];
        $suiteSecret = $this->app['config']['suite_secret'];
        $suiteTicket = $this->app['config']['suite_ticket'];
        $options = [
            'suite_id'     => $suiteId,
            'suite_secret' => $suiteSecret,
            'suite_ticket' => $suiteTicket
        ];
        $cacheKey = $this->cachePrefix . md5(json_encode($options));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        } else {
            $suiteAccessToken = $this->requestSuiteAccessToken($suiteId, $suiteSecret, $suiteTicket);
            Cache::put($cacheKey, $suiteAccessToken['suite_access_token'], Carbon::now()->addSeconds($suiteAccessToken['expires_in']));
            return $suiteAccessToken['suite_access_token'];
        }
    }


    /**
     * 获取第三方应用凭证
     * @param $suiteId
     * @param $suiteSec
     * @param $suiteTicket
     * @return mixed
     * @author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
     */
    public function requestSuiteAccessToken($suiteId, $suiteSec, $suiteTicket)
    {
        //suiteId 为null 的默认取第一个
        $response = $this->sendRequest('cgi-bin/service/get_suite_token', [
            'json' => [
                'suite_id'     => $suiteId,
                'suite_secret' => $suiteSec,
                'suite_ticket' => $suiteTicket
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);
        if (isset($response['errcode']) && $response['errcode'] != 0) {
            throw new RequestWeChatException($response['errmsg']);
        }

        return $response;
    }


    /**
     * 请求永久授权码
     * @param $suiteAccessToken
     * @param $authCode ,临时授权码，会在授权成功时附加在redirect_uri中跳转回第三方服务商网站，或通过回调推送给服务商。长度为64至512个字节
     * @return mixed
     * @author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
     */
    public function requestPermanentCode($suiteAccessToken, $authCode)
    {
        $response = $this->sendRequest('cgi-bin/service/get_permanent_code', [
            'query' => [
                'suite_access_token' => $suiteAccessToken
            ],
            'json'  => [
                'auth_code' => $authCode
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);
        if (isset($response['errcode']) && $response['errcode'] != 0) {
            throw new RequestWeChatException($response['errmsg']);
        }

        return $response;
    }

    /**
     * 获取预授权码【带缓存】
     * @return mixed
     */
    public function getPreAuthCode()
    {
        $suiteAccessToken = $this->getSuiteAccessToken();
        $options = [
            'suite_access_token' => $suiteAccessToken
        ];
        $cacheKey = $this->cachePrefix . md5(json_encode($options));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        } else {
            $preAuthCode = $this->requestPreAuthCode($suiteAccessToken);
            Cache::put($cacheKey, $preAuthCode['pre_auth_code'], Carbon::now()->addSeconds($preAuthCode['expires_in']));
            return $preAuthCode['pre_auth_code'];
        }
    }


    /**
     * 请求预授权码，预授权码用于企业授权时的第三方服务商安全验证。
     * @param $suiteAccessToken
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function requestPreAuthCode($suiteAccessToken)
    {
        $response = $this->sendRequest('cgi-bin/service/get_pre_auth_code', [
            'query' => [
                'suite_access_token' => $suiteAccessToken
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);
        if (isset($response['errcode']) && $response['errcode'] != 0) {
            throw new RequestWeChatException($response['errmsg']);
        }

        return $response;
    }

    /**
     * 通过永久授权码获取 Token【带缓存】
     * @return mixed
     */
    public function getCorpAccessTokenByPermanentCode()
    {
        $suiteAccessToken = $this->getSuiteAccessToken();
        $corpId = $this->app['config']['corp_id'];
        $permanentCode = $this->app['config']['permanent_code'];
        $options = [
            'suite_access_token' => $suiteAccessToken,
            'auth_corpid'        => $corpId,
            'permanent_code'     => $permanentCode
        ];
        $cacheKey = $this->cachePrefix . md5(json_encode($options));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        } else {
            $accessToken = $this->requestCorpAccessTokenByPermanentCode($suiteAccessToken, $corpId, $permanentCode);
            Cache::put($cacheKey, $accessToken['access_token'], Carbon::now()->addSeconds($accessToken['expires_in']));
            return $accessToken['access_token'];
        }
    }


    /**
     * 通过永久授权码获取企业Token
     * @param $suiteAccessToken
     * @param $corpId
     * @param $permanentCode
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
     */
    public function requestCorpAccessTokenByPermanentCode($suiteAccessToken, $corpId, $permanentCode)
    {
        $response = $this->sendRequest('cgi-bin/service/get_corp_token', [
            'query' => [
                'suite_access_token' => $suiteAccessToken
            ],
            'json'  => [
                'auth_corpid'    => $corpId,
                'permanent_code' => $permanentCode
            ]
        ]);
        $response = json_decode($response->getBody()->getContents(), true);

        if (isset($response['errcode']) && $response['errcode'] != 0) {
            throw new RequestWeChatException($response['errmsg']);
        }

        return $response;
    }


    public function getProviderToken()
    {
        $corpId = $this->app['config']['corp_id'];
        $providerSecret = $this->app['config']['provider_secret'];
        $options = [
            'corpid'          => $corpId,
            'provider_secret' => $providerSecret
        ];
        $cacheKey = $this->cachePrefix . md5(json_encode($options));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        } else {
            $providerToken = $this->requestProviderToken($corpId, $providerSecret);
            Cache::put($cacheKey, $providerToken['provider_access_token'], Carbon::now()->addSeconds($providerToken['expires_in']));
            return $providerToken['provider_access_token'];
        }
    }

    /**
     * 请求服务商的 Token
     * @param $corpId
     * @param $providerSecret
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function requestProviderToken($corpId, $providerSecret)
    {
        $response = $this->sendRequest('cgi-bin/service/get_provider_token', [
            'json' => [
                'corpid'          => $corpId,
                'provider_secret' => $providerSecret
            ]
        ]);
        $response = json_decode($response->getBody()->getContents(), true);

        if (isset($response['errcode']) && $response['errcode'] != 0) {
            throw new RequestWeChatException($response['errmsg']);
        }

        return $response;
    }


    /**
     * 通过服务商 ProviderToken 获取单点登录的用户信息。
     * @param $authCode
     * @param $providerAccessToken
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
     */
    public function requestLoginInfoByProviderAccessToken($authCode, $providerAccessToken)
    {
        $response = $this->sendRequest('cgi-bin/service/get_login_info', [
            'json'  => [
                'auth_code' => $authCode
            ],
            'query' => [
                'access_token' => $providerAccessToken
            ]
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        if (isset($response['errcode']) && $response['errcode'] != 0) {
            throw new RequestWeChatException($response['errmsg']);
        }

        return $response;
    }
}