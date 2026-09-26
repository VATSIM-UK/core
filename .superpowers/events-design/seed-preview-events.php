<?php

/*
 * Seeds placeholder events so the public events pages can be reviewed against
 * the approved design. Copy is lorem ipsum on purpose - nothing here should be
 * mistaken for a real VATSIM UK event.
 *
 * Re-run after any test run: the events suite mixes RefreshDatabase (unit) with
 * DatabaseTransactions (feature) against the shared dev database, so running it
 * empties these rows.
 *
 *   php artisan tinker --execute="require base_path('.superpowers/events-design/seed-preview-events.php');"
 */

use App\Models\Atc\Position;
use App\Models\Events\Event;

$positions = collect([
    ['callsign' => 'EGGW_APP', 'name' => 'Luton Approach', 'frequency' => '129.550'],
    ['callsign' => 'EGLL_TWR', 'name' => 'Heathrow Tower', 'frequency' => '118.500'],
    ['callsign' => 'EGKK_GND', 'name' => 'Gatwick Ground', 'frequency' => '121.800'],
    ['callsign' => 'EGCC_APP', 'name' => 'Manchester Approach', 'frequency' => '118.575'],
    ['callsign' => 'LTC_CTR', 'name' => 'London Terminal Control', 'frequency' => '134.900'],
])->map(fn ($p) => Position::firstOrCreate(
    ['callsign' => $p['callsign']],
    ['name' => $p['name'], 'frequency' => $p['frequency'], 'type' => 4, 'top_down' => false, 'virtual' => false]
));

$byCallsign = $positions->keyBy('callsign');

$description = <<<'HTML'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<h4>Ut enim ad minim veniam</h4>
<ul>
<li>Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.</li>
<li>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</li>
<li>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.</li>
</ul>
<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa quae ab illo inventore.</p>
HTML;

Event::query()->delete();

$make = function (array $attrs, array $callsigns = []) use ($byCallsign) {
    $event = Event::factory()->published()->create($attrs);
    if ($callsigns) {
        $event->positions()->sync($byCallsign->only($callsigns)->pluck('id')->all());
    }

    return $event;
};

$make([
    'name' => 'Lorem Ipsum Dolor',
    'tagline' => 'Consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore.',
    'description' => $description,
    'image_url' => null,
    'start' => now()->addDays(22)->setTime(18, 0),
    'end' => now()->addDays(22)->setTime(22, 0),
    'rostered' => true,
], ['EGGW_APP', 'EGLL_TWR', 'EGKK_GND']);

$make([
    'name' => 'Sed Do Eiusmod',
    'tagline' => 'Ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi.',
    'description' => '<p>Duis aute irure dolor in reprehenderit in voluptate velit esse.</p>',
    'image_url' => null,
    'start' => now()->addDays(24)->setTime(19, 0),
    'end' => now()->addDays(24)->setTime(21, 30),
    'rostered' => false,
], ['EGCC_APP', 'LTC_CTR']);

$make([
    'name' => 'Tempor Incididunt Labore',
    'tagline' => 'Excepteur sint occaecat cupidatat non proident sunt in culpa officia.',
    'description' => '<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur.</p>',
    'image_url' => null,
    'start' => now()->addDays(29)->setTime(10, 0),
    'end' => now()->addDays(29)->setTime(16, 0),
    'rostered' => false,
], ['EGGW_APP']);

$make([
    'name' => 'Magna Aliqua Veniam',
    'tagline' => 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet.',
    'description' => '<p>Ut enim ad minima veniam quis nostrum exercitationem ullam.</p>',
    'image_url' => null,
    'start' => now()->addDays(35)->setTime(17, 0),
    'end' => now()->addDays(35)->setTime(20, 0),
    'rostered' => true,
], ['EGLL_TWR', 'EGKK_GND', 'LTC_CTR']);

foreach ([
    ['Quis Nostrud Exercitation', 'Ullamco laboris nisi ut aliquip ex ea commodo consequat.', 40],
    ['Voluptate Velit Esse', 'Cillum dolore eu fugiat nulla pariatur excepteur sint.', 70],
    ['Occaecat Cupidatat Proident', 'Sunt in culpa qui officia deserunt mollit anim id est.', 110],
] as [$name, $tagline, $daysAgo]) {
    $make([
        'name' => $name,
        'tagline' => $tagline,
        'description' => "<p>{$tagline}</p>",
        'image_url' => null,
        'start' => now()->subDays($daysAgo)->setTime(18, 0),
        'end' => now()->subDays($daysAgo)->setTime(21, 0),
        'rostered' => false,
    ]);
}

echo 'events='.Event::count()
    .' upcoming='.app(App\Repositories\Events\EventRepository::class)->getUpcoming()->count()
    .' ids='.Event::pluck('id')->implode(',');
