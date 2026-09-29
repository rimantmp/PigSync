<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('kind')->default('medicine')->comment('medicine/vaccine');
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->string('default_dose')->nullable();
            $table->integer('withdrawal_days')->default(0);
            $table->boolean('auto_deduct')->default(true)->comment('BR-15 / Q4: opsional per item');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
