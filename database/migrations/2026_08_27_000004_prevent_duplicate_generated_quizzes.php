<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unique(
                ['template_id', 'quizable_type', 'quizable_id'],
                'quizzes_template_target_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', fn (Blueprint $table) =>
            $table->dropUnique('quizzes_template_target_unique')
        );
    }
};
