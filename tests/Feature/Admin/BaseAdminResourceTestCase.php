<?php

namespace Tests\Feature\Admin;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class BaseAdminResourceTestCase extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected ?string $resourceClass = null;

    /**
     * @dataProvider providerPageRenderData
     */
    public function test_page_renders(string $name, $dataGenerator)
    {
        $this->actingAsSuperUser();
        $this->get($this->resourceClass::getUrl($name, $dataGenerator()))->assertSuccessful();
    }

    public function providerPageRenderData()
    {
        if (! $this->resourceClass) {
            throw new Exception('You must specify the resource class to use the BaseAdminResourceTestCase');
        }

        $pages = $this->resourceClass::getPages();

        return collect($pages)->mapWithKeys(function ($page, $name) {
            return [$name => [$name, fn () => (in_array($name, ['index', 'create']) ? null : ['record' => $this->resourceClass::getModel()::factory()->create()])]];
        })->all();
    }
}
