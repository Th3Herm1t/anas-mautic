<?php

declare(strict_types=1);

namespace MauticPlugin\MauticAnasBusinessBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mautic\EmailBundle\Entity\Email;

class LoadEmailData extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['anas_business'];
    }

    public function load(ObjectManager $manager): void
    {
        $emails = [
            // Template Set A: Trial Conversion
            [
                'name' => 'Email A - Trial Day 7',
                'alias' => 'email_a_trial_day_7',
                'subject' => 'Your child is making great progress! 🌟',
                'content' => '<p>Learning milestones achieved so far...</p><p><a href="{pagelink=1}" class="button">Continue their journey - Upgrade now</a></p>'
            ],
            [
                'name' => 'Email B - Trial Day 12',
                'alias' => 'email_b_trial_day_12',
                'subject' => 'Only 2 days left in your free trial',
                'content' => '<p>Don\'t let their progress stop.</p><p><a href="{pagelink=1}" class="button">Upgrade today</a></p>'
            ],
            [
                'name' => 'Email C - Trial Day 14',
                'alias' => 'email_c_trial_day_14',
                'subject' => 'Last day of your free trial',
                'content' => '<p>Keep the momentum going.</p><p><a href="{pagelink=1}" class="button">Upgrade now</a></p>'
            ],
            // Template Set D: Activation Recovery
            [
                'name' => 'Email D - Activation 24h',
                'alias' => 'email_d_activation_24h',
                'subject' => '{contactfield=firstname}, complete your AnasArabic registration in 2 minutes',
                'content' => '<p>Easy activation process.</p><p><a href="https://anasarabic.com/activate?email={contactfield=email}" class="button">Activate My Account</a></p>'
            ],
            [
                'name' => 'Email E - Activation 72h',
                'alias' => 'email_e_activation_72h',
                'subject' => 'Your Arabic learning journey is waiting...',
                'content' => '<p>What your child will learn...</p><p><a href="https://anasarabic.com/activate?email={contactfield=email}" class="button">Activate account & start learning</a></p>'
            ],
            [
                'name' => 'Email F - Activation 120h',
                'alias' => 'email_f_activation_120h',
                'subject' => 'We haven\'t seen you yet - Need help?',
                'content' => '<p>Offer assistance.</p><p><a href="https://anasarabic.com/support" class="button">Talk to support</a></p>'
            ],
            // Template Set G: Recovery with Discount
            [
                'name' => 'Email G - Recovery Discount',
                'alias' => 'email_g_recovery_discount',
                'subject' => '🎁 Special offer: 20% off your first year',
                'content' => '<p>Unique promo code: <strong>SAVE20</strong></p><p><a href="https://anasarabic.com/upgrade?code=SAVE20" class="button">Claim your discount now</a></p>'
            ],
            [
                'name' => 'Email H - Discount Expiry',
                'alias' => 'email_h_discount_expiry',
                'subject' => '⏰ Your 20% discount expires tomorrow!',
                'content' => '<p>Success stories from other parents.</p><p><a href="https://anasarabic.com/upgrade?code=SAVE20" class="button">Use your discount now</a></p>'
            ],
        ];

        $repo = $manager->getRepository(Email::class);

        foreach ($emails as $data) {
            $existing = $repo->findOneBy(['alias' => $data['alias']]);
            if ($existing) {
                continue;
            }

            $email = new Email();
            $email->setName($data['name']);
            $email->setAlias($data['alias']);
            $email->setSubject($data['subject']);
            $email->setSubject($data['subject']); // Set twice to ensure internal
            $email->setIsPublished(true);
            $email->setLanguage('en');

            // Set definition for Theme
            $email->setTemplate('anas_arabic');

            // Wrap content in base template structure (simplified)
            $html = '<!DOCTYPE html><html><body><div class="container">' . $data['content'] . '</div></body></html>';
            $email->setCustomHtml($html);
            $email->setEmailType('template'); // Marketing email

            $email->setDateAdded(new \DateTime());

            $manager->persist($email);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }
}
