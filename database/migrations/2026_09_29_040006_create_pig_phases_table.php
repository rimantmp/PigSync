<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pig_phases', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->integer('age_min')->nullable()->comment('hari');
            $table->integer('age_max')->nullable()->comment('hari');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pig_phases');
    }
};
