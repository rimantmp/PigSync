<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->string('item_type')->comment('feed/medicine/equipment');
            $table->unsignedBigInteger('item_id')->index();
            $table->decimal('qty', 14, 2)->default(0);
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->decimal('min_stock', 14, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
