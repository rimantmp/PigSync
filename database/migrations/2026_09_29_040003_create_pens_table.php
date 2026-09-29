<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('area_id')->nullable()->index();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('fattening')->comment('grower/finisher/farrowing/gestation/weaning/quarantine');
            $table->integer('capacity')->default(0);
            $table->string('status')->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pens');
    }
};
