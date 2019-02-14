<?php

namespace Tests\Feature;

use App\Models\Smartcars\Aircraft;
use App\Models\Smartcars\Airport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FteCreationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function testExerciseCanBeCreated()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('testing.png');
        $this->actingAs($this->privacc)->post(route('adm.smartcars.exercises.store'), $this->validParams(['image' => $file]))
            ->assertRedirect();

        $name = sha1("{$file->getClientOriginalName()}.{$file->getClientOriginalExtension()}");

        Storage::disk('public')->assertExists('storage/smartcars/exercises/'. $name .'.png');
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            "code" => "Q1",
            "name" => "blanditiis provident quia",
            "description" => "Praesentium sunt eius officia deserunt optio. Iure molestiae ipsum est eius dolorem repellendus nisi odit. Cupiditate expedita in dolor vel cum.",
            "featured" => false,
            "flightnum" => "1",
            "departure_id" => factory(Airport::class)->create()->id,
            "arrival_id" => factory(Airport::class)->create()->id,
            "route" => "Cupiditate non adipisci aut ducimus. Reprehenderit facilis et dolor accusantium qui quo sequi. Et tenetur et molestiae temporibus sapiente inventore dolorem perspiciatis.",
            "route_details" => "Qui odio quis et rerum alias. Rerum sed fuga blanditiis vel. Ex voluptas consequuntur nesciunt dignissimos. Rem omnis omnis maxime qui rerum.",
            "aircraft_id" => factory(Aircraft::class)->create()->id,
            "cruise_altitude" => 4721,
            "distance" => 65.91,
            "flight_time" => "0.45",
            "notes" => "Aliquid dolore ut praesentium sed. Blanditiis assumenda adipisci nihil aut et. Est rerum et voluptates reiciendis. Iusto et voluptatem ex maiores et.",
            "enabled" => true,
            "created_at" => "2019-02-02 14:42:59",
            "updated_at" => "2019-02-02 14:42:59",
            'image' => null,
        ], $overrides);
    }
}
