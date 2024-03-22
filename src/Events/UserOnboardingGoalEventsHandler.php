<?php

namespace Crm\RempCampaignModule\Events;

use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\OnboardingModule\Events\UserOnboardingGoalCompletedEvent;
use Crm\OnboardingModule\Events\UserOnboardingGoalCreatedEvent;
use Crm\OnboardingModule\Events\UserOnboardingGoalTimedoutEvent;
use Crm\OnboardingModule\Models\OnboardingGoalSegment;
use Crm\OnboardingModule\Repositories\OnboardingGoalsRepository;
use League\Event\AbstractListener;
use League\Event\EventInterface;
use Tomaj\Hermes\Emitter;

class UserOnboardingGoalEventsHandler extends AbstractListener
{
    public function __construct(
        private readonly OnboardingGoalsRepository $onboardingGoalsRepository,
        private readonly Emitter $hermesEmitter
    ) {
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

        $this->hermesEmitter->emit(new HermesMessage('onboarding-segment-cache', [
            'action' => $flagAddUser ? 'add' : 'remove',
            'user_id' => $userId,
            'segment_code' => $segmentCode,
        ]), HermesMessage::PRIORITY_DEFAULT);
    }
}
