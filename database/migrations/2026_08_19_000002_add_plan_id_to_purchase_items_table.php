<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| افزودن ستون گم‌شده‌ی plan_id به purchase_items
|--------------------------------------------------------------------------
| مدل PurchaseItem از قبل انتظار این ستون را در fillable داشت،
| ولی هیچ‌وقت واقعاً migrate نشده بود — همین باعث خطای
| «Unknown column 'plan_id'» موقع ساخت خرید از روی پلن می‌شد.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {

            $table->foreignId('plan_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('plans')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {

            $table->dropConstrainedForeignId('plan_id');

        });
    }
};
