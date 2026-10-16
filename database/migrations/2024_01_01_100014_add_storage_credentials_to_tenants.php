<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Cloudinary credentials per school
            if (!Schema::hasColumn('tenants', 'cloudinary_cloud_name')) {
                $table->string('cloudinary_cloud_name')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('tenants', 'cloudinary_api_key')) {
                $table->string('cloudinary_api_key')->nullable()->after('cloudinary_cloud_name');
            }
            if (!Schema::hasColumn('tenants', 'cloudinary_api_secret')) {
                $table->text('cloudinary_api_secret')->nullable()->after('cloudinary_api_key');
            }

            // Backblaze B2 credentials per school
            if (!Schema::hasColumn('tenants', 'b2_key_id')) {
                $table->string('b2_key_id')->nullable()->after('cloudinary_api_secret');
            }
            if (!Schema::hasColumn('tenants', 'b2_application_key')) {
                $table->text('b2_application_key')->nullable()->after('b2_key_id');
            }
            if (!Schema::hasColumn('tenants', 'b2_bucket_name')) {
                $table->string('b2_bucket_name')->nullable()->after('b2_application_key');
            }
            if (!Schema::hasColumn('tenants', 'b2_bucket_id')) {
                $table->string('b2_bucket_id')->nullable()->after('b2_bucket_name');
            }
            if (!Schema::hasColumn('tenants', 'b2_endpoint')) {
                $table->string('b2_endpoint')->nullable()->after('b2_bucket_id');
                // e.g. https://s3.us-west-004.backblazeb2.com
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumnIfExists('cloudinary_cloud_name');
            $table->dropColumnIfExists('cloudinary_api_key');
            $table->dropColumnIfExists('cloudinary_api_secret');
            $table->dropColumnIfExists('b2_key_id');
            $table->dropColumnIfExists('b2_application_key');
            $table->dropColumnIfExists('b2_bucket_name');
            $table->dropColumnIfExists('b2_bucket_id');
            $table->dropColumnIfExists('b2_endpoint');
        });
    }
};
