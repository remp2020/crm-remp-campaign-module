<?php

namespace Crm\RempCampaignModule\Events;

use Crm\OnboardingModule\Events\UserOnboardingGoalCompletedEvent;
use Crm\OnboardingModule\Events\UserOnboardingGoalCreatedEvent;
use Crm\OnboardingModule\Events\UserOnboardingGoalTimedoutEvent;
use Crm\OnboardingModule\Models\OnboardingGoalSegment;
use Crm\OnboardingModule\Repositories\OnboardingGoalsRepository;
use Crm\RempCampaignModule\Models\Campaign\Api;
use League\Event\AbstractListener;
use League\Event\EventInterface;

class UserOnboardingGoalEventsHandler extends AbstractListener
{
    private $campaignApiClient;

    private $onboardingGoalsRepository;

    public function __construct(
        Api $campaignApiClient,
        OnboardingGoalsRepository $onboardingGoalsRepository
    ) {
        $this->campaignApiClient = $campaignApiClient;
        $this->onboardingGoalsRepository = $onboardingGoalsRepository;
    }

    public function handle(EventInterface $event)
    {
        if ($event instanceof UserOnboardingGoalCreatedEvent) {
            $flagAddUser = true;
        } elseif ($event instanceof UserOnboardingGoalCompletedEvent || $event instanceof UserOnboardingGoalTimedoutEvent) {
            $flagAddUser = false;
        } else {
            throw new \Exception('Unexpected type of event, UserOnboardingGoalCreatedEvent|UserOnboardingGoalCompletedEvent|UserOnboardingGoalTimedoutEvent expected: ' . get_class($event));
        }

        $userOnboardingGoal = $event->getUserOnboardingGoal();

        $userId = $userOnboardingGoal->user_id;
        $onboardingGoal = $this->onboardingGoalsRepository->find($userOnboardingGoal->onboarding_goal_id);
        $segmentCode = OnboardingGoalSegment::getSegmentCode($onboardingGoal->code);

        if ($flagAddUser) {
            $this->campaignApiClient->segmentCacheAddUser($userId, $segmentCode);
        } else {
            $this->campaignApiClient->segmentCacheRemoveUser($userId, $segmentCode);
        }
    }
}
