<?php
/**
 * Tweakwise & Emico (https://www.tweakwise.com/ & https://www.emico.nl/) - All Rights Reserved
 *
 * @copyright Copyright (c) 2017-2017 Tweakwise.com B.V. (https://www.tweakwise.com)
 * @license   Proprietary and confidential, Unauthorized copying of this file, via any medium is strictly prohibited
 */

namespace Emico\Tweakwise\Model\Observer;

use Emico\Tweakwise\Model\Catalog\Layer\NavigationContext;
use Emico\Tweakwise\Model\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Observer;
use Emico\Tweakwise\Model\Catalog\Layer\Url\AbstractUrl;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;

class CatalogSearchRedirect implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var NavigationContext
     */
    protected $context;

    /**
     * @var Context
     */
    private $actionContext;

    /**
     * CatalogSearchRedirect constructor.
     * @param Config $config
     * @param NavigationContext $context
     * @param Context $actionContext
     */
    public function __construct(Config $config, NavigationContext $context, Context $actionContext)
    {
        $this->config = $config;
        $this->context = $context;
        $this->actionContext = $actionContext;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isSearchEnabled()) {
            return;
        }

        if (!$this->actionContext->getRequest()->getParam(AbstractUrl::PARAM_SEARCH)) {
            return;
        }

        $redirects = $this->context->getResponse()->getRedirects();
        if (!$redirects) {
            return;
        }

        $redirect = current($redirects);
        $url = $redirect->getUrl();
        if (strpos($url, 'http') !== 0) {
            $url = $this->actionContext->getUrl()->getUrl($url);
        }

        $response = $this->getHttpResponse();
        if (!$response) {
            return;
        }

        $response->setRedirect($url);
        /** @var Action $controller */
        $controller = $observer->getData('controller_action');
        $controller->getActionFlag()->set('', Action::FLAG_NO_DISPATCH, 1);
    }

    /**
     * @return Response|null
     */
    protected function getHttpResponse()
    {
        $response = $this->actionContext->getResponse();
        if (!$response instanceof Response) {
            return null;
        }

        return $response;
    }
}
