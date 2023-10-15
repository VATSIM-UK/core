<?php

namespace Tests\Feature\Admin;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

abstract class BaseAdminResourceTestCase extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected static ?string $resourceClass = null;

    protected ?string $policy = null;

    #[DataProvider('providerPageRenderData')]
    public function test_page_renders(string $name, $dataGenerator)
    {
        $this->actingAsSuperUser();

        if ($this->policy && in_array($name, ['index', 'view', 'edit', 'create'])) {
            // We'll test the gate too!
            $actionName = $name === 'index' ? 'viewAny' : ($name === 'edit' ? 'update' : $name);

            // Test when the gate says no
            $this->mockPolicyAction($this->policy, $actionName, false);
            $this->get(static::$resourceClass::getUrl($name, $dataGenerator()))->assertForbidden();

            // Test when gate says yes
            $this->mockPolicyAction($this->policy, $actionName, true);
        }

        $this->get(static::$resourceClass::getUrl($name, $dataGenerator()))->assertSuccessful();
    }

    public static function providerPageRenderData()
    {
        if (! static::$resourceClass) {
            throw new Exception('You must specify the resource class to use the BaseAdminResourceTestCase');
        }

        $pages = static::$resourceClass::getPages();

        return collect($pages)->mapWithKeys(function ($page, $name) {
            return [$name => [$name, fn () => (in_array($name, ['index', 'create']) ? [] : ['record' => self::makeFactoryModel()->create()])]];
        })->all();
    }

    protected static function makeFactoryModel(): \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\FactoryBuilder
    {
        $model = static::$resourceClass::getModel();
        if (method_exists($model, 'factory')) {
            return $model::factory();
        }

        return factory($model);
    }
}
