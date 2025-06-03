<?php

namespace Tests\Feature\FTE;

use App\Libraries\Storage\CoreUploadedFile;
use App\Models\Smartcars\Flight;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FteAdminTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
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

        $this->assertEquals(url('/').'/storage/smartcars/exercises/'.$name.'.png', Flight::first()->image);
    }

    #[Test]
    public function test_exercise_can_be_deleted()
    {
        $file = CoreUploadedFile::fake()->image('testing.png');
        $name = sha1("{$file->getClientOriginalName()}.{$file->getClientOriginalExtension()}");

        Storage::putFileAs('public', $file, 'smartcars/exercises/'.$name.'.png');

        $exercise = factory(Flight::class)->create([
            'image' => $name.'.png',
        ]);

        $this->actingAs($this->privacc)
            ->followingRedirects()
            ->delete(route('adm.smartcars.exercises.destroy', $exercise->id))
            ->assertSuccessful();

        $this->assertFalse(Storage::disk('public')->exists('smartcars/exercises/'.$name.'.png'));
        $this->assertCount(0, Flight::get(['id']));
    }
}
