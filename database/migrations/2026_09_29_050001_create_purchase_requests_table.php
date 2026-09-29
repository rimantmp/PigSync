<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->date('request_date');
            $table->unsignedBigInteger('requester_id')->nullable()->index();
            $table->string('status')->default('draft')->comment('draft/approved/po_created/rejected');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pr_id')->index();
            $table->string('item_type')->comment('feed/medicine/equipment');
            $table->unsignedBigInteger('item_id')->index();
            $table->decimal('qty', 14, 2);
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->decimal('est_price', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
