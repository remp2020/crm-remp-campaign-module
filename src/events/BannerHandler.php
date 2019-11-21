<?php

namespace Crm\RempCampaignModule\Events;

use Crm\RempCampaignModule\Models\Campaign\Api;
use Crm\ScenariosModule\Events\BannerEvent;
use League\Event\AbstractListener;
use League\Event\EventInterface;

class BannerHandler extends AbstractListener
{
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function handle(EventInterface $event)
    {
        if (!($event instanceof BannerEvent)) {
            throw new \Exception('Unable to handle event, expected BannerEvent');
        }

        $this->api->showOneTimeBanner($event->getUser()->id, $event->getBannerId(), $event->getExpiresInMinutes());
    }
}
