<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('tag_id')->nullable();
            $table->string('rfid')->nullable();
            $table->string('sex')->comment('jantan/betina');
            $table->unsignedBigInteger('breed_id')->nullable()->index();
            $table->date('birth_date');
            $table->string('origin_type')->default('internal')->comment('internal/eksternal');
            $table->unsignedBigInteger('origin_ref')->nullable()->comment('kelahiran/PO');
            $table->unsignedBigInteger('sire_id')->nullable()->index();
            $table->unsignedBigInteger('dam_id')->nullable()->index();
            $table->unsignedBigInteger('pen_id')->index();
            $table->unsignedBigInteger('phase_id')->nullable()->index();
            $table->string('status')->default('aktif');
            $table->decimal('initial_weight', 8, 2)->nullable();
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('died_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigs');
    }
};
