<?php

declare(strict_types=1);

namespace MauticPlugin\MauticAnasBusinessBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Model\ListModel;

class LoadSegmentData extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private ListModel $segmentModel
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $segments = [
            [
                'name' => 'B2C - Parents',
                'alias' => 'segment_b2c_parents',
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'institution_name',
                        'object' => 'lead',
                        'type' => 'text',
                        'operator' => 'empty',
                        'filter' => null,
                        'display' => null,
                    ],
                ],
            ],
            [
                'name' => 'B2B - Institutions',
                'alias' => 'segment_b2b_institutions',
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'institution_name',
                        'object' => 'lead',
                        'type' => 'text',
                        'operator' => '!empty',
                        'filter' => null,
                        'display' => null,
                    ],
                ],
            ],
            [
                'name' => 'Trial - Active',
                'alias' => 'trial_active',
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'trial_end_date',
                        'object' => 'lead',
                        'type' => 'date',
                        'operator' => 'gt',
                        'properties' => ['filter' => 'today'], // Mautic date filter syntax
                        'filter' => 'today',
                        'display' => null,
                    ],
                ],
            ],
            [
                'name' => 'Trial - Expired (Unconverted)',
                'alias' => 'trial_expired_unconverted',
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'trial_end_date',
                        'object' => 'lead',
                        'type' => 'date',
                        'operator' => 'lt', // Less than today
                        'properties' => ['filter' => 'today'],
                        'filter' => 'today',
                        'display' => null,
                    ],
                    [
                        'glue' => 'and',
                        'field' => 'tags',
                        'object' => 'lead',
                        'type' => 'tags',
                        'operator' => '!in',
                        'filter' => ['paid_subscriber'],
                        'display' => null,
                    ],
                ],
            ],
            [
                'name' => 'Signup - Incomplete',
                'alias' => 'signup_incomplete',
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'tags',
                        'object' => 'lead', // Explicit object
                        'type' => 'tags',
                        'operator' => '!in',
                        'filter' => ['activated'],
                        'display' => null,
                    ]
                ],
            ]
        ];

        $user = $manager->getRepository(\Mautic\UserBundle\Entity\User::class)->findOneBy(['username' => 'admin']);
        if (!$user) {
            // Fallback for first user found
            $user = $manager->getRepository(\Mautic\UserBundle\Entity\User::class)->findOneBy([]);
        }

        foreach ($segments as $data) {
            $existing = $manager->getRepository(LeadList::class)->findOneBy(['alias' => $data['alias']]);
            if ($existing) {
                continue;
            }

            $list = new LeadList();
            $list->setName($data['name']);
            $list->setAlias($data['alias']);
            $list->setPublicName($data['name']);
            $list->setFilters($data['filters']);
            $list->setIsGlobal(true);
            if ($user) {
                $list->setCreatedBy($user);
            }
            $list->setDateAdded(new \DateTime());

            $manager->persist($list);

            // Note: In real fixture execution, verify if we can call segmentModel->rebuildListLeads
            // here without issues. It might be safer to skip rebuild during fixtures to save time.
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2; // After Custom Fields
    }
}
