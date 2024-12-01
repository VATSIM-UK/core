<?php

namespace Tests\Feature\FTE;

use App\Models\Smartcars\Flight;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FteCreationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function test_exercise_can_be_created()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('testing.png');
        $exercise = factory(Flight::class)->make()->toArray();
        $exercise['image'] = $file;

        $this->actingAs($this->privacc)
            ->post(route('adm.smartcars.exercises.store'), $exercise)
            ->assertRedirect();

        $name = sha1("{$file->getClientOriginalName()}.{$file->getClientOriginalExtension()}");

        Storage::disk('public')->assertExists('smartcars/exercises/'.$name.'.png');
    }
}
