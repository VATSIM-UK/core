<?php

namespace Tests\Feature\TrainingPanel\WaitingLists;

use App\Filament\Training\Resources\WaitingListResource;
use App\Policies\Training\WaitingListPolicy;
use Tests\Feature\TrainingPanel\BaseTrainingPanelResourceTestCase;

class WaitingListResourceTest extends BaseTrainingPanelResourceTestCase
{
    protected static ?string $resourceClass = WaitingListResource::class;

    protected ?string $policy = WaitingListPolicy::class;
}
