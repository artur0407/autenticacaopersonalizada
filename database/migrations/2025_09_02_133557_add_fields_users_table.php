<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('last_login_at')->nullable()->after('remember_token')->default(null);
            $table->boolean('active')->default(true)->after('last_login_at')->default(null);
            $table->dateTime('blocked_until')->nullable()->after('active')->default(null);
            $table->softDeletes()->after('updated_at'); // cria a coluna deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_login_at',
                'active',
                'blocked_until',
                'deleted_at',
            ]);
        });
    }
};
