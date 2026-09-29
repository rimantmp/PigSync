<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('births', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sow_id')->nullable()->index();
            $table->date('farrowed_at');
            $table->integer('total_born');
            $table->integer('born_alive');
            $table->integer('born_dead')->default(0);
            $table->integer('mummified')->default(0);
            $table->decimal('avg_weight', 8, 2)->nullable();
            $table->unsignedBigInteger('pen_id')->index();
            $table->string('assistant')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('births');
    }
};
