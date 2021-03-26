<?php

namespace CodeQ\Instagram\Controller;

use CodeQ\Instagram\Domain\Service\InstagramService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;

class InstagramEndpointController extends ActionController
{
    /**
     * @Flow\Inject
     * @var InstagramService
     */
    protected $instagramService;

    protected $defaultViewObjectName = JsonView::class;

    /**
     * @param string|null $code
     *
     * @throws Exception
     */
    public function authorizeAction(string $code)
    {
        $redirectUri = sprintf("https://%s%s", $_SERVER['HTTP_HOST'],
            $_SERVER['REQUEST_URI']);
        $tokenArray = $this->instagramService->authorize($code, $redirectUri);
        $this->view->assign('value', $tokenArray);
    }
}
