<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_id')->index();
            $table->date('received_at');
            $table->unsignedBigInteger('receiver_id')->nullable()->index();
            $table->string('status')->default('diterima');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_id')->index();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->decimal('total', 14, 2);
            $table->string('status')->default('belum_bayar')->comment('belum_bayar/lunas');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payable_type')->comment('invoice/expense');
            $table->unsignedBigInteger('payable_id')->index();
            $table->decimal('amount', 14, 2);
            $table->string('method')->default('transfer')->comment('tunai/transfer/tempo');
            $table->date('paid_at');
            $table->string('status')->default('lunas');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_receipts');
    }
};
