<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('perlengkapan')->comment('kandang/peralatan/k3/lainnya');
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->decimal('default_price', 14, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index()->after('pr_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::dropIfExists('equipments');
    }
};
