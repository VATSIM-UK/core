<?php

declare(strict_types=1);

namespace App\Console\Commands\Sitemap;

use App\Console\Commands\Command;
use App\Models\Airport;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap.';

    public function handle(): void
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap);

        $this->addAirportPages($sitemap);

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info("Sitemap generated successfully at {$path}");
    }

    protected function addStaticPages(Sitemap $sitemap): void
    {
        $pages = [
            ['route' => route('site.home'), 'priority' => 1.0, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.join'), 'priority' => 0.9, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.airports'), 'priority' => 0.8, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.atc.landing'), 'priority' => 0.9, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.atc.newController'), 'priority' => 0.9, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.atc.endorsements'), 'priority' => 0.7, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.atc.heathrow'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.atc.mentor'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.atc.bookings'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.pilots.landing'), 'priority' => 0.8, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.pilots.ratings'), 'priority' => 0.7, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.pilots.mentor'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.pilots.oceanic'), 'priority' => 0.7, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.pilots.stands'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.pilots.tfp'), 'priority' => 0.7, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.operations.landing'), 'priority' => 0.7, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.operations.sectors'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.community.teamspeak'), 'priority' => 0.5, 'change_frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['route' => route('site.policy.division'), 'priority' => 0.5, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.atc-training'), 'priority' => 0.5, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.visiting-and-transferring'), 'priority' => 0.5, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.community-standards'), 'priority' => 0.5, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.privacy'), 'priority' => 0.3, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.data-protection'), 'priority' => 0.3, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.branding'), 'priority' => 0.4, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.training.s1-syllabus'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.training.s2-syllabus'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.training.s3-syllabus'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['route' => route('site.policy.training.c1-syllabus'), 'priority' => 0.6, 'change_frequency' => Url::CHANGE_FREQUENCY_YEARLY],
        ];

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create($page['route'])
                    ->setPriority($page['priority'])
                    ->setChangeFrequency($page['change_frequency'])
            );
        }
    }

    protected function addAirportPages(Sitemap $sitemap): void
    {
        try {
            $airports = Airport::uk()->get();
        } catch (\Exception $e) {
            $this->warn('Could not fetch airports from the database');

            return;
        }

        foreach ($airports as $airport) {
            $sitemap->add(
                Url::create(route('site.airport.view', $airport))
                    ->setPriority(0.5)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        $this->info("Added {$airports->count()} airport pages to the sitemap.");
    }
}
