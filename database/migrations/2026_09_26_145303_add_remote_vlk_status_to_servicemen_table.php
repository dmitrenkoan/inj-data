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
            $table->string('remote_vlk_status')->nullable()->after('facility_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropColumn('remote_vlk_status');
        });
    }
};
