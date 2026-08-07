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
        Schema::create('payment_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Gateway
            |--------------------------------------------------------------------------
            */

            $table->string('gateway', 30);
            // zibal

            $table->string('authority', 100)
                ->nullable();
            // TrackId / Authority

            $table->string('transaction_id', 100)
                ->nullable();
            // شناسه داخلی درگاه

            $table->string('reference_id', 100)
                ->nullable();
            // شماره مرجع بانک

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('amount');

            $table->string('currency', 10)
                ->default('IRT');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'paid',

                'failed',

                'cancelled',

                'refunded',

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */

            $table->string('card_pan', 20)
                ->nullable();

            $table->text('message')
                ->nullable();

            $table->json('gateway_response')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('paid_at')
                ->nullable();

            $table->timestamp('verified_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['user_id', 'status'],
                'payment_user_status_index'
            );

            $table->index(
                ['purchase_id'],
                'payment_purchase_index'
            );

            $table->index(
                ['gateway'],
                'payment_gateway_index'
            );

            $table->index(
                ['authority'],
                'payment_authority_index'
            );

            $table->index(
                ['reference_id'],
                'payment_reference_index'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
