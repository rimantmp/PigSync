<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detail per penerimaan. Tanpa tabel ini, received_qty di purchase_order_items
        // hanya kumulatif sehingga struk penerimaan tidak bisa menunjukkan isi
        // penerimaan yang spesifik.
        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id')->index();
            $table->unsignedBigInteger('po_item_id')->index();
            $table->string('item_type')->comment('feed/medicine/equipment');
            $table->unsignedBigInteger('item_id')->index();
            $table->decimal('qty', 14, 2);
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['receipt_id', 'po_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
    }
};
