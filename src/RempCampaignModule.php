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
use Crm\RempCampaignModule\Hermes\OnboardingSegmentCacheHandler;
use Crm\ScenariosModule\Events\BannerEvent;
use Tomaj\Hermes\Dispatcher;

class RempCampaignModule extends CrmModule
{
    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(
            new ApiRoute(
                new ApiIdentifier('1', 'remp', 'list-banners'),
                ListBannersHandler::class,
                BearerTokenAuthorization::class,
            ),
        );
    }

    public function registerLazyEventHandlers(LazyEventEmitter $emitter)
    {
        $emitter->addListener(BannerEvent::class, BannerHandler::class);
    }

    public function registerHermesHandlers(Dispatcher $dispatcher)
    {
        $dispatcher->registerHandler(
            'onboarding-segment-cache',
            $this->getInstance(OnboardingSegmentCacheHandler::class),
        );
    }
}
