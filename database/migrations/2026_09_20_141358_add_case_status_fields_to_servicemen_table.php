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
        Schema::table('servicemen', function (Blueprint $table) {
            $table->string('military_status')->default('active')->after('status');
            $table->boolean('is_combat_veteran')->default(false)->after('military_status');
            $table->string('treatment_status')->nullable()->after('is_combat_veteran');
            $table->foreignId('curator_id')->nullable()->after('treatment_status')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curator_id');
            $table->dropColumn(['military_status', 'is_combat_veteran', 'treatment_status']);
        });
    }
};
