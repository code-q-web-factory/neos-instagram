<?php
namespace CodeQ\Instagram\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * Class Instagram
 * @package CodeQ\Instagram\Eel
 *
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

    public function allowsCallOfMethod($methodName) {
        return true;
    }
}
