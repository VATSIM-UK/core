<?php

declare(strict_types=1);

namespace App\Enums;

enum EventChecklistItem: string
{
    case EoiPublished = 'eoi_published';
    case RosterPublished = 'roster_published';
    case BriefingPublished = 'briefing_published';
    case BriefingCreated = 'briefing_created';
    case BannerCreated = 'banner_created';
    case EcfmpSetUp = 'ecfmp_set_up';
    case MyVatsimPublished = 'my_vatsim_published';

    public function label(): string
    {
        return match ($this) {
            self::EoiPublished => 'EOI published',
            self::RosterPublished => 'Roster published',
            self::BriefingPublished => 'Briefing published',
            self::BriefingCreated => 'Briefing created',
            self::BannerCreated => 'Banner created',
            self::EcfmpSetUp => 'ECFMP set up',
            self::MyVatsimPublished => 'My.vatsim.net published',
        };
    }

    /**
     * Value => label, for CheckboxList::options().
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $options, self $item): array => $options + [$item->value => $item->label()],
            [],
        );
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
