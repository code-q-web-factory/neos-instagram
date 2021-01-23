<?php
namespace CodeQ\Instagram\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * Class Instagram
 * @package CodeQ\Instagram\Eel
 *
 * This class can be used to create and refresh Instagram access tokens as well as to retrieve Instagram media files.
 *
 * First you have to setup a Facebook Instagram app. To do so follow the instructions up until
 * "Step 5: Exchange the Code for a Token" at
 * https://developers.facebook.com/docs/instagram-basic-display-api/getting-started
 *
 * Then you can create a long-lived access token by clicking on the button "Generate Token" in Facebook developer's
 * "User Token Generator" or use the function getToken.
 *
 * The token has a lifespan of 60 days - to refresh the token you can use the function refreshToken.
 *
 * To retrieve Media files you can use the function getInstagramFeed.
 */
class Instagram implements ProtectedContextAwareInterface {

    protected array $settings;

    /**
    * Inject the settings
    *
    * @param array $settings
    * @return void
    */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }

    /**
     * @param $token
     * @return mixed
     * @throws \Exception
     */
    public function refreshToken($token)
    {
        $apiData = [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $token
        ];

        return $this->makeApiCall($this->settings['apiEndpoints']['tokenRefreshUrl'], $apiData, 'GET');
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getFeed()
    {
        $token = $this->settings['token'];

        $apiData = [
            'fields' => 'id,caption,permalink,media_type,media_url,timestamp',
            'access_token' => $token
        ];

        $result = $this->makeApiCall($this->settings['apiEndpoints']['instagramMediaUrl'], $apiData, 'GET');

        if(isset($result['error'])) {
            throw new \Exception('Instagram API Error: ' . json_encode($result));
        }

        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * @param $code
     * @param $appId
     * @param $appSecret
     * @param $redirectUri
     * @return mixed
     * @throws \Exception
     */
    public function getToken($code, $appId, $appSecret, $redirectUri)
    {
        $shortLivedToken = $this->getShortLivedToken($appId, $appSecret, $redirectUri, $code);
        return $this->getLongLivedToken($shortLivedToken, $appSecret);
    }

    /**
     * @param $apiHost
     * @param $params
     * @param  string  $method
     * @return mixed
     * @throws \Exception
     */
    private function makeApiCall($apiHost, $params, $method = 'POST')
    {
        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '?'.http_build_query($params);
        }

        $apiCall = $apiHost.(('GET' === $method) ? $paramString : null);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->settings['apiEndpoints']['timeout']);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new \Exception('Error: _makeOAuthCall() - cURL error: '.curl_error($ch));
        }

        curl_close($ch);

        return json_decode($jsonData, 1);
    }

    /**
     * @param $token
     * @param $appSecret
     * @return mixed
     * @throws \Exception
     */
    private function getLongLivedToken($token, $appSecret)
    {
        $apiData = [
            'client_secret' => $appSecret,
            'grant_type' => 'ig_exchange_token',
            'access_token' => $token
        ];

        $result = $this->makeApiCall($this->settings['apiEndpoints']['tokenExchangeUrl'], $apiData, 'GET');

        if (!isset($result->access_token)) {
            throw new \Exception("Could not retrieve long lived access token: $result->error_message");
        }

        return $result->access_token;
    }

    /**
     * @param $appId
     * @param $appSecret
     * @param $redirectUri
     * @param $code
     * @return mixed
     * @throws \Exception
     */
    private function getShortLivedToken($appId, $appSecret, $redirectUri, $code)
    {
        $apiData = [
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code' => $code
        ];

        $result = $this->makeApiCall($this->settings['apiEndpoints']['oauthTokenUrl'], $apiData);

        if (!isset($result->access_token)) {
            throw new \Exception("Could not retrieve short lived access token: $result->error_message");
        }

        return $result->access_token;
    }


    public function allowsCallOfMethod($methodName) {
        return true;
    }
}
