<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pig_weights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pig_id')->index();
            $table->date('weighed_at');
            $table->decimal('weight', 8, 2);
            $table->string('method')->default('individu');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->index(['pig_id', 'weighed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pig_weights');
    }
};
