<?php

namespace CodeQ\Instagram\Domain\Service;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;

class InstagramService
{
    public const INSTAGRAM_TOKEN_CACHE_IDENTIFIER = 'INSTAGRAM_TOKEN';

    /**
     * @Flow\Inject
     * @var VariableFrontend
     */
    protected $instagramTokenCache;

    protected array $settings;

    /**
     * Inject the settings
     *
     * @param array $settings
     *
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getFeed()
    {
        $token = $this->getToken()['token'];

        $apiData = [
            'fields' => 'id,caption,permalink,media_type,media_url,thumbnail_url,timestamp,username',
            'access_token' => $token
        ];

        $result
            = $this->makeApiCall($this->settings['apiEndpoints']['instagramMediaUrl'],
            $apiData, 'GET');

        if (isset($result['error'])) {
            throw new \Exception('Instagram API Error: '
                . json_encode($result));
        }

        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws \Neos\Cache\Exception
     * @throws \Exception
     */
    public function getToken()
    {
        $tokenFromCache
            = $this->instagramTokenCache->get(self::INSTAGRAM_TOKEN_CACHE_IDENTIFIER);

        if (is_array($tokenFromCache)
            && array_key_exists('token', $tokenFromCache)
            && array_key_exists('expires', $tokenFromCache)
        ) {
            if (($tokenFromCache['expires'] - (30 * 24 * 60 * 60)) < time()) {
                $refreshedToken = $this->refreshToken($tokenFromCache['token']);
                $tokenFromCache
                    = $this->cacheLongLivingToken($refreshedToken['access_token'],
                    $refreshedToken['expires']);
            }

            return $tokenFromCache;
        }

        throw new Exception('ERROR: Instagram Token expired, please re-authorize!');
    }

    /**
     * @param $token
     *
     * @return mixed
     * @throws \Exception
     */
    public function refreshToken($token)
    {
        $apiData = [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $token
        ];

        return $this->makeApiCall($this->settings['apiEndpoints']['tokenRefreshUrl'],
            $apiData, 'GET');
    }

    /**
     * @param        $apiHost
     * @param        $params
     * @param string $method
     *
     * @return mixed
     * @throws \Exception
     */
    private function makeApiCall($apiHost, $params, $method = 'POST')
    {
        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '?' . http_build_query($params);
        }

        $apiCall = $apiHost . (('GET' === $method) ? $paramString : null);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS,
            $this->settings['apiEndpoints']['timeout']);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new \Exception('Error: _makeOAuthCall() - cURL error: '
                . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($jsonData, 1);
    }

    /**
     * @param string $longLivingToken
     * @param int    $expires
     *
     * @return array
     * @throws \Neos\Cache\Exception
     */
    private function cacheLongLivingToken(
        string $longLivingToken,
        int $expires
    ): array {
        $longLivingToken = str_replace('#_', '', $longLivingToken);
        $expiryDateTime = time() + $expires;
        $tokenArray = [
            'token' => $longLivingToken,
            'expires' => $expiryDateTime
        ];
        $this->instagramTokenCache->set(self::INSTAGRAM_TOKEN_CACHE_IDENTIFIER,
            $tokenArray);

        return $tokenArray;
    }

    /**
     * @return int|mixed
     * @throws Exception
     * @throws \Neos\Cache\Exception
     */
    public function getTokenLifetime()
    {
        return $this->getToken()['expires'] - time();
    }

    /**
     * @param string $code
     * @param string $redirectUri
     *
     * @return array
     * @throws Exception
     * @throws \Neos\Cache\Exception
     */
    public function authorize($code, $redirectUri)
    {
        if (!array_key_exists('appSecret', $this->settings)) {
            throw new Exception('ERROR: You need to define the app secret in your settings!');
        }

        $shortLivedToken = $this->getShortLivedToken($code, $redirectUri);
        $longLivingToken = $this->getLongLivedToken($shortLivedToken);

        return $this->cacheLongLivingToken($longLivingToken['access_token'],
            $longLivingToken['expires_in']);
    }

    /**
     * @param $code
     * @param $redirectUri
     *
     * @return mixed
     * @throws \Exception
     */
    public function getShortLivedToken($code, $redirectUri)
    {
        $apiData = [
            'app_id' => $this->settings['appId'],
            'app_secret' => $this->settings['appSecret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code' => $code
        ];

        $result
            = $this->makeApiCall($this->settings['apiEndpoints']['oauthTokenUrl'],
            $apiData);

        if (!isset($result['access_token'])) {
            throw new \Exception(sprintf("Could not retrieve short lived access token: %s",
                $result['error_message']));
        }

        return $result['access_token'];

    }

    /**
     * @param $shortLivedToken
     * @param $appSecret
     *
     * @return mixed
     * @throws \Exception
     */
    private function getLongLivedToken($shortLivedToken)
    {
        $apiData = [
            'client_secret' => $this->settings['appSecret'],
            'grant_type' => 'ig_exchange_token',
            'access_token' => $shortLivedToken
        ];

        $result
            = $this->makeApiCall($this->settings['apiEndpoints']['tokenExchangeUrl'],
            $apiData, 'GET');

        if (!isset($result['access_token'])) {
            throw new \Exception(sprintf("Could not retrieve long lived access token: %s",
                $result['message']));
        }

        return $result;
    }
}
