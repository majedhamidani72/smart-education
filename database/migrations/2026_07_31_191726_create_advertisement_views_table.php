
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * اجرای Migration
     */
    public function up(): void
    {
        Schema::create('advertisement_views', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | تبلیغ
            |--------------------------------------------------------------------------
            */

            $table->foreignId('advertisement_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | کاربر
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | اطلاعات بازدید
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address',45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->string('device_type')
                ->nullable();

            $table->string('platform')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('advertisement_id');

            $table->index('user_id');

            $table->index('created_at');

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'advertisement_views'
        );
    }
};
