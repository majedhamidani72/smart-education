<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('template_scope', 30)->nullable()->after('template_book_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', fn (Blueprint $table) => $table->dropColumn('template_scope'));
    }
};
