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
        Schema::create('devices', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Device Information
            |--------------------------------------------------------------------------
            */

            $table->string(
                'device_identifier',
                150
            )->unique();

            $table->string(
                'device_name',
                150
            );

            $table->string(
                'manufacturer',
                100
            )->nullable();

            $table->string(
                'model',
                100
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Platform
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'platform',
                [

                    'android',

                    'ios',

                    'web',

                ]
            );

            $table->string(
                'os_version',
                50
            )->nullable();

            $table->string(
                'app_version',
                50
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Firebase
            |--------------------------------------------------------------------------
            */

            $table->text(
                'fcm_token'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Login
            |--------------------------------------------------------------------------
            */

            $table->string(
                'last_ip',
                45
            )->nullable();

            $table->timestamp(
                'last_login_at'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean(
                'is_active'
            )->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [

                    'user_id',

                    'is_active',

                ],
                'device_user_status_index'
            );

            $table->index(
                [

                    'platform',

                    'is_active',

                ],
                'device_platform_index'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
