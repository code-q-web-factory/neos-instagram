<?php
namespace CodeQ\Instagram\Eel;

use CodeQ\Instagram\Domain\Service\InstagramService;
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

    /**
     * @Flow\Inject
     * @var InstagramService
     */
    protected $instagramService;

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getFeed()
    {
        return $this->instagramService->getFeed();
    }

    /**
     * @return array|mixed
     * @throws \Neos\Cache\Exception
     * @throws \Neos\Flow\Exception
     */
    public function getToken()
    {
        return $this->instagramService->getToken()['token'];
    }

    /**
     * @return int|mixed
     * @throws \Neos\Cache\Exception
     * @throws \Neos\Flow\Exception
     */
    public function getTokenLifetime()
    {
        return $this->instagramService->getTokenLifetime();
    }

    /**
     * @param string $methodName
     *
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
