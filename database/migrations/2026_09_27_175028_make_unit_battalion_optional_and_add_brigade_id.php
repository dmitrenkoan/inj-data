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
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('brigade_id')->nullable()->after('battalion_id')->constrained()->cascadeOnDelete();
        });

        // Doctrine/DBAL isn't installed, so a plain column ALTER via raw SQL
        // is used instead of Blueprint::change() to drop the NOT NULL
        // constraint on battalion_id without touching the column type or
        // its existing foreign key.
        DB::statement('ALTER TABLE units ALTER COLUMN battalion_id DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE units ALTER COLUMN battalion_id SET NOT NULL');

        Schema::table('units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brigade_id');
        });
    }
};
