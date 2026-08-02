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
    Schema::create('otp_codes', function (Blueprint $table) {

        $table->id();

        $table->foreignId('user_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->string('mobile',11)->index();

        $table->string('code',6);

        $table->enum('purpose',[
            'login',
            'reset_password'
        ]);

        $table->timestamp('expires_at');

        $table->timestamp('used_at')->nullable();

        $table->string('ip_address',45)->nullable();

        $table->text('user_agent')->nullable();

        $table->timestamps();

        $table->index(['mobile','purpose']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
