<?php

namespace Tests\Feature\Admin\Feedback;

use App\Filament\Resources\FeedbackResource;
use App\Policies\Nova\FeedbackPolicy;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class FeedbackResourceTest extends BaseAdminResourceTestCase
{
    protected ?string $resourceClass = FeedbackResource::class;

    protected ?string $policy = FeedbackPolicy::class;
}
