<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnancy_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('breeding_id')->nullable()->index();
            $table->unsignedBigInteger('sow_id')->index();
            $table->date('checked_at');
            $table->string('result')->default('positif')->comment('positif/negatif');
            $table->string('method')->default('ultrasound');
            $table->date('expected_farrow_at')->nullable();
            $table->string('status')->default('bunting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancy_records');
    }
};
