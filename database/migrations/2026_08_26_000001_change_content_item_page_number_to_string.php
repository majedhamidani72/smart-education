<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $table): void {
            $table->string('page_number', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_items', function (Blueprint $table): void {
            $table->unsignedSmallInteger('page_number')->nullable()->change();
        });
    }
};
