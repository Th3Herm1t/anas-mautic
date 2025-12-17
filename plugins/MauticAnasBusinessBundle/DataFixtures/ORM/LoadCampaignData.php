<?php

declare(strict_types=1);

namespace MauticPlugin\MauticAnasBusinessBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Entity\LeadList;

class LoadCampaignData extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['anas_business'];
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Signup Incomplete Campaign
        $signupSegment = $manager->getRepository(LeadList::class)->findOneBy(['alias' => 'signup_incomplete']);
        // Email D (24h), E (72h), F (120h)
        $emailD = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email D - Activation 24h']);
        $emailE = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email E - Activation 72h']);
        $emailF = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email F - Activation 120h']);

        if ($signupSegment && $emailD && $emailE) { // Allow partial load
            $campaign = $this->createCampaign($manager, 'Scenario 1: Signup Incomplete', 'signup_incomplete_flow');
            $campaign->addList($signupSegment);

            // Action 1: Send Email D after 24 hours
            $event1 = $this->createEmailEvent($manager, $campaign, $emailD, 24, 'h');

            // Action 2: Send Email E 48h after Event 1 (Total 72h)
            $event2 = $this->createEmailEvent($manager, $campaign, $emailE, 48, 'h', $event1);

            // Action 3: Send Email F 48h after Event 2 (Total 120h)
            if ($emailF) {
                $this->createEmailEvent($manager, $campaign, $emailF, 48, 'h', $event2);
            }

            $manager->persist($campaign);
        }

        // 2. Trial Active Campaign
        $trialSegment = $manager->getRepository(LeadList::class)->findOneBy(['alias' => 'trial_active']);
        $emailA = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email A - Trial Day 7']);
        $emailB = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email B - Trial Day 12']);
        $emailC = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email C - Trial Day 14']);

        if ($trialSegment && $emailA && $emailB) {
            $campaign = $this->createCampaign($manager, 'Scenario 2: Trial Active Flow', 'trial_active_flow');
            $campaign->addList($trialSegment);

            // Action 1: Send Email A after 7 days
            $eventA = $this->createEmailEvent($manager, $campaign, $emailA, 7, 'd');

            // Action 2: Send Email B 5 days after A (Day 12)
            $eventB = $this->createEmailEvent($manager, $campaign, $emailB, 5, 'd', $eventA);

            // Action 3: Send Email C 2 days after B (Day 14)
            if ($emailC) {
                $this->createEmailEvent($manager, $campaign, $emailC, 2, 'd', $eventB);
            }

            $manager->persist($campaign);
        }

        // 3. Recovery Campaign
        $recoverySegment = $manager->getRepository(LeadList::class)->findOneBy(['alias' => 'trial_expired_unconverted']);
        $emailG = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email G - Recovery Discount']);
        $emailH = $manager->getRepository(Email::class)->findOneBy(['name' => 'Email H - Discount Expiry']);

        if ($recoverySegment && $emailG && $emailH) {
            $campaign = $this->createCampaign($manager, 'Scenario 3: Recovery Flow', 'recovery_flow');
            $campaign->addList($recoverySegment);

            // Action 1: Send Email G after 1 day
            $eventG = $this->createEmailEvent($manager, $campaign, $emailG, 1, 'd');

            // Action 2: Send Email H 5 days after G (Day 11 -> Day 16)
            $eventH = $this->createEmailEvent($manager, $campaign, $emailH, 5, 'd', $eventG);

            $manager->persist($campaign);
        }

        $manager->flush();
    }

    private function createCampaign(ObjectManager $manager, string $name, string $alias): Campaign
    {
        $repo = $manager->getRepository(Campaign::class);
        $existing = $repo->findOneBy(['alias' => $alias]);
        if ($existing) {
            return $existing;
        }

        $campaign = new Campaign();
        $campaign->setName($name);
        $campaign->setAlias($alias);
        $campaign->setIsPublished(true);

        return $campaign;
    }

    private function createEmailEvent(ObjectManager $manager, Campaign $campaign, Email $email, int $interval, string $unit, ?Event $parent = null): Event
    {
        $event = new Event();
        $event->setCampaign($campaign);
        $event->setName('Send ' . $email->getName());
        $event->setType('action');
        $event->setEventType('email.send');
        $event->setProperties(['email' => $email->getId()]);
        $event->setTriggerInterval($interval);
        $event->setTriggerIntervalUnit($unit);
        $event->setTriggerMode('interval');

        if ($parent) {
            $event->setParent($parent);
        }

        $campaign->addEvent($event);
        $manager->persist($event);

        return $event;
    }

    public function getOrder(): int
    {
        return 4;
    }
}
