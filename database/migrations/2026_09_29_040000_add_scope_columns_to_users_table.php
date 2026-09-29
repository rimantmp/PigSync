<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('branch_scope')->nullable()->after('password')->comment('null=all, else json array of branch ids');
            $table->boolean('is_active')->default(true)->after('branch_scope');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['branch_scope', 'is_active', 'last_login_at']);
        });
    }
};
