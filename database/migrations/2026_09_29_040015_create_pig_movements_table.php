<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pig_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pig_id')->index();
            $table->unsignedBigInteger('from_pen_id')->nullable()->index();
            $table->unsignedBigInteger('to_pen_id')->index();
            $table->date('moved_at');
            $table->string('reason');
            $table->string('type')->default('pen')->comment('pen/cross_branch/quarantine');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pig_movements');
    }
};
