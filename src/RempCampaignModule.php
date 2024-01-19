<?php

namespace Crm\RempCampaignModule;

use Crm\ApiModule\Models\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Models\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Models\Router\ApiIdentifier;
use Crm\ApiModule\Models\Router\ApiRoute;
use Crm\ApplicationModule\CrmModule;
use Crm\ApplicationModule\Models\Event\LazyEventEmitter;
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

    public function registerLazyEventHandlers(LazyEventEmitter $emitter)
    {
        $emitter->addListener(BannerEvent::class, BannerHandler::class);
    }
}
