<?php

namespace Tests\Feature\Admin\Positions;

use App\Filament\Admin\Resources\Positions\Pages\CreatePosition;
use App\Filament\Admin\Resources\Positions\Pages\ListPositions;
use App\Filament\Admin\Resources\Positions\PositionResource;
use App\Models\Atc\Position;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class PositionResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = PositionResource::class;

    #[Test]
    public function it_denies_access_without_permission(): void
    {
        $this->actingAsAdminUser();

        $this->get(PositionResource::getUrl('index'))->assertForbidden();
        $this->get(PositionResource::getUrl('create'))->assertForbidden();
    }

    #[Test]
    public function it_allows_access_with_permission(): void
    {
        $this->actingAsAdminUser(['operations.positions']);

        $this->get(PositionResource::getUrl('index'))->assertSuccessful();
        $this->get(PositionResource::getUrl('create'))->assertSuccessful();
    }

    #[Test]
    public function it_can_create_a_position(): void
    {
        $this->actingAsAdminUser(['operations.positions']);

        Livewire::test(CreatePosition::class)
            ->fillForm([
                'callsign' => 'EGLL_TWR',
                'name' => 'London Tower',
                'frequency' => 118.500,
                'type' => Position::TYPE_TOWER,
                'temporarily_endorsable' => false,
                'virtual' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGLL_TWR',
            'name' => 'London Tower',
        ]);
    }

    #[Test]
    public function it_can_list_and_search_positions(): void
    {
        $this->actingAsAdminUser(['operations.positions']);

        $position1 = Position::factory()->create(['callsign' => 'EGLL_TWR', 'name' => 'London Tower']);
        $position2 = Position::factory()->create(['callsign' => 'EGKK_TWR', 'name' => 'Gatwick Tower']);

        Livewire::test(ListPositions::class)
            ->assertSuccessful()
            ->assertTableColumnStateSet('callsign', 'EGLL_TWR', $position1)
            ->assertTableColumnStateSet('callsign', 'EGKK_TWR', $position2)
            ->assertCanRenderTableColumn('callsign')
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('frequency')
            ->assertCanRenderTableColumn('type');
    }
}
