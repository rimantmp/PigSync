<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pig_id')->index();
            $table->date('checked_at');
            $table->text('symptoms')->nullable();
            $table->unsignedBigInteger('disease_id')->nullable()->index();
            $table->text('diagnosis')->nullable();
            $table->unsignedBigInteger('medicine_id')->nullable()->index();
            $table->string('dose')->nullable();
            $table->string('route')->nullable()->comment('injeksi/oral/topikal');
            $table->date('withdrawal_until')->nullable();
            $table->string('vet')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
