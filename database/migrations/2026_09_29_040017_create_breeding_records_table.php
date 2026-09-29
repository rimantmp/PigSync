<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeding_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sow_id')->index();
            $table->unsignedBigInteger('boar_id')->nullable()->index();
            $table->date('bred_at');
            $table->string('method')->default('alami');
            $table->string('technician')->nullable();
            $table->string('dose')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeding_records');
    }
};
