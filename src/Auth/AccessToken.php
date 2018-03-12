<?php
/**
 * Created by PhpStorm.
 * User: hachi
 * Date: 2018/1/4
 * Time: 17:32
 */

namespace hachi\LaravelWechatThirdWork\Auth;

use EasyWeChat\Kernel\AccessToken as BaseAccessToken;
use EasyWeChat\Kernel\Contracts\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;

class AccessToken extends BaseAccessToken
{
    /**
     * @var string
     */
    protected $endpointToGetToken;

    protected $requestMethod = 'post';

    protected $refresh = false;

    /**
     * Credential for get token.
     *
     * @return array
     */
    protected function getCredentials(): array
    {
        return [
            'suite_id'       => $this->app['config']['suite_id'],
            'auth_corpid'    => $this->app['config']['corp_id'],
            'permanent_code' => $this->app['config']['permanent_code'],
        ];
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getToken(bool $refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $token = $this->requestToken($this->getCredentials(), true);

        $this->setToken($token[$this->tokenKey], $token['expires_in'] ?? 7200);

        return $token;
    }

    /**
     * @return string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getEndpoint(): string
    {
        return 'cgi-bin/service/get_corp_token';
    }


    /**
     * Send http request.
     *
     * @param array $credentials
     *
     * @return ResponseInterface
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function sendRequest(array $credentials): ResponseInterface
    {
        $openPlatform = $this->app['open_platform'];
        $suiteAccessToken = $openPlatform->getSuiteAccessToken();
        $options = [
            'query' => [
                'suite_access_token' => $suiteAccessToken
            ],
            'json'  => $credentials
        ];

        return $this->setHttpClient($this->app['http_client'])->request($this->getEndpoint(), $this->requestMethod, $options);
    }


    public function refresh(): AccessTokenInterface
    {
        $this->refresh = true;
        return $this;
    }
}