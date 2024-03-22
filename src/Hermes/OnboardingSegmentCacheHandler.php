<?php

namespace Crm\RempCampaignModule\Hermes;

use Crm\RempCampaignModule\Models\Campaign\Api;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\Handler\RetryTrait;
use Tomaj\Hermes\MessageInterface;
use Tracy\Debugger;
use Tracy\ILogger;

class OnboardingSegmentCacheHandler implements HandlerInterface
{
    use RetryTrait;

    public function __construct(private readonly Api $campaignApiClient)
    {
    }

    public function handle(MessageInterface $message): bool
    {
        $payload = $message->getPayload();

        if (!in_array($payload['action'], ['add', 'remove'], true)) {
            Debugger::log('Unable to handle event, missing or wrong `action` in payload', ILogger::ERROR);
            return false;
        }

        if (!$payload['user_id']) {
            Debugger::log('Unable to handle event, missing `user_id` in payload', ILogger::ERROR);
            return false;
        }

        if (!$payload['segment_code']) {
            Debugger::log('Unable to handle event, missing `segment_code` in payload', ILogger::ERROR);
            return false;
        }

        if ($payload['action'] === 'add') {
            return $this->campaignApiClient->segmentCacheAddUser($payload['user_id'], $payload['segment_code']);
        }

        return $this->campaignApiClient->segmentCacheRemoveUser($payload['user_id'], $payload['segment_code']);
    }
}
