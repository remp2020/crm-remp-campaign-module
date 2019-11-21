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
use League\Event\Emitter;

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

    public function registerEventHandlers(Emitter $emitter)
    {
        $emitter->addListener(BannerEvent::class, $this->getInstance(BannerHandler::class));
    }
}
