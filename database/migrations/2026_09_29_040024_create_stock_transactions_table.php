<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->string('item_type')->comment('feed/medicine/equipment');
            $table->unsignedBigInteger('item_id')->index();
            $table->string('direction')->comment('in/out');
            $table->decimal('qty', 14, 2);
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->string('txn_type')->comment('receipt/issue/transfer/opname/damage/expiry');
            $table->unsignedBigInteger('pen_id')->nullable()->index()->comment('tujuan distribusi pakan');
            $table->unsignedBigInteger('pig_id')->nullable()->index()->comment('pemakaian untuk ternak');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamp('at');
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
