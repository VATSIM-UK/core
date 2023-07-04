<?php

namespace Tests\Feature\Admin;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class BaseAdminResourceTestCase extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected ?string $resourceClass = null;

    protected ?string $policy = null;

    /**
     * @dataProvider providerPageRenderData
     */
    public function test_page_renders(string $name, $dataGenerator)
    {
        $this->actingAsSuperUser();

        if ($this->policy && in_array($name, ['index', 'view', 'edit', 'create'])) {
            // We'll test the gate too!
            $actionName = $name === 'index' ? 'viewAny' : ($name === 'edit' ? 'update' : $name);

            // Test when the gate says no
            $this->mockPolicyAction($this->policy, $actionName, false);
            $this->get($this->resourceClass::getUrl($name, $dataGenerator()))->assertForbidden();

            // Test when gate says yes
            $this->mockPolicyAction($this->policy, $actionName, true);
        }

        $this->get($this->resourceClass::getUrl($name, $dataGenerator()))->assertSuccessful();
    }

    public function providerPageRenderData()
    {
        if (! $this->resourceClass) {
            throw new Exception('You must specify the resource class to use the BaseAdminResourceTestCase');
        }

        $pages = $this->resourceClass::getPages();

        return collect($pages)->mapWithKeys(function ($page, $name) {
            return [$name => [$name, fn () => (in_array($name, ['index', 'create']) ? null : ['record' => $this->makeFactoryModel()->create()])]];
        })->all();
    }

    protected function makeFactoryModel(): \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\FactoryBuilder
    {
        $model = $this->resourceClass::getModel();
        if (method_exists($model, 'factory')) {
            return $model::factory();
        }

        return factory($model);
    }
}
