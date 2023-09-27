<?php

namespace Crm\RempCampaignModule;

use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApplicationModule\CrmModule;
use Crm\RempCampaignModule\Api\ListBannersHandler;
use Crm\RempCampaignModule\Events\BannerHandler;
use Crm\ScenariosModule\Events\BannerEvent;

class RempCampaignModule extends CrmModule
{
    private $services = [];

    public function linkService($key, $host)
    {
        $this->services[$key] = $host;
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(
            new ApiRoute(
                new ApiIdentifier('1', 'remp', 'list-banners'),
                ListBannersHandler::class,
                BearerTokenAuthorization::class
            )
        );
    }

    public function registerLazyEventHandlers(\Crm\ApplicationModule\Event\LazyEventEmitter $emitter)
    {
        $emitter->addListener(BannerEvent::class, BannerHandler::class);
    }
}
