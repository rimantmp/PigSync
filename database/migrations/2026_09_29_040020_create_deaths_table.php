<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deaths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pig_id')->index();
            $table->date('died_at');
            $table->unsignedBigInteger('pen_id')->nullable()->index();
            $table->string('cause');
            $table->unsignedBigInteger('suspected_disease_id')->nullable()->index();
            $table->string('disposal')->nullable()->comment('kubur/bakar/afkir jual');
            $table->decimal('estimated_loss', 14, 2)->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deaths');
    }
};
