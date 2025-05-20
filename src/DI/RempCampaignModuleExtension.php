<?php

namespace Crm\RempCampaignModule\DI;

use Nette\DI\CompilerExtension;

final class RempCampaignModuleExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        // load services from config and register them to Nette\DI Container
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services'],
        );
    }
}
