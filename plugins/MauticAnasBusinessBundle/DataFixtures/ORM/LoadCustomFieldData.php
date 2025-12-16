<?php

declare(strict_types=1);

namespace MauticPlugin\MauticAnasBusinessBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mautic\LeadBundle\Entity\LeadField;

class LoadCustomFieldData extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $fields = [
            [
                'label' => 'Student Age',
                'alias' => 'student_age',
                'type' => 'number',
                'group' => 'core',
            ],
            [
                'label' => 'Institution Name',
                'alias' => 'institution_name',
                'type' => 'text',
                'group' => 'professional',
            ],
            [
                'label' => 'Level Result',
                'alias' => 'level_result',
                'type' => 'select',
                'group' => 'core',
                'properties' => [
                    'list' => [
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]
                ]
            ],
            [
                'label' => 'Trial Start Date',
                'alias' => 'trial_start_date',
                'type' => 'date',
                'group' => 'core',
            ],
            [
                'label' => 'Trial End Date',
                'alias' => 'trial_end_date',
                'type' => 'date',
                'group' => 'core',
            ],
        ];

        $repo = $manager->getRepository(LeadField::class);

        foreach ($fields as $data) {
            $existing = $repo->findOneBy(['alias' => $data['alias']]);
            if ($existing) {
                continue;
            }

            $field = new LeadField();
            $field->setLabel($data['label']);
            $field->setAlias($data['alias']);
            $field->setType($data['type']);
            $field->setGroup($data['group']);
            $field->setObject('lead');
            $field->setIsPublished(true);
            $field->setDateAdded(new \DateTime());

            if (isset($data['properties'])) {
                $field->setProperties($data['properties']);
            }

            $manager->persist($field);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
