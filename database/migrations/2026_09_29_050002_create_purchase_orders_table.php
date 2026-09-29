<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('supplier_id')->index();
            $table->unsignedBigInteger('pr_id')->nullable()->index();
            $table->date('po_date');
            $table->string('po_number')->unique();
            $table->string('status')->default('draft')->comment('draft/sent/received/invoiced/paid/lunas');
            $table->decimal('total', 14, 2)->default(0);
            $table->string('payment_method')->default('transfer')->comment('tunai/transfer/tempo');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_id')->index();
            $table->string('item_type')->comment('feed/medicine/equipment');
            $table->unsignedBigInteger('item_id')->index();
            $table->decimal('qty', 14, 2);
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->decimal('price', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('received_qty', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
