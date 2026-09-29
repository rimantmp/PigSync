<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pig_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pig_id')->index();
            $table->string('event_type');
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->date('event_date');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pig_histories');
    }
};
