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
            $table->string('role')->default('battalion')->after('password');
            $table->foreignId('brigade_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->foreignId('battalion_id')->nullable()->after('brigade_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('battalion_id');
            $table->dropConstrainedForeignId('brigade_id');
            $table->dropColumn('role');
        });
    }
};
