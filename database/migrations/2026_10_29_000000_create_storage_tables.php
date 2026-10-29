<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_storage_category', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('visibility')->default('private');

            $table->unsignedInteger('max_file_size_kb')->nullable();
            $table->json('allowed_mimes')->nullable();
            $table->unsignedInteger('max_files_per_user')->nullable();
            $table->unsignedBigInteger('max_bytes_per_user')->nullable();
            $table->unsignedInteger('retention_days')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('mship_account')->nullOnDelete();
        });

        Schema::create('sys_storage_upload', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->unsignedBigInteger('category_id');
            // The quota subject. Null for assets that do not belong to a member.
            $table->unsignedInteger('account_id')->nullable();
            // Who performed the upload, which may differ from the quota subject
            // (e.g. staff uploading a document on behalf of a member).
            $table->unsignedInteger('uploaded_by')->nullable();

            $table->nullableMorphs('uploadable');
            $table->string('collection')->default('default');

            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('extension', 32);
            $table->string('mime_type', 191);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64)->nullable();

            $table->dateTime('expires_at')->nullable();
            $table->dateTime('purged_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('sys_storage_category')->restrictOnDelete();
            $table->foreign('account_id')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('mship_account')->nullOnDelete();

            $table->index(['category_id', 'account_id']);
            $table->index('account_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_storage_upload');
        Schema::dropIfExists('sys_storage_category');
    }
};
