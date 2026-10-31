<?php

namespace Tests\Feature\Storage;

use App\Exceptions\Storage\UploadLimitExceededException;
use App\Jobs\Storage\PurgeUpload;
use App\Models\Storage\Upload;
use App\Models\Storage\UploadCategory;
use App\Services\Storage\UploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadServiceTest extends TestCase
{
    private UploadCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');

        $this->category = UploadCategory::create([
            'slug' => 'event-banner',
            'name' => 'Event banner',
            'visibility' => 'private',
            'is_active' => true,
        ]);
    }

    public function test_it_stores_a_file_and_records_metadata(): void
    {
        $file = UploadedFile::fake()->create('report.pdf', 10, 'application/pdf');

        $upload = app(UploadService::class)->store($file, $this->category, $this->user);

        Storage::disk('uploads')->assertExists($upload->path);

        $this->assertSame($this->category->id, $upload->category_id);
        $this->assertSame($this->user->id, $upload->account_id);
        $this->assertSame('report.pdf', $upload->original_name);
        $this->assertSame('pdf', $upload->extension);
        $this->assertSame('application/pdf', $upload->mime_type);
        $this->assertSame(10 * 1024, $upload->size_bytes);
        $this->assertNotNull($upload->checksum);
        $this->assertNull($upload->expires_at);
        $this->assertStringStartsWith("uploads/{$upload->ulid}.", $upload->path);
    }

    public function test_it_uses_the_category_retention_for_expiry(): void
    {
        $this->category->update(['retention_days' => 7]);

        $upload = app(UploadService::class)->store(
            UploadedFile::fake()->create('report.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
        );

        $this->assertTrue($upload->expires_at->equalTo(now()->addDays(7)));
    }

    public function test_it_rejects_an_inactive_category(): void
    {
        $this->category->update(['is_active' => false]);

        $this->expectException(UploadLimitExceededException::class);
        $this->expectExceptionMessage('is not accepting files');

        app(UploadService::class)->store(
            UploadedFile::fake()->create('report.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
        );
    }

    public function test_it_rejects_a_file_over_the_category_size_limit(): void
    {
        $this->category->update(['max_file_size_kb' => 1]);

        $this->expectException(UploadLimitExceededException::class);
        $this->expectExceptionMessage('maximum allowed size');

        app(UploadService::class)->store(
            UploadedFile::fake()->create('report.pdf', 2, 'application/pdf'),
            $this->category,
            $this->user,
        );
    }

    public function test_it_rejects_a_mime_type_outside_the_allow_list(): void
    {
        $this->category->update(['allowed_mimes' => ['application/pdf']]);

        $this->expectException(UploadLimitExceededException::class);
        $this->expectExceptionMessage('not allowed');

        app(UploadService::class)->store(
            UploadedFile::fake()->create('photo.png', 1, 'image/png'),
            $this->category,
            $this->user,
        );
    }

    public function test_it_enforces_the_per_user_file_count(): void
    {
        $this->category->update(['max_files_per_user' => 1]);
        $service = app(UploadService::class);

        $service->store(UploadedFile::fake()->create('one.pdf', 1, 'application/pdf'), $this->category, $this->user);

        $this->expectException(UploadLimitExceededException::class);
        $this->expectExceptionMessage('at most 1');

        $service->store(UploadedFile::fake()->create('two.pdf', 1, 'application/pdf'), $this->category, $this->user);
    }

    public function test_it_enforces_the_per_user_byte_quota(): void
    {
        $this->category->update(['max_bytes_per_user' => 1536]);
        $service = app(UploadService::class);

        $service->store(UploadedFile::fake()->create('one.pdf', 1, 'application/pdf'), $this->category, $this->user);

        $this->expectException(UploadLimitExceededException::class);
        $this->expectExceptionMessage('storage limit');

        $service->store(UploadedFile::fake()->create('two.pdf', 1, 'application/pdf'), $this->category, $this->user);
    }

    public function test_usage_only_counts_the_owner_and_category(): void
    {
        $service = app(UploadService::class);
        $other = \App\Models\Mship\Account::factory()->createQuietly();

        $service->store(UploadedFile::fake()->create('one.pdf', 2, 'application/pdf'), $this->category, $this->user);
        $service->store(UploadedFile::fake()->create('two.pdf', 3, 'application/pdf'), $this->category, $this->user);
        $service->store(UploadedFile::fake()->create('other.pdf', 5, 'application/pdf'), $this->category, $other);

        $usage = $service->usage($this->category, $this->user);

        $this->assertSame(2, $usage['files']);
        $this->assertSame(5 * 1024, $usage['bytes']);
    }

    public function test_delete_soft_deletes_and_queues_the_object_purge(): void
    {
        Queue::fake();

        $upload = app(UploadService::class)->store(
            UploadedFile::fake()->create('report.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
        );

        app(UploadService::class)->delete($upload);

        $this->assertSoftDeleted('sys_storage_upload', ['id' => $upload->id]);
        Storage::disk('uploads')->assertExists($upload->path);

        Queue::assertPushed(PurgeUpload::class, fn (PurgeUpload $job): bool => $job->uploadId === $upload->id
            && $job->disk === 'uploads'
            && $job->path === $upload->path);
    }

    public function test_replacing_a_morph_collection_removes_the_previous_upload(): void
    {
        Queue::fake();

        $event = \App\Models\Events\Event::factory()->create();
        $service = app(UploadService::class);

        $first = $service->store(
            UploadedFile::fake()->create('first.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
            $event,
            'banner',
        );

        $second = $service->store(
            UploadedFile::fake()->create('second.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
            $event,
            'banner',
            replace: true,
        );

        $this->assertSoftDeleted('sys_storage_upload', ['id' => $first->id]);
        $this->assertDatabaseHas('sys_storage_upload', ['id' => $second->id, 'collection' => 'banner']);
        $this->assertSame($second->id, Upload::query()
            ->where('uploadable_type', $event->getMorphClass())
            ->where('uploadable_id', $event->getKey())
            ->where('collection', 'banner')
            ->latest('id')
            ->value('id'));
    }

    public function test_upload_exposes_a_stable_route_url(): void
    {
        $upload = app(UploadService::class)->store(
            UploadedFile::fake()->create('report.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
        );

        $this->assertSame(route('storage.files.show', $upload), $upload->url());
        $this->assertStringContainsString($upload->ulid, $upload->url());
        $this->assertSame($upload->ulid, $upload->getRouteKey());
    }

    public function test_upload_can_be_found_by_ulid(): void
    {
        $upload = app(UploadService::class)->store(
            UploadedFile::fake()->create('report.pdf', 1, 'application/pdf'),
            $this->category,
            $this->user,
        );

        $this->assertTrue(Upload::where('ulid', $upload->ulid)->exists());
    }
}
