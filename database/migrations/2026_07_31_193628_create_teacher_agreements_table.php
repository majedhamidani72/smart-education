<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_agreements', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Agreement
            |--------------------------------------------------------------------------
            */

            $table->enum('agreement_type', [

                'teacher',

                'admin',

            ]);

            $table->string('agreement_version', 20);

            $table->timestamp('accepted_at');

            /*
            |--------------------------------------------------------------------------
            | Client Information
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                'agreement_type',
                'ta_type_idx'
            );

            $table->index(
                'agreement_version',
                'ta_version_idx'
            );

            $table->unique(
                [
                    'teacher_id',
                    'agreement_type',
                    'agreement_version',
                ],
                'ta_teacher_type_version_unique'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_agreements');
    }
};
