<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Curators are no longer a standalone list; existing assignments
        // pointed at the old curators table and have no valid meaning
        // against the users table.
        DB::table('servicemen')->update(['curator_id' => null]);

        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curator_id');
        });

        Schema::table('servicemen', function (Blueprint $table) {
            $table->foreignId('curator_id')->nullable()->after('treatment_status')->constrained('users')->nullOnDelete();
        });

        Schema::dropIfExists('curators');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('curators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curator_id');
        });

        Schema::table('servicemen', function (Blueprint $table) {
            $table->foreignId('curator_id')->nullable()->after('treatment_status')->constrained('curators')->nullOnDelete();
        });
    }
};
