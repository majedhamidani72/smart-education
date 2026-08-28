<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $table): void {
            // محتوا، مخصوصاً نمونه‌سؤال، می‌تواند مستقیماً متعلق به فصل باشد.
            $table->foreignId('section_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_items', function (Blueprint $table): void {
            $table->foreignId('section_id')->nullable(false)->change();
        });
    }
};
