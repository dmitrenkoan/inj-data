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
            $table->string('facility_oblast')->nullable()->after('facility_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropColumn('facility_oblast');
        });
    }
};
