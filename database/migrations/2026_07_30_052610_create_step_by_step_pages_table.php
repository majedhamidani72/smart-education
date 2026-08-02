<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('step_by_step_pages', function (Blueprint $table) {

        $table->id();

        $table->foreignId('content_item_id')
            ->constrained()
            ->cascadeOnDelete(); // متعلق به یک محتوای گام‌به‌گام

        $table->unsignedSmallInteger('page_number'); // شماره صفحه کتاب

        $table->string('image'); // تصویر صفحه

        $table->unsignedTinyInteger('sort_order'); // ترتیب نمایش صفحات

        $table->boolean('is_free')->default(false); // صفحه رایگان

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_by_step_pages');
    }
};
