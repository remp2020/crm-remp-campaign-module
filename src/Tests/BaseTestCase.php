<?php

namespace Crm\RempCampaignModule\Tests;

use Contributte\Translation\Translator;
use Crm\ApplicationModule\Models\Event\EventsStorage;
use Crm\ApplicationModule\Models\Scenario\TriggerManager;
use Crm\ApplicationModule\Tests\DatabaseTestCase;
use Crm\PaymentsModule\Events\RecurrentPaymentRenewedEvent;
use Crm\RempCampaignModule\RempCampaignModule;
use Crm\ScenariosModule\Engine\Engine;
use Crm\ScenariosModule\Repositories\ElementElementsRepository;
use Crm\ScenariosModule\Repositories\ElementsRepository;
use Crm\ScenariosModule\Repositories\JobsRepository;
use Crm\ScenariosModule\Repositories\ScenariosRepository;
use Crm\ScenariosModule\Repositories\TriggerElementsRepository;
use Crm\ScenariosModule\Repositories\TriggersRepository;
use Crm\ScenariosModule\ScenariosModule;
use Crm\SubscriptionsModule\Events\NewSubscriptionEvent;
use Crm\SubscriptionsModule\Events\SubscriptionEndsEvent;
use Crm\UsersModule\Events\UserRegisteredEvent;
use Crm\UsersModule\Repositories\UsersRepository;
use League\Event\Emitter;
use Tomaj\Hermes\Dispatcher;

abstract class BaseTestCase extends DatabaseTestCase
{
    /** @var ScenariosModule */
    protected $scenariosModule;

    /** @var RempCampaignModule */
    protected $rempCampaignModule;

    /** @var Dispatcher */
    protected $dispatcher;

    /** @var Emitter */
    protected $emitter;

    /** @var Engine */
    protected $engine;

    protected function requiredRepositories(): array
    {
        return [
            UsersRepository::class,
            // Scenario tables
            JobsRepository::class,
            ScenariosRepository::class,
            TriggerElementsRepository::class,
            TriggersRepository::class,
            ElementElementsRepository::class,
            ElementsRepository::class,
        ];
    }

    protected function requiredSeeders(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        $this->refreshContainer();
        parent::setUp();

        // INITIALIZE MODULES
        // TODO: figure out how to do this in configuration
        $translator = $this->inject(Translator::class);
        $this->scenariosModule = new ScenariosModule($this->container, $translator);
        $this->rempCampaignModule = new RempCampaignModule($this->container, $translator);
        $this->dispatcher = $this->inject(Dispatcher::class);
        $this->emitter = $this->inject(Emitter::class);

        // Events are not automatically registered, we need to register them manually for tests
        $eventsStorage = $this->inject(EventsStorage::class);
        $eventsStorage->register('user_registered', UserRegisteredEvent::class, true);
        $eventsStorage->register('new_subscription', NewSubscriptionEvent::class, true);
        $eventsStorage->register('subscription_ends', SubscriptionEndsEvent::class, true);
        $eventsStorage->register('recurrent_payment_renewed', RecurrentPaymentRenewedEvent::class, true);
        $this->scenariosModule->registerHermesHandlers($this->dispatcher);
        $this->scenariosModule->registerScenariosTriggers($this->inject(TriggerManager::class));
        $this->rempCampaignModule->registerLazyEventHandlers($this->emitter);

        $this->engine = $this->inject(Engine::class);
    }

    public static function obj(array $array)
    {
        return json_decode(json_encode($array), false);
    }
}
