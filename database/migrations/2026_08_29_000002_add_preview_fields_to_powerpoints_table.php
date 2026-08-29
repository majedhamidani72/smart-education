<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('powerpoints', function (Blueprint $table) {
            $table->string('preview_pdf_path')->nullable()->after('preview_image');
            $table->unsignedInteger('sample_slides_count')->nullable()->after('slides_count');
            $table->json('features')->nullable()->after('sample_slides_count');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->index(['is_featured', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('powerpoints', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'is_active']);
            $table->dropColumn(['preview_pdf_path', 'sample_slides_count', 'features', 'is_featured']);
        });
    }
};
